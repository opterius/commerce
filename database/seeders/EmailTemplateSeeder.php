<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'mailable' => 'client.welcome',
                'subject'  => 'Welcome to {company_name}',
                'body'     => '<p>Hi {client_name},</p>
<p>Welcome! Your account has been created successfully.</p>
<p>You can log in to your client portal at any time using the link below:</p>
<p><a href="{login_url}" class="btn">Log In to Your Account</a></p>
<p>If you have any questions, please don\'t hesitate to open a support ticket.</p>
<p>Thanks,<br>{company_name}</p>',
            ],
            [
                'mailable' => 'invoice.generated',
                'subject'  => 'Invoice {invoice_number} — {invoice_total} due on {invoice_due_date}',
                'body'     => '<p>Hi {client_name},</p>
<p>A new invoice has been generated on your account.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:8px 0;color:#6b7280;font-size:14px;">Invoice number</td><td style="padding:8px 0;font-weight:600;">{invoice_number}</td></tr>
    <tr><td style="padding:8px 0;color:#6b7280;font-size:14px;">Amount due</td><td style="padding:8px 0;font-weight:600;">{invoice_total}</td></tr>
    <tr><td style="padding:8px 0;color:#6b7280;font-size:14px;">Due date</td><td style="padding:8px 0;font-weight:600;">{invoice_due_date}</td></tr>
</table>
<p><a href="{invoice_url}" class="btn">View &amp; Pay Invoice</a></p>
<p>Thanks,<br>{company_name}</p>',
            ],
            [
                'mailable' => 'invoice.overdue',
                'subject'  => 'Reminder: Invoice {invoice_number} is overdue',
                'body'     => '<p>Hi {client_name},</p>
<p>This is a reminder that invoice <strong>{invoice_number}</strong> for <strong>{invoice_total}</strong> was due on <strong>{invoice_due_date}</strong> and remains unpaid.</p>
<p>Please settle this invoice within the next <strong>{grace_period_days} days</strong> to avoid service suspension.</p>
<p><a href="{invoice_url}" class="btn">Pay Invoice Now</a></p>
<p>If you have already made payment, please disregard this email.</p>
<p>Thanks,<br>{company_name}</p>',
            ],
            [
                'mailable' => 'invoice.paid',
                'subject'  => 'Payment received for invoice {invoice_number}',
                'body'     => '<p>Hi {client_name},</p>
<p>We have received your payment of <strong>{invoice_total}</strong> for invoice <strong>{invoice_number}</strong>. Thank you!</p>
<p>You can view your invoice history at any time in your client portal.</p>
<p>Thanks,<br>{company_name}</p>',
            ],
            [
                'mailable' => 'service.activated',
                'subject'  => 'Your service is now active — {service_name}',
                'body'     => '<p>Hi {client_name},</p>
<p>Your service <strong>{service_name}</strong> has been activated successfully.</p>
<p>Domain / hostname: <strong>{service_domain}</strong></p>
<p>You can manage your service from your client portal at any time.</p>
<p>Thanks,<br>{company_name}</p>',
            ],
            [
                'mailable' => 'service.suspended',
                'subject'  => 'Your service has been suspended — {service_name}',
                'body'     => '<p>Hi {client_name},</p>
<p>Your service <strong>{service_name}</strong> ({service_domain}) has been suspended due to a past-due invoice.</p>
<p>To restore your service immediately, please settle the outstanding invoice:</p>
<p><a href="{invoice_url}" class="btn">Pay Invoice Now</a></p>
<p>Your service will be reactivated automatically once payment is received.</p>
<p>Thanks,<br>{company_name}</p>',
            ],
            [
                'mailable' => 'service.unsuspended',
                'subject'  => 'Your service has been reactivated — {service_name}',
                'body'     => '<p>Hi {client_name},</p>
<p>Your service <strong>{service_name}</strong> ({service_domain}) has been reactivated. Everything is back to normal.</p>
<p>Thank you for your payment.</p>
<p>Thanks,<br>{company_name}</p>',
            ],
            [
                'mailable' => 'service.terminated',
                'subject'  => 'Your service has been terminated — {service_name}',
                'body'     => '<p>Hi {client_name},</p>
<p>Your service <strong>{service_name}</strong> ({service_domain}) has been permanently terminated due to an extended period of non-payment.</p>
<p>If you believe this is an error or would like to discuss reactivation, please open a support ticket.</p>
<p>Thanks,<br>{company_name}</p>',
            ],
        ];

        foreach ($templates as $data) {
            EmailTemplate::updateOrCreate(
                ['mailable' => $data['mailable'], 'locale' => 'en'],
                ['subject' => $data['subject'], 'body' => $data['body'], 'is_active' => true],
            );
        }
    }
}
