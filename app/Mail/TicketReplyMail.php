<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Ticket      $ticket,
        public readonly TicketReply $reply,
        public readonly string      $fromEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->fromEmail],
            subject: 'Re: [Ticket #' . $this->ticket->id . '] ' . $this->ticket->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tickets.reply',
        );
    }
}
