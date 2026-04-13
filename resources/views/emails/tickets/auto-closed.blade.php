<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #333; font-size: 15px; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px;">

    <p>Hello {{ $ticket->client->first_name }},</p>

    <p>
        Your support ticket <strong>#{{ $ticket->id }} — {{ $ticket->subject }}</strong> has been
        automatically closed as it has been marked as answered and there was no reply for several days.
    </p>

    <p>
        If you still need help, please open a new ticket or reply to this email to re-open the ticket.
    </p>

    <p>
        <a href="{{ route('client.tickets.create') }}" style="display:inline-block;background:#6366f1;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">
            Open New Ticket
        </a>
    </p>

    <p style="color:#888; font-size:13px;">{{ config('app.name') }}</p>
</body>
</html>
