<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientNote;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ClientNoteController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'is_sticky' => ['nullable', 'boolean'],
        ]);

        $client->notes()->create([
            'body' => $validated['body'],
            'is_sticky' => $request->boolean('is_sticky'),
            'staff_id' => auth('staff')->id(),
        ]);

        ActivityLogger::log(
            'client.note_added',
            'client',
            $client->id,
            $client->full_name,
        );

        return back()->with('success', __('clients.note_added'));
    }

    public function destroy(Request $request, Client $client, ClientNote $note)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        if ($note->client_id !== $client->id) {
            abort(404);
        }

        $note->delete();

        return back()->with('success', __('clients.note_deleted'));
    }
}
