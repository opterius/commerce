<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\NewTicketMail;
use App\Mail\TicketReplyMail;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketDepartment;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = auth('client')->user()
            ->tickets()
            ->with('department')
            ->orderByDesc('last_reply_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(config('commerce.pagination', 25))->withQueryString();

        return view('client.tickets.index', compact('tickets'));
    }

    public function create()
    {
        $departments = TicketDepartment::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('client.tickets.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:ticket_departments,id',
            'subject'       => 'required|string|max:255',
            'body'          => 'required|string|max:65535',
            'attachments.*' => 'nullable|file|max:' . (int) Setting::get('ticket_max_attachment_kb', 10240),
        ]);

        $client = auth('client')->user();

        $ticket = Ticket::create([
            'client_id'     => $client->id,
            'department_id' => $request->department_id,
            'subject'       => $request->subject,
            'status'        => 'open',
            'priority'      => Setting::get('ticket_default_priority', 'medium'),
            'last_reply_at' => now(),
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'client_id' => $client->id,
            'body'      => $request->body,
        ]);

        $this->storeAttachments($request, $reply);

        // Notify staff (admin-wide)
        $this->notifyStaff($ticket, $reply);

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', __('tickets.ticket_created'));
    }

    public function show(Ticket $ticket)
    {
        abort_if($ticket->client_id !== auth('client')->id(), 403);

        $ticket->load([
            'department',
            'publicReplies.staff',
            'publicReplies.client',
            'publicReplies.attachments',
        ]);

        return view('client.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        abort_if($ticket->client_id !== auth('client')->id(), 403);

        $request->validate([
            'body'          => 'required|string|max:65535',
            'attachments.*' => 'nullable|file|max:' . (int) Setting::get('ticket_max_attachment_kb', 10240),
        ]);

        $client = auth('client')->user();

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'client_id' => $client->id,
            'body'      => $request->body,
        ]);

        $this->storeAttachments($request, $reply);

        // Re-open closed tickets; mark others as customer_reply
        $newStatus = $ticket->status === 'closed' ? 'customer_reply' : 'customer_reply';

        $ticket->update([
            'status'        => $newStatus,
            'closed_at'     => null,
            'last_reply_at' => now(),
        ]);

        // Notify assigned staff or general inbox
        $this->notifyStaff($ticket, $reply);

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', __('tickets.reply_sent'));
    }

    public function close(Request $request, Ticket $ticket)
    {
        abort_if($ticket->client_id !== auth('client')->id(), 403);

        $ticket->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        return redirect()->route('client.tickets.show', $ticket)
            ->with('success', __('tickets.ticket_closed'));
    }

    public function downloadAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        abort_if($ticket->client_id !== auth('client')->id(), 403);
        abort_if($attachment->reply->ticket_id !== $ticket->id, 403);

        return Storage::download($attachment->path, $attachment->original_name);
    }

    private function storeAttachments(Request $request, TicketReply $reply): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        $allowedExt = array_map('trim', explode(',',
            Setting::get('ticket_allowed_extensions', 'jpg,jpeg,png,gif,pdf,txt,zip,doc,docx')
        ));

        foreach ($request->file('attachments') as $file) {
            if (! $file->isValid()) continue;

            $ext = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, $allowedExt)) continue;

            $dir      = 'ticket-attachments/' . $reply->ticket_id;
            $filename = $file->store($dir);

            TicketAttachment::create([
                'ticket_reply_id' => $reply->id,
                'filename'        => basename($filename),
                'original_name'   => $file->getClientOriginalName(),
                'mime_type'       => $file->getMimeType(),
                'size'            => $file->getSize(),
                'path'            => $filename,
            ]);
        }
    }

    private function notifyStaff(Ticket $ticket, TicketReply $reply): void
    {
        try {
            $adminEmail = config('mail.from.address');
            Mail::to($adminEmail)->send(new NewTicketMail($ticket, $reply));
        } catch (\Throwable) {
            // Don't block the request
        }
    }
}
