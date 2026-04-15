<?php

use App\Http\Controllers\Api\ClientApiController;
use Illuminate\Support\Facades\Route;

// All routes require a valid personal access token (Authorization: Bearer <token>)
Route::middleware('api.token')->prefix('v1')->name('api.')->group(function () {

    Route::get('/me',               [ClientApiController::class, 'me'])->name('me');
    Route::get('/services',         [ClientApiController::class, 'services'])->name('services');
    Route::get('/invoices',         [ClientApiController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{id}',    [ClientApiController::class, 'invoice'])->name('invoices.show');
    Route::get('/orders',           [ClientApiController::class, 'orders'])->name('orders');
    Route::get('/domains',          [ClientApiController::class, 'domains'])->name('domains');
});
