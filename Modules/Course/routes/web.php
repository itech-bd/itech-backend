<?php

use Illuminate\Support\Facades\Route;
use Modules\Course\Http\Controllers\CourseController;
use Modules\Course\Http\Controllers\MyCoursesController;

$adminCourseRoutes = static function (): void {
    Route::resource('courses', CourseController::class);
};

$studentCoursesIndexAction = [MyCoursesController::class, 'index'];
$studentCoursesShowAction = [MyCoursesController::class, 'show'];

$studentCourseRoutes = static function () use (
    $studentCoursesIndexAction,
    $studentCoursesShowAction
): void {
    Route::get('courses', $studentCoursesIndexAction)->name('courses.index');
    Route::get('courses/{course}', $studentCoursesShowAction)->name('courses.show');
};

$dashboardRoutes = static function () use (
    $adminCourseRoutes,
    $studentCourseRoutes
): void {
    $adminGroup = Route::middleware(['role:admin']);
    $adminGroup->group($adminCourseRoutes);

    $studentGroup = Route::middleware(['role:student']);
    $studentGroup = $studentGroup->prefix('student');
    $studentGroup = $studentGroup->name('student.');
    $studentGroup->group($studentCourseRoutes);
};

$webRoutes = static function () use ($dashboardRoutes): void {
    $dashboardGroup = Route::prefix('dashboard')->name('dashboard.');
    $dashboardGroup->group($dashboardRoutes);
};

Route::middleware(['auth', 'verified', 'backend.locale'])->group($webRoutes);
