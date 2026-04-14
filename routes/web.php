<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Webhook\GatewayWebhookController;

// ── Gateway Webhooks (public, no auth, no CSRF) ──────────────────────────────
Route::post('/webhooks/{gateway}', [GatewayWebhookController::class, 'handle'])
    ->name('webhooks.gateway')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── Public portal ────────────────────────────────────────────────────────────
Route::get('/', [PortalController::class, 'home'])->name('home');

// Knowledge Base
Route::get('/kb',                              [PortalController::class, 'kbIndex'])->name('portal.kb');
Route::get('/kb/{category:slug}',              [PortalController::class, 'kbCategory'])->name('portal.kb.category');
Route::get('/kb/{category:slug}/{article:slug}', [PortalController::class, 'kbArticle'])->name('portal.kb.article');

// FAQ
Route::get('/faq', [PortalController::class, 'faq'])->name('portal.faq');

// Contact
Route::get('/contact',  [PortalController::class, 'contact'])->name('portal.contact');
Route::post('/contact', [PortalController::class, 'contactSubmit'])->name('portal.contact.submit');

// Announcements
Route::get('/announcements',                    [PortalController::class, 'announcements'])->name('portal.announcements');
Route::get('/announcements/{announcement:slug}', [PortalController::class, 'announcement'])->name('portal.announcement');

// Status page
Route::get('/status', [PortalController::class, 'status'])->name('portal.status');

// ── Staff Auth ───────────────────────────────────────────────────────────────
Route::get('/admin/login', [Auth\StaffLoginController::class, 'showLoginForm'])->name('staff.login');
Route::post('/admin/login', [Auth\StaffLoginController::class, 'login']);
Route::post('/admin/logout', [Auth\StaffLoginController::class, 'logout'])->name('admin.logout');

// Staff 2FA challenge (public — no staff middleware)
Route::get('/admin/two-factor-challenge', [Auth\StaffTwoFactorChallengeController::class, 'show'])->name('staff.two-factor.challenge');
Route::post('/admin/two-factor-challenge', [Auth\StaffTwoFactorChallengeController::class, 'store']);

Route::get('/admin/forgot-password', [Auth\StaffForgotPasswordController::class, 'showForm'])->name('staff.password.request');
Route::post('/admin/forgot-password', [Auth\StaffForgotPasswordController::class, 'sendResetLink'])->name('staff.password.email');
Route::get('/admin/reset-password/{token}', [Auth\StaffResetPasswordController::class, 'showForm'])->name('staff.password.reset');
Route::post('/admin/reset-password', [Auth\StaffResetPasswordController::class, 'reset'])->name('staff.password.update');

// ── Client Auth ──────────────────────────────────────────────────────────────
Route::get('/client/login', [Auth\ClientLoginController::class, 'showLoginForm'])->name('client.login');
Route::post('/client/login', [Auth\ClientLoginController::class, 'login']);
Route::post('/client/logout', [Auth\ClientLoginController::class, 'logout'])->name('client.logout');

// Client 2FA challenge (public — no client middleware)
Route::get('/client/two-factor-challenge', [Auth\ClientTwoFactorChallengeController::class, 'show'])->name('client.two-factor.challenge');
Route::post('/client/two-factor-challenge', [Auth\ClientTwoFactorChallengeController::class, 'store']);

Route::get('/client/forgot-password', [Auth\ClientForgotPasswordController::class, 'showForm'])->name('client.password.request');
Route::post('/client/forgot-password', [Auth\ClientForgotPasswordController::class, 'sendResetLink'])->name('client.password.email');
Route::get('/client/reset-password/{token}', [Auth\ClientResetPasswordController::class, 'showForm'])->name('client.password.reset');
Route::post('/client/reset-password', [Auth\ClientResetPasswordController::class, 'reset'])->name('client.password.update');

// ── Admin Panel ──────────────────────────────────────────────────────────────
Route::prefix('admin')->middleware(['auth:staff', 'staff'])->name('admin.')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', fn () => redirect()->route('admin.two-factor.show'))->name('profile');

    // Clients
    Route::resource('clients', Admin\ClientController::class);

    // Client Groups + per-product price overrides
    Route::resource('client-groups', Admin\ClientGroupController::class)->except('show');
    Route::post('/client-groups/{clientGroup}/prices', [Admin\ClientGroupController::class, 'storePrice'])->name('client-groups.prices.store');
    Route::delete('/client-groups/{clientGroup}/prices/{price}', [Admin\ClientGroupController::class, 'destroyPrice'])->name('client-groups.prices.destroy');
    Route::post('/clients/{client}/notes', [Admin\ClientNoteController::class, 'store'])->name('clients.notes.store');
    Route::delete('/clients/{client}/notes/{note}', [Admin\ClientNoteController::class, 'destroy'])->name('clients.notes.destroy');
    Route::post('/clients/{client}/contacts', [Admin\ClientContactController::class, 'store'])->name('clients.contacts.store');
    Route::put('/clients/{client}/contacts/{contact}', [Admin\ClientContactController::class, 'update'])->name('clients.contacts.update');
    Route::delete('/clients/{client}/contacts/{contact}', [Admin\ClientContactController::class, 'destroy'])->name('clients.contacts.destroy');

    // Impersonation
    Route::post('/login-as-client/{client}', function (\App\Models\Client $client) {
        session(['impersonating_staff_id' => auth('staff')->id()]);
        auth('client')->login($client);
        return redirect()->route('client.dashboard');
    })->name('login-as-client');

    // Products
    Route::resource('products', Admin\ProductController::class);

    // Product Groups
    Route::resource('product-groups', Admin\ProductGroupController::class)->except('show');

    // Configurable Options
    Route::resource('configurable-options', Admin\ConfigurableOptionController::class)->except('edit');
    Route::post('/configurable-options/{group}/options', [Admin\ConfigurableOptionController::class, 'storeOption'])->name('configurable-options.options.store');
    Route::delete('/configurable-options/{group}/options/{option}', [Admin\ConfigurableOptionController::class, 'destroyOption'])->name('configurable-options.options.destroy');
    Route::post('/configurable-options/options/{option}/values', [Admin\ConfigurableOptionController::class, 'storeValue'])->name('configurable-options.values.store');
    Route::delete('/configurable-options/options/{option}/values/{value}', [Admin\ConfigurableOptionController::class, 'destroyValue'])->name('configurable-options.values.destroy');

    // Promo Codes
    Route::resource('promo-codes', Admin\PromoCodeController::class)->except('show');

    // Orders
    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Services
    Route::get('/services', [Admin\ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [Admin\ServiceController::class, 'show'])->name('services.show');
    Route::post('/services/{service}/provision', [Admin\ServiceController::class, 'provisionAction'])->name('services.provision');

    // Invoices
    Route::get('/invoices', [Admin\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [Admin\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [Admin\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/void', [Admin\InvoiceController::class, 'void'])->name('invoices.void');
    Route::post('/invoices/{invoice}/send', [Admin\InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{invoice}/manual-payment', [Admin\InvoiceController::class, 'manualPayment'])->name('invoices.manual-payment');
    Route::post('/invoices/{invoice}/apply-credit', [Admin\InvoiceController::class, 'applyCredit'])->name('invoices.apply-credit');

    // Payments
    Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');

    // Tax Rules
    Route::resource('tax-rules', Admin\TaxRuleController::class);

    // Infrastructure: Server Groups + Servers + Provisioning Log
    Route::resource('server-groups', Admin\ServerGroupController::class)->except('show');
    Route::resource('servers', Admin\ServerController::class)->except('show');
    Route::post('/servers/{server}/test', [Admin\ServerController::class, 'testConnection'])->name('servers.test');
    Route::get('/provisioning-log', [Admin\ProvisioningLogController::class, 'index'])->name('provisioning-log.index');
    Route::get('/provisioning-log/{provisioningLog}', [Admin\ProvisioningLogController::class, 'show'])->name('provisioning-log.show');

    // Support Tickets
    Route::get('/tickets', [Admin\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [Admin\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [Admin\TicketController::class, 'reply'])->name('tickets.reply');
    Route::patch('/tickets/{ticket}', [Admin\TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{ticket}/merge', [Admin\TicketController::class, 'merge'])->name('tickets.merge');
    Route::get('/tickets/{ticket}/attachments/{attachment}', [Admin\TicketController::class, 'downloadAttachment'])->name('tickets.attachment');
    Route::resource('ticket-departments', Admin\TicketDepartmentController::class)->except('show');
    Route::resource('canned-responses', Admin\CannedResponseController::class)->except('show');

    // Domains (admin)
    Route::get('/domains', [Admin\DomainController::class, 'index'])->name('domains.index');
    Route::get('/domains/{domain}', [Admin\DomainController::class, 'show'])->name('domains.show');
    Route::patch('/domains/{domain}/nameservers', [Admin\DomainController::class, 'updateNameservers'])->name('domains.nameservers');
    Route::patch('/domains/{domain}/privacy', [Admin\DomainController::class, 'togglePrivacy'])->name('domains.privacy');
    Route::patch('/domains/{domain}/lock', [Admin\DomainController::class, 'toggleLock'])->name('domains.lock');
    Route::post('/domains/{domain}/epp', [Admin\DomainController::class, 'getEppCode'])->name('domains.epp');
    Route::post('/domains/{domain}/sync', [Admin\DomainController::class, 'sync'])->name('domains.sync');
    Route::patch('/domains/{domain}/notes', [Admin\DomainController::class, 'updateNotes'])->name('domains.notes');
    Route::post('/domains/test-connection', [Admin\DomainController::class, 'testConnection'])->name('domains.test-connection');

    // TLD Manager
    Route::resource('tlds', Admin\TldController::class)->except('show');

    // Staff Management
    Route::resource('staff', Admin\StaffController::class)->except('show');

    // Email Templates
    Route::resource('email-templates', Admin\EmailTemplateController::class)->except('show');

    // Content: Knowledge Base, FAQ, Contact Messages
    Route::resource('kb-categories', Admin\KbCategoryController::class)->except('show');
    Route::resource('kb-articles',   Admin\KbArticleController::class)->except('show');
    Route::resource('faqs',          Admin\FaqController::class)->except('show');
    Route::get('/contact-messages',              [Admin\ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [Admin\ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::delete('/contact-messages/{contactMessage}', [Admin\ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    Route::resource('announcements',    Admin\AnnouncementController::class)->except('show');
    Route::resource('service-statuses', Admin\ServiceStatusController::class)->except('show');

    // Settings
    Route::get('/settings/{category?}', [Admin\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/{category}', [Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/currencies/{currency}', [Admin\SettingsController::class, 'deleteCurrency'])->name('settings.currencies.destroy');

    // Reports
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [Admin\ReportController::class, 'exportCsv'])->name('reports.export-csv');

    // Service Upgrade Requests
    Route::get('/service-upgrades', [Admin\ServiceUpgradeController::class, 'index'])->name('service-upgrades.index');
    Route::post('/service-upgrades/{upgradeRequest}/approve', [Admin\ServiceUpgradeController::class, 'approve'])->name('service-upgrades.approve');
    Route::post('/service-upgrades/{upgradeRequest}/reject', [Admin\ServiceUpgradeController::class, 'reject'])->name('service-upgrades.reject');

    // Staff 2FA profile
    Route::get('/profile/two-factor', [Auth\StaffTwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/profile/two-factor/enable', [Auth\StaffTwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/profile/two-factor/confirm', [Auth\StaffTwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/profile/two-factor/disable', [Auth\StaffTwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('/profile/two-factor/recovery-codes', [Auth\StaffTwoFactorController::class, 'regenerateCodes'])->name('two-factor.recovery-codes');
});

// ── Client Portal ────────────────────────────────────────────────────────────
Route::prefix('client')->middleware(['auth:client', 'client'])->name('client.')->group(function () {

    Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [Client\ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [Client\ProfileController::class, 'update'])->name('profile.update');

    // Services
    Route::get('/services', [Client\ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [Client\ServiceController::class, 'show'])->name('services.show');

    // Invoices
    Route::get('/invoices', [Client\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [Client\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [Client\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/pay/{gateway?}', [Client\InvoiceController::class, 'pay'])->name('invoices.pay');
    Route::post('/invoices/{invoice}/pay/{gateway}', [Client\InvoiceController::class, 'processPayment'])->name('invoices.process-payment');

    // Store
    Route::get('/store', [Client\StoreController::class, 'index'])->name('store.index');
    Route::post('/store/promo-check', [Client\StoreController::class, 'promoCheck'])->name('store.promo-check');
    Route::get('/store/{product:slug}', [Client\StoreController::class, 'show'])->name('store.show');
    Route::post('/store/{product:slug}', [Client\StoreController::class, 'order'])->name('store.order');

    // Orders
    Route::get('/orders', [Client\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Client\OrderController::class, 'show'])->name('orders.show');

    // Domains (client)
    Route::get('/domains', [Client\DomainController::class, 'index'])->name('domains.index');
    Route::get('/domains/search', [Client\DomainController::class, 'search'])->name('domains.search');
    Route::get('/domains/register', [Client\DomainController::class, 'register'])->name('domains.register');
    Route::post('/domains', [Client\DomainController::class, 'store'])->name('domains.store');
    Route::get('/domains/{domain}', [Client\DomainController::class, 'show'])->name('domains.show');
    Route::patch('/domains/{domain}/nameservers', [Client\DomainController::class, 'updateNameservers'])->name('domains.nameservers');
    Route::patch('/domains/{domain}/privacy', [Client\DomainController::class, 'togglePrivacy'])->name('domains.privacy');
    Route::patch('/domains/{domain}/auto-renew', [Client\DomainController::class, 'toggleAutoRenew'])->name('domains.auto-renew');
    Route::post('/domains/{domain}/epp', [Client\DomainController::class, 'requestEppCode'])->name('domains.epp');

    // Tickets
    Route::get('/tickets', [Client\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [Client\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [Client\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [Client\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [Client\TicketController::class, 'reply'])->name('tickets.reply');
    Route::patch('/tickets/{ticket}/close', [Client\TicketController::class, 'close'])->name('tickets.close');
    Route::get('/tickets/{ticket}/attachments/{attachment}', [Client\TicketController::class, 'downloadAttachment'])->name('tickets.attachment');

    // Service Upgrades / Downgrades
    Route::get('/services/{service}/upgrade', [Client\ServiceUpgradeController::class, 'show'])->name('services.upgrade');
    Route::post('/services/{service}/upgrade', [Client\ServiceUpgradeController::class, 'store'])->name('services.upgrade.store');

    // Client 2FA settings
    Route::get('/security/two-factor', [Client\ClientTwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/security/two-factor/enable', [Client\ClientTwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/security/two-factor/confirm', [Client\ClientTwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/security/two-factor/disable', [Client\ClientTwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('/security/two-factor/recovery-codes', [Client\ClientTwoFactorController::class, 'regenerateCodes'])->name('two-factor.recovery-codes');

    // Return from impersonation
    Route::post('/return-to-admin', function () {
        $staffId = session('impersonating_staff_id');
        if ($staffId) {
            auth('client')->logout();
            session()->forget('impersonating_staff_id');
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('client.dashboard');
    })->name('return-to-admin');
});
