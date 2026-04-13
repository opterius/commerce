<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; font-size: 15px; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px;">

    <p>Hello {{ $ticket->client->first_name }},</p>

    <p>A reply has been added to your support ticket:</p>

    <div style="background:#f5f5f5; border-left: 4px solid #6366f1; padding: 16px; margin: 20px 0; border-radius: 4px;">
        <p style="margin:0 0 8px 0; font-weight:bold;">{{ $ticket->subject }}</p>
        <div style="white-space: pre-wrap;">{{ strip_tags($reply->body) }}</div>
    </div>

    <p>
        <a href="{{ route('client.tickets.show', $ticket) }}" style="display:inline-block;background:#6366f1;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">
            View Ticket #{{ $ticket->id }}
        </a>
    </p>

    <p style="color:#888; font-size:13px;">
        Ticket #{{ $ticket->id }} &bull; {{ $ticket->department?->name }} &bull; {{ config('app.name') }}
    </p>
</body>
</html>
