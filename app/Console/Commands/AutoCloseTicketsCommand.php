<?php

namespace App\Console\Commands;

use App\Mail\TicketAutoClosedMail;
use App\Models\Setting;
use App\Models\Ticket;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AutoCloseTicketsCommand extends Command
{
    protected $signature   = 'commerce:auto-close-tickets';
    protected $description = 'Close tickets that have been in "answered" status without a client reply for X days.';

    public function handle(): int
    {
        $days   = (int) Setting::get('ticket_auto_close_days', 5);
        $cutoff = now()->subDays($days);

        $tickets = Ticket::where('status', 'answered')
            ->where('last_reply_at', '<=', $cutoff)
            ->with(['client', 'department'])
            ->get();

        $count = 0;
        foreach ($tickets as $ticket) {
            $ticket->update([
                'status'    => 'closed',
                'closed_at' => now(),
            ]);

            try {
                Mail::to($ticket->client->email)->send(new TicketAutoClosedMail($ticket));
            } catch (\Throwable) {
                // Non-critical
            }

            ActivityLogger::log(
                'ticket.auto_closed',
                'ticket',
                $ticket->id,
                $ticket->subject,
                "Auto-closed after {$days} days unanswered."
            );

            $count++;
        }

        $this->info("Auto-closed {$count} ticket(s).");

        return Command::SUCCESS;
    }
}
