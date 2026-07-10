<?php

use Illuminate\Support\Facades\Route;
use Modules\NewsUpdates\Http\Controllers\Admin\NewsUpdatesAdminController;

Route::middleware(['auth', 'verified', 'backend.locale'])->group(function (): void {
    Route::middleware(['role:admin'])
        ->prefix('dashboard/admin')
        ->name('dashboard.admin.')
        ->group(function (): void {
            Route::get('news', [NewsUpdatesAdminController::class, 'index'])->name('news.index');
            Route::get('news/create', [NewsUpdatesAdminController::class, 'create'])->name('news.create');
            Route::post('news', [NewsUpdatesAdminController::class, 'store'])->name('news.store');
            Route::get('news/{newsUpdate}/edit', [NewsUpdatesAdminController::class, 'edit'])->name('news.edit');
            Route::patch('news/{newsUpdate}', [NewsUpdatesAdminController::class, 'update'])->name('news.update');
            Route::delete('news/{newsUpdate}', [NewsUpdatesAdminController::class, 'destroy'])->name('news.destroy');
        });
});
