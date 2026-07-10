<?php

use Illuminate\Support\Facades\Route;
use Modules\Reviews\Http\Controllers\ReviewController;

$adminReviewRoutes = static function (): void {
    Route::resource('reviews', ReviewController::class);
};

$dashboardRoutes = static function () use ($adminReviewRoutes): void {
    $adminReviewRoutes();
};

$webRoutes = static function () use ($dashboardRoutes): void {
    $dashboardGroup = Route::prefix('dashboard')->name('dashboard.');
    $dashboardGroup->group($dashboardRoutes);
};

Route::middleware(['auth', 'verified', 'backend.locale'])->group($webRoutes);
