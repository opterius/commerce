<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'mailable',
        'locale',
        'subject',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Find a template for the given mailable and locale,
     * falling back to the app's default locale.
     */
    public static function findFor(string $mailable, string $locale): ?static
    {
        $template = static::where('mailable', $mailable)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();

        if (! $template && $locale !== config('app.locale')) {
            $template = static::where('mailable', $mailable)
                ->where('locale', config('app.locale'))
                ->where('is_active', true)
                ->first();
        }

        return $template;
    }

    /**
     * All registered mailable types with their available variables.
     */
    public static function mailables(): array
    {
        return [
            'client.welcome'       => ['{client_name}', '{login_url}', '{company_name}'],
            'invoice.generated'    => ['{client_name}', '{invoice_number}', '{invoice_total}', '{invoice_due_date}', '{invoice_url}', '{company_name}'],
            'invoice.overdue'      => ['{client_name}', '{invoice_number}', '{invoice_total}', '{invoice_due_date}', '{invoice_url}', '{grace_period_days}', '{company_name}'],
            'invoice.paid'         => ['{client_name}', '{invoice_number}', '{invoice_total}', '{company_name}'],
            'service.activated'    => ['{client_name}', '{service_name}', '{service_domain}', '{company_name}'],
            'service.suspended'    => ['{client_name}', '{service_name}', '{service_domain}', '{invoice_url}', '{company_name}'],
            'service.unsuspended'  => ['{client_name}', '{service_name}', '{service_domain}', '{company_name}'],
            'service.terminated'   => ['{client_name}', '{service_name}', '{service_domain}', '{company_name}'],
        ];
    }
}
