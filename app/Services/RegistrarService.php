<?php

namespace App\Services;

use App\Contracts\DomainRegistrarModule;
use App\Models\Domain;
use App\Models\Setting;
use App\Registrar\Modules\EnomModule;
use App\Registrar\Modules\OpenSrsModule;
use App\Registrar\Modules\ResellerClubModule;

class RegistrarService
{
    private const MODULE_MAP = [
        'resellerclub' => ResellerClubModule::class,
        'enom'         => EnomModule::class,
        'opensrs'      => OpenSrsModule::class,
    ];

    public static function module(): DomainRegistrarModule
    {
        $slug  = Setting::get('registrar_module', 'resellerclub');
        $class = self::MODULE_MAP[$slug] ?? ResellerClubModule::class;

        return new $class();
    }

    public static function register(Domain $domain): void
    {
        $module   = self::module();
        $contacts = $domain->contacts->keyBy('type')->toArray();
        $years    = $domain->registrationYears();

        $result = $module->register($domain, $contacts, $years);

        if ($result->success) {
            $domain->update([
                'status'             => 'active',
                'registrar_order_id' => $result->data['registrar_order_id'] ?? null,
                'registration_date'  => now()->toDateString(),
                'next_due_date'      => now()->addYears($years)->toDateString(),
                'expiry_date'        => now()->addYears($years)->toDateString(),
            ]);
        } else {
            throw new \RuntimeException('Domain registration failed: ' . $result->error);
        }
    }

    public static function renew(Domain $domain): void
    {
        $module = self::module();
        $years  = $domain->registrationYears();

        $result = $module->renew($domain, $years);

        if ($result->success) {
            $newExpiry = ($domain->expiry_date ?? now())->addYears($years);
            $domain->update([
                'expiry_date'   => $newExpiry->toDateString(),
                'next_due_date' => $newExpiry->subMonth()->toDateString(),
                'last_due_date' => $domain->next_due_date?->toDateString(),
            ]);
        } else {
            throw new \RuntimeException('Domain renewal failed: ' . $result->error);
        }
    }

    public static function transfer(Domain $domain, string $eppCode): void
    {
        $module   = self::module();
        $contacts = $domain->contacts->keyBy('type')->toArray();

        $result = $module->transfer($domain, $eppCode, $contacts);

        if ($result->success) {
            $domain->update([
                'status'             => 'active',
                'registrar_order_id' => $result->data['registrar_order_id'] ?? null,
                'registration_date'  => now()->toDateString(),
            ]);
        } else {
            throw new \RuntimeException('Domain transfer failed: ' . $result->error);
        }
    }

    public static function sync(Domain $domain): void
    {
        $module = self::module();
        $result = $module->getDomainInfo($domain);

        if ($result->success) {
            $updates = array_filter($result->data, fn($v) => $v !== null);
            if (! empty($updates)) {
                $domain->update($updates);
            }
        }
    }
}
