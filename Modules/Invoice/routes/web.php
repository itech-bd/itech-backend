<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoice\Http\Controllers\AdminInvoicesController;
use Modules\Invoice\Http\Controllers\MyInvoicesController;

$studentInvoicesIndexAction = [MyInvoicesController::class, 'index'];
$studentInvoicesShowAction = [MyInvoicesController::class, 'show'];
$studentInvoicesDownloadAction = [MyInvoicesController::class, 'download'];

$studentInvoiceRoutes = static function () use (
    $studentInvoicesIndexAction,
    $studentInvoicesShowAction,
    $studentInvoicesDownloadAction
): void {
    Route::get('invoices', $studentInvoicesIndexAction)->name('invoices.index');
    Route::get('invoices/{order}', $studentInvoicesShowAction)
        ->name('invoices.show');
    Route::get('invoices/{order}/download', $studentInvoicesDownloadAction)
        ->name('invoices.download');
};

$adminInvoicesIndexAction = [AdminInvoicesController::class, 'index'];
$adminInvoicesDownloadAction = [AdminInvoicesController::class, 'download'];
$adminInvoicesUpdateStatusAction = [AdminInvoicesController::class, 'updateStatus'];

$adminInvoiceRoutes = static function () use (
    $adminInvoicesIndexAction,
    $adminInvoicesDownloadAction,
    $adminInvoicesUpdateStatusAction
): void {
    Route::get('invoices', $adminInvoicesIndexAction)->name('invoices.index');
    Route::get('invoices/{order}/download', $adminInvoicesDownloadAction)
        ->name('invoices.download');
    Route::patch('invoices/{order}', $adminInvoicesUpdateStatusAction)
        ->name('invoices.update');
};

$dashboardRoutes = static function () use (
    $studentInvoiceRoutes,
    $adminInvoiceRoutes
): void {
    $studentGroup = Route::middleware(['role:student']);
    $studentGroup = $studentGroup->prefix('student');
    $studentGroup = $studentGroup->name('student.');
    $studentGroup->group($studentInvoiceRoutes);

    $adminGroup = Route::middleware(['role:admin']);
    $adminGroup = $adminGroup->prefix('admin');
    $adminGroup = $adminGroup->name('admin.');
    $adminGroup->group($adminInvoiceRoutes);
};

$webRoutes = static function () use ($dashboardRoutes): void {
    $dashboardGroup = Route::prefix('dashboard')->name('dashboard.');
    $dashboardGroup->group($dashboardRoutes);
};

Route::middleware(['auth', 'verified', 'backend.locale'])->group($webRoutes);
