<?php

use Illuminate\Support\Facades\Route;
use Modules\Mentors\Http\Controllers\MentorController;
use Modules\Mentors\Http\Controllers\MyMentorsController;

$dashboardMentorsRoutes = static function (): void {
    Route::resource('mentors', MentorController::class)->names('mentors');

    $studentGroup = Route::middleware(['role:student']);
    $studentGroup = $studentGroup->prefix('student');
    $studentGroup = $studentGroup->name('student.');

    $studentMentorsIndexAction = [MyMentorsController::class, 'index'];

    $studentMentorRoutes = static function () use (
        $studentMentorsIndexAction
    ): void {
        Route::get('mentors', $studentMentorsIndexAction)->name('mentors.index');
    };

    $studentGroup->group($studentMentorRoutes);
};

$webRoutes = static function () use ($dashboardMentorsRoutes): void {
    $dashboardGroup = Route::prefix('dashboard')->name('dashboard.');
    $dashboardGroup->group($dashboardMentorsRoutes);
};

Route::middleware(['auth', 'backend.locale'])->group($webRoutes);
