<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'unread') {
                $query->where('is_read', false);
            } elseif ($status === 'read') {
                $query->where('is_read', true);
            }
        }

        $messages = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $unreadCount = ContactMessage::where('is_read', false)->count();

        return view('admin.contact-messages.index', compact('messages', 'unreadCount'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if (! $contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function destroy(Request $request, ContactMessage $contactMessage)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $contactMessage->delete();
        ActivityLogger::log('contact_message.deleted', 'contact_message', $contactMessage->id, $contactMessage->email, null);

        return redirect()->route('admin.contact-messages.index')
            ->with('success', __('contact.message_deleted'));
    }
}
