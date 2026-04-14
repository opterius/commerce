<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    @php
        $accent = $invoiceS['accent_color'] ?: $branding['primary_color'];
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11pt;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .wrap { padding: 40px 40px 50px; }

        /* Header */
        .hdr { width: 100%; margin-bottom: 36px; }
        .hdr td { vertical-align: top; }
        .brand-name { font-size: 18pt; font-weight: 700; color: #111827; }
        .invoice-tag {
            display: inline-block;
            padding: 6px 14px;
            background: {{ $accent }};
            color: #ffffff;
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            border-radius: 4px;
        }
        .invoice-num {
            font-size: 13pt;
            font-weight: 700;
            color: #111827;
            margin-top: 10px;
        }

        /* From / To */
        .parties { width: 100%; margin-bottom: 28px; }
        .parties td { vertical-align: top; padding: 0; }
        .parties .label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 6px;
        }
        .parties .name { font-weight: 700; color: #111827; font-size: 11pt; }
        .parties p { margin: 2px 0; color: #4b5563; font-size: 10pt; }

        /* Meta */
        .meta { width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 24px; }
        .meta td { padding: 10px 14px; font-size: 10pt; }
        .meta .meta-label { color: #9ca3af; font-size: 8pt; text-transform: uppercase; letter-spacing: 1px; }
        .meta .meta-val { color: #111827; font-weight: 600; margin-top: 2px; }

        /* Items */
        .items { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .items th {
            background: #f9fafb;
            color: #6b7280;
            font-size: 8pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 14px;
            border-bottom: 2px solid {{ $accent }};
            text-align: left;
        }
        .items th.r, .items td.r { text-align: right; }
        .items td { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; font-size: 10pt; color: #1f2937; }

        /* Totals */
        .tot { width: 280px; margin-left: auto; margin-top: 8px; border-collapse: collapse; }
        .tot td { padding: 6px 14px; font-size: 10pt; }
        .tot td.l { color: #6b7280; }
        .tot td.r { text-align: right; color: #111827; font-weight: 600; }
        .tot tr.grand td { border-top: 2px solid {{ $accent }}; padding-top: 10px; font-size: 12pt; font-weight: 700; color: {{ $accent }}; }

        /* Status badge */
        .status {
            display: inline-block;
            padding: 4px 10px;
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
        }
        .status-paid    { background: #dcfce7; color: #166534; }
        .status-unpaid  { background: #fef3c7; color: #92400e; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #f3f4f6; color: #4b5563; }
        .status-refunded { background: #dbeafe; color: #1e40af; }

        /* Footer */
        .footer { margin-top: 34px; padding-top: 18px; border-top: 1px solid #e5e7eb; font-size: 9pt; color: #6b7280; line-height: 1.5; }
        .footer h4 { font-size: 9pt; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin: 0 0 4px 0; }
        .footer p { margin: 0 0 10px 0; white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="wrap">

    {{-- Header --}}
    <table class="hdr">
        <tr>
            <td>
                @if ($invoiceS['show_logo'] && $branding['logo'] && file_exists(storage_path('app/public/' . $branding['logo'])))
                    <img src="{{ storage_path('app/public/' . $branding['logo']) }}" alt="{{ $branding['name'] }}" style="max-height: 50px;">
                @else
                    <div class="brand-name">{{ $branding['name'] ?: $company['name'] }}</div>
                @endif
            </td>
            <td style="text-align: right;">
                <span class="invoice-tag">{{ __('invoices.invoice') ?: 'Invoice' }}</span>
                <div class="invoice-num">{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 8px;">
                    <span class="status status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- From / To --}}
    <table class="parties">
        <tr>
            <td style="width: 50%;">
                <p class="label">From</p>
                <p class="name">{{ $company['name'] }}</p>
                @if ($company['address']) <p>{{ $company['address'] }}</p> @endif
                @if ($company['city'] || $company['postcode'])
                    <p>{{ trim($company['postcode'] . ' ' . $company['city']) }}{{ $company['state'] ? ', ' . $company['state'] : '' }}</p>
                @endif
                @if ($company['country']) <p>{{ $company['country'] }}</p> @endif
                @if ($company['email'])   <p>{{ $company['email'] }}</p> @endif
                @if ($company['tax_id'])  <p>Tax ID: {{ $company['tax_id'] }}</p> @endif
            </td>
            <td style="width: 50%;">
                <p class="label">Bill To</p>
                <p class="name">{{ $invoice->client->name ?? '' }}</p>
                @if ($invoice->client?->company) <p>{{ $invoice->client->company }}</p> @endif
                @if ($invoice->client?->address) <p>{{ $invoice->client->address }}</p> @endif
                @if ($invoice->client?->city || $invoice->client?->postcode)
                    <p>{{ trim(($invoice->client->postcode ?? '') . ' ' . ($invoice->client->city ?? '')) }}{{ $invoice->client?->state ? ', ' . $invoice->client->state : '' }}</p>
                @endif
                @if ($invoice->client?->country) <p>{{ $invoice->client->country }}</p> @endif
                @if ($invoice->client?->email)   <p>{{ $invoice->client->email }}</p> @endif
                @if ($invoice->client?->tax_id)  <p>Tax ID: {{ $invoice->client->tax_id }}</p> @endif
            </td>
        </tr>
    </table>

    {{-- Meta --}}
    <table class="meta">
        <tr>
            <td style="width: 33%;">
                <div class="meta-label">Issued</div>
                <div class="meta-val">{{ $invoice->created_at->format('M j, Y') }}</div>
            </td>
            <td style="width: 33%; border-left: 1px solid #e5e7eb;">
                <div class="meta-label">Due</div>
                <div class="meta-val">{{ \Illuminate\Support\Carbon::parse($invoice->due_date)->format('M j, Y') }}</div>
            </td>
            <td style="width: 34%; border-left: 1px solid #e5e7eb;">
                <div class="meta-label">Currency</div>
                <div class="meta-val">{{ $invoice->currency_code }}</div>
            </td>
        </tr>
    </table>

    {{-- Line items --}}
    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="r" style="width: 70px;">Qty</th>
                <th class="r" style="width: 120px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="r">{{ $item->quantity }}</td>
                    <td class="r">{{ number_format(($item->amount + $item->tax_amount) / 100, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="tot">
        <tr>
            <td class="l">Subtotal</td>
            <td class="r">{{ $invoice->currency_code }} {{ number_format($invoice->subtotal / 100, 2) }}</td>
        </tr>
        @if ($invoice->tax > 0)
            <tr>
                <td class="l">Tax</td>
                <td class="r">{{ $invoice->currency_code }} {{ number_format($invoice->tax / 100, 2) }}</td>
            </tr>
        @endif
        @if ($invoice->credit_applied > 0)
            <tr>
                <td class="l">Credit Applied</td>
                <td class="r">− {{ $invoice->currency_code }} {{ number_format($invoice->credit_applied / 100, 2) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td class="l">Total</td>
            <td class="r">{{ $invoice->currency_code }} {{ number_format(($invoice->total - $invoice->credit_applied) / 100, 2) }}</td>
        </tr>
    </table>

    {{-- Footer --}}
    @if ($invoiceS['payment_terms'] || $invoiceS['footer_text'] || $invoice->notes)
        <div class="footer">
            @if ($invoice->notes)
                <h4>Notes</h4>
                <p>{{ $invoice->notes }}</p>
            @endif
            @if ($invoiceS['payment_terms'])
                <h4>Payment Terms</h4>
                <p>{{ $invoiceS['payment_terms'] }}</p>
            @endif
            @if ($invoiceS['footer_text'])
                <p style="margin-top: 14px; text-align: center;">{{ $invoiceS['footer_text'] }}</p>
            @endif
        </div>
    @endif

</div>
</body>
</html>
