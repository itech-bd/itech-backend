<?php

use Illuminate\Support\Facades\Route;
use Modules\ContactMessages\Http\Controllers\Admin\ContactMessageController;
use Modules\ContactMessages\Http\Controllers\PublicContactController;

Route::middleware(['frontend.locale'])->group(function () {
    Route::get('/contact', [PublicContactController::class, 'show'])
        ->name('contact');

    Route::post('/contact', [PublicContactController::class, 'store'])
        ->name('contact.store');
});

Route::middleware(['auth', 'verified', 'role:admin', 'backend.locale'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/contact-messages', [ContactMessageController::class, 'index'])
            ->name('contact-messages.index');

        Route::delete('/contact-messages/bulk-destroy', [ContactMessageController::class, 'destroyBulk'])
            ->name('contact-messages.destroyBulk');

        Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])
            ->name('contact-messages.show');

        Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])
            ->name('contact-messages.destroy');
    });
