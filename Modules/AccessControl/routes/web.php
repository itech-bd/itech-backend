<?php

use Illuminate\Support\Facades\Route;
use Modules\AccessControl\Http\Controllers\PermissionController;
use Modules\AccessControl\Http\Controllers\RoleController;
use Modules\AccessControl\Http\Controllers\UserController;

Route::middleware(['auth', 'role:admin', 'backend.locale'])->group(
    function () {
        Route::resource('permissions', PermissionController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);

        Route::get('users/{user}/invoices', [UserController::class, 'invoices'])
            ->name('users.invoices.index');
        Route::get('users/{user}/invoices/{order}', [UserController::class, 'invoiceShow'])
            ->name('users.invoices.show');
        Route::get('users/{user}/invoices/{order}/download', [UserController::class, 'invoiceDownload'])
            ->name('users.invoices.download');
    }
);
