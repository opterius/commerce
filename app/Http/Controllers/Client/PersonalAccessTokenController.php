<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PersonalAccessToken;
use Illuminate\Http\Request;

class PersonalAccessTokenController extends Controller
{
    private function client()
    {
        return auth('client')->user();
    }

    public function index()
    {
        $tokens = PersonalAccessToken::where('client_id', $this->client()->id)
            ->orderByDesc('created_at')
            ->get();

        return view('client.api-tokens.index', compact('tokens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        [$token, $plaintext] = PersonalAccessToken::generate($this->client(), $data['name']);

        return redirect()->route('client.api-tokens.index')
            ->with('new_token', $plaintext)
            ->with('new_token_name', $token->name);
    }

    public function destroy(Request $request, PersonalAccessToken $personalAccessToken)
    {
        $data = $request->validate([
            'password' => 'required|string',
        ]);

        if (! password_verify($data['password'], $this->client()->password)) {
            return back()->with('error', __('common.invalid_password'));
        }

        if ($personalAccessToken->client_id !== $this->client()->id) {
            abort(403);
        }

        $personalAccessToken->delete();

        return back()->with('success', __('api_tokens.revoked'));
    }
}
