<?php

namespace App\Console\Commands;

use App\Jobs\TerminateHostingAccountJob;
use App\Models\Service;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;

class CheckTerminationServicesCommand extends Command
{
    protected $signature   = 'commerce:check-termination-services';
    protected $description = 'Terminate services that have been suspended past the auto-close period.';

    public function handle(): int
    {
        $autoCloseDays = (int) \App\Models\Setting::get('auto_close_days', 14);
        $cutoff        = now()->subDays($autoCloseDays);

        $services = Service::where('status', 'suspended')
            ->where('suspended_at', '<=', $cutoff)
            ->with(['product', 'server'])
            ->get();

        $count = 0;
        foreach ($services as $service) {
            if ($service->needsProvisioning()) {
                TerminateHostingAccountJob::dispatch($service->id);
            } else {
                $service->update(['status' => 'terminated', 'terminated_at' => now()]);
            }

            ActivityLogger::log(
                'service.auto_terminate',
                'service',
                $service->id,
                $service->domain ?? "service #{$service->id}",
                "Terminated after {$autoCloseDays} days suspended."
            );

            $count++;
        }

        $this->info("Terminated {$count} service(s).");

        return Command::SUCCESS;
    }
}
