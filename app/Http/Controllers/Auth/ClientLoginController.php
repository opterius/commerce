<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.client-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth('client')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $client = auth('client')->user();
            $client->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        auth('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.login');
    }
}
