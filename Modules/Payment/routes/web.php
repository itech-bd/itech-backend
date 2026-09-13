<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;

Route::middleware(['web', 'auth:web', 'role:admin', 'backend.locale'])->group(function () {
    Route::get('dashboard/admin/payments', [PaymentController::class, 'index'])->name('dashboard.admin.payments.index');
    Route::get('dashboard/admin/payments/invoices/{order}/create', [PaymentController::class, 'create'])->name('dashboard.admin.payments.create');
    Route::post('dashboard/admin/payments/invoices/{order}', [PaymentController::class, 'store'])->name('dashboard.admin.payments.store');
    Route::patch('dashboard/admin/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('dashboard.admin.payments.reverse');
    Route::get('users/{student}/payments', [PaymentController::class, 'index'])->name('users.payments.index');
});
