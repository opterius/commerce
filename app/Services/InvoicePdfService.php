<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService
{
    public function render(Invoice $invoice): string
    {
        $invoice->loadMissing(['client', 'items']);

        $data = [
            'invoice'  => $invoice,
            'company'  => $this->companyData(),
            'branding' => $this->brandingData(),
            'invoiceS' => $this->invoiceSettings(),
        ];

        return Pdf::loadView('pdf.invoice', $data)
            ->setPaper('a4')
            ->output();
    }

    public function stream(Invoice $invoice): Response
    {
        $filename = $this->filename($invoice);
        return response($this->render($invoice), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function download(Invoice $invoice): Response
    {
        $filename = $this->filename($invoice);
        return response($this->render($invoice), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function filename(Invoice $invoice): string
    {
        return 'invoice-' . preg_replace('/[^a-zA-Z0-9\-_]/', '-', $invoice->invoice_number) . '.pdf';
    }

    private function companyData(): array
    {
        $s = Setting::getGroup('company');
        return [
            'name'     => $s['company_name']     ?? '',
            'address'  => $s['company_address']  ?? '',
            'city'     => $s['company_city']     ?? '',
            'state'    => $s['company_state']    ?? '',
            'postcode' => $s['company_postcode'] ?? '',
            'country'  => $s['company_country']  ?? '',
            'phone'    => $s['company_phone']    ?? '',
            'email'    => $s['company_email']    ?? '',
            'tax_id'   => $s['company_tax_id']   ?? '',
            'website'  => $s['company_website']  ?? '',
        ];
    }

    private function brandingData(): array
    {
        $s = Setting::getGroup('branding');
        return [
            'name'          => $s['brand_name']          ?? '',
            'logo'          => $s['brand_logo']          ?? null,
            'primary_color' => $s['brand_primary_color'] ?? '#4f46e5',
        ];
    }

    private function invoiceSettings(): array
    {
        $s = Setting::getGroup('invoices');
        return [
            'show_logo'    => ($s['invoice_show_logo'] ?? '1') === '1',
            'accent_color' => $s['invoice_accent_color'] ?? null,
            'footer_text'  => $s['invoice_footer_text'] ?? '',
            'payment_terms' => $s['invoice_payment_terms'] ?? '',
        ];
    }
}
