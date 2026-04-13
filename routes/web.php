<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use Illuminate\Support\Facades\Route;

// ── Root redirect ────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('staff.login'));

// ── Staff Auth ───────────────────────────────────────────────────────────────
Route::get('/admin/login', [Auth\StaffLoginController::class, 'showLoginForm'])->name('staff.login');
Route::post('/admin/login', [Auth\StaffLoginController::class, 'login']);
Route::post('/admin/logout', [Auth\StaffLoginController::class, 'logout'])->name('staff.logout');

Route::get('/admin/forgot-password', [Auth\StaffForgotPasswordController::class, 'showForm'])->name('staff.password.request');
Route::post('/admin/forgot-password', [Auth\StaffForgotPasswordController::class, 'sendResetLink'])->name('staff.password.email');
Route::get('/admin/reset-password/{token}', [Auth\StaffResetPasswordController::class, 'showForm'])->name('staff.password.reset');
Route::post('/admin/reset-password', [Auth\StaffResetPasswordController::class, 'reset'])->name('staff.password.update');

// ── Client Auth ──────────────────────────────────────────────────────────────
Route::get('/client/login', [Auth\ClientLoginController::class, 'showLoginForm'])->name('client.login');
Route::post('/client/login', [Auth\ClientLoginController::class, 'login']);
Route::post('/client/logout', [Auth\ClientLoginController::class, 'logout'])->name('client.logout');

Route::get('/client/forgot-password', [Auth\ClientForgotPasswordController::class, 'showForm'])->name('client.password.request');
Route::post('/client/forgot-password', [Auth\ClientForgotPasswordController::class, 'sendResetLink'])->name('client.password.email');
Route::get('/client/reset-password/{token}', [Auth\ClientResetPasswordController::class, 'showForm'])->name('client.password.reset');
Route::post('/client/reset-password', [Auth\ClientResetPasswordController::class, 'reset'])->name('client.password.update');

// ── Admin Panel ──────────────────────────────────────────────────────────────
Route::prefix('admin')->middleware(['auth:staff', 'staff'])->name('admin.')->group(function () {

    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Clients
    Route::resource('clients', Admin\ClientController::class);
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

    // Settings
    Route::get('/settings/{category?}', [Admin\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/{category}', [Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/currencies/{currency}', [Admin\SettingsController::class, 'deleteCurrency'])->name('settings.currencies.destroy');
});

// ── Client Portal ────────────────────────────────────────────────────────────
Route::prefix('client')->middleware(['auth:client', 'client'])->name('client.')->group(function () {

    Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [Client\ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [Client\ProfileController::class, 'update'])->name('profile.update');

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
