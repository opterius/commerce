<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; font-size: 15px; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px;">

    <p>A new support ticket has been submitted.</p>

    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
        <tr><td style="padding:6px;color:#666;width:120px;">Ticket #</td><td style="padding:6px;font-weight:bold;">{{ $ticket->id }}</td></tr>
        <tr><td style="padding:6px;color:#666;">Subject</td><td style="padding:6px;">{{ $ticket->subject }}</td></tr>
        <tr><td style="padding:6px;color:#666;">Department</td><td style="padding:6px;">{{ $ticket->department?->name }}</td></tr>
        <tr><td style="padding:6px;color:#666;">Client</td><td style="padding:6px;">{{ $ticket->client->full_name }} &lt;{{ $ticket->client->email }}&gt;</td></tr>
        <tr><td style="padding:6px;color:#666;">Priority</td><td style="padding:6px;">{{ ucfirst($ticket->priority) }}</td></tr>
    </table>

    <div style="background:#f5f5f5; padding:16px; border-radius:4px; margin-bottom:20px; white-space:pre-wrap;">{{ strip_tags($reply->body) }}</div>

    <p>
        <a href="{{ route('admin.tickets.show', $ticket) }}" style="display:inline-block;background:#6366f1;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">
            View Ticket in Admin
        </a>
    </p>
</body>
</html>
