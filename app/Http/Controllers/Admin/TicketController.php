<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TicketReplyMail;
use App\Models\CannedResponse;
use App\Models\Staff;
use App\Models\Ticket;
use App\Models\TicketDepartment;
use App\Models\TicketReply;
use App\Models\TicketTag;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['client', 'department', 'assignedStaff'])
            ->orderByDesc('last_reply_at');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_staff_id')) {
            $query->where('assigned_staff_id', $request->assigned_staff_id);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('subject', 'like', $term)
                  ->orWhereHas('client', fn ($c) =>
                      $c->where('email', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                  );
            });
        }

        $tickets     = $query->paginate(config('commerce.pagination', 25))->withQueryString();
        $departments = TicketDepartment::orderBy('sort_order')->get();
        $staffList   = Staff::orderBy('name')->get();

        return view('admin.tickets.index', compact('tickets', 'departments', 'staffList'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'client',
            'department',
            'assignedStaff',
            'replies.staff',
            'replies.client',
            'replies.attachments',
            'tags',
        ]);

        $departments    = TicketDepartment::where('is_active', true)->orderBy('sort_order')->get();
        $staffList      = Staff::orderBy('name')->get();
        $cannedResponses = CannedResponse::where(function ($q) use ($ticket) {
            $q->whereNull('department_id')
              ->orWhere('department_id', $ticket->department_id);
        })->orderBy('title')->get();
        $tags = TicketTag::orderBy('name')->get();

        return view('admin.tickets.show', compact(
            'ticket', 'departments', 'staffList', 'cannedResponses', 'tags'
        ));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'body'             => 'required|string|max:65535',
            'is_internal_note' => 'boolean',
            'attachments.*'    => 'nullable|file|max:' . (int) (\App\Models\Setting::get('ticket_max_attachment_kb', 10240)),
        ]);

        $isInternal = $request->boolean('is_internal_note');

        $reply = TicketReply::create([
            'ticket_id'        => $ticket->id,
            'staff_id'         => auth('staff')->id(),
            'body'             => $request->body,
            'is_internal_note' => $isInternal,
        ]);

        $this->storeAttachments($request, $reply);

        // Update ticket status & last_reply_at
        if (! $isInternal) {
            $ticket->update([
                'status'        => 'answered',
                'last_reply_at' => now(),
            ]);

            // Notify client
            $this->notifyClient($ticket, $reply);
        } else {
            $ticket->touch();
        }

        ActivityLogger::log('ticket.reply', 'ticket', $ticket->id, $ticket->subject, null, [
            'is_internal' => $isInternal,
        ]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', $isInternal ? 'Note added.' : 'Reply sent.');
    }

    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status'             => 'nullable|in:open,answered,customer_reply,on_hold,closed',
            'priority'           => 'nullable|in:low,medium,high,urgent',
            'assigned_staff_id'  => 'nullable|exists:staff,id',
            'department_id'      => 'nullable|exists:ticket_departments,id',
            'tags'               => 'nullable|array',
            'tags.*'             => 'exists:ticket_tags,id',
        ]);

        $wasOpen   = $ticket->status !== 'closed';
        $nowClosed = isset($data['status']) && $data['status'] === 'closed';

        if ($nowClosed && $wasOpen) {
            $data['closed_at'] = now();
        } elseif (isset($data['status']) && $data['status'] !== 'closed') {
            $data['closed_at'] = null;
        }

        $ticket->update(array_filter($data, fn ($v, $k) => $k !== 'tags', ARRAY_FILTER_USE_BOTH));

        if (isset($data['tags'])) {
            $ticket->tags()->sync($data['tags']);
        }

        ActivityLogger::log('ticket.updated', 'ticket', $ticket->id, $ticket->subject, null, $data);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket updated.');
    }

    public function merge(Request $request, Ticket $ticket)
    {
        $request->validate([
            'merge_into_id' => 'required|exists:tickets,id|different:id',
        ]);

        $target = Ticket::findOrFail($request->merge_into_id);

        // Move all replies to the target ticket
        TicketReply::where('ticket_id', $ticket->id)
            ->update(['ticket_id' => $target->id]);

        $target->update(['last_reply_at' => now()]);

        ActivityLogger::log('ticket.merged', 'ticket', $ticket->id, $ticket->subject, null, [
            'merged_into' => $target->id,
        ]);

        $ticket->delete();

        return redirect()->route('admin.tickets.show', $target)
            ->with('success', "Ticket #{$ticket->id} merged into #{$target->id}.");
    }

    public function downloadAttachment(Ticket $ticket, \App\Models\TicketAttachment $attachment)
    {
        // Ensure the attachment belongs to this ticket
        abort_if($attachment->reply->ticket_id !== $ticket->id, 403);

        return Storage::download($attachment->path, $attachment->original_name);
    }

    private function storeAttachments(Request $request, TicketReply $reply): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        $allowedExt = array_map('trim', explode(',',
            \App\Models\Setting::get('ticket_allowed_extensions', 'jpg,jpeg,png,gif,pdf,txt,zip,doc,docx')
        ));

        foreach ($request->file('attachments') as $file) {
            if (! $file->isValid()) continue;

            $ext = strtolower($file->getClientOriginalExtension());
            if (! in_array($ext, $allowedExt)) continue;

            $dir      = 'ticket-attachments/' . $reply->ticket_id;
            $filename = $file->store($dir);

            \App\Models\TicketAttachment::create([
                'ticket_reply_id' => $reply->id,
                'filename'        => basename($filename),
                'original_name'   => $file->getClientOriginalName(),
                'mime_type'       => $file->getMimeType(),
                'size'            => $file->getSize(),
                'path'            => $filename,
            ]);
        }
    }

    private function notifyClient(Ticket $ticket, TicketReply $reply): void
    {
        try {
            $fromEmail = $ticket->department?->email ?: config('mail.from.address');
            Mail::to($ticket->client->email)
                ->send(new TicketReplyMail($ticket, $reply, $fromEmail));
        } catch (\Throwable) {
            // Don't break the request if mail fails
        }
    }
}
