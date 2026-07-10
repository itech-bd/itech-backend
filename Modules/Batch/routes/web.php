<?php

use Illuminate\Support\Facades\Route;
use Modules\Batch\Http\Controllers\AdminBatchesController;
use Modules\Batch\Http\Controllers\AdminBatchDetailsController;
use Modules\Batch\Http\Controllers\BatchStudentApprovalController;
use Modules\Batch\Http\Controllers\BatchMentorAssignmentController;
use Modules\Batch\Http\Controllers\BatchStudentAssignmentController;
use Modules\Batch\Http\Controllers\BatchLiveClassLinkController;
use Modules\Batch\Http\Controllers\ClassScheduleController;
use Modules\Batch\Http\Controllers\CourseBatchController;
use Modules\Batch\Http\Controllers\MyMentorBatchesController;
use Modules\Batch\Http\Controllers\MyStudentBatchesController;
use Modules\Course\Models\Course;

$adminAssignmentRoutes = static function (): void {
    $mentorEditAction = [
        BatchMentorAssignmentController::class,
        'edit',
    ];
    $mentorUpdateAction = [
        BatchMentorAssignmentController::class,
        'update',
    ];
    $studentEditAction = [
        BatchStudentAssignmentController::class,
        'edit',
    ];
    $studentUpdateAction = [
        BatchStudentAssignmentController::class,
        'update',
    ];

    $scopedRoutes = static function () use (
        $mentorEditAction,
        $mentorUpdateAction,
        $studentEditAction,
        $studentUpdateAction
    ): void {
        Route::get('mentors', $mentorEditAction)->name('mentors.edit');
        Route::put('mentors', $mentorUpdateAction)->name('mentors.update');

        Route::post(
            'mentors/add',
            [BatchMentorAssignmentController::class, 'add']
        )->name('mentors.add');
        Route::delete(
            'mentors/{mentor}',
            [BatchMentorAssignmentController::class, 'remove']
        )
            ->whereNumber('mentor')
            ->name('mentors.remove');

        Route::get('students', $studentEditAction)->name('students.edit');
        Route::put('students', $studentUpdateAction)->name('students.update');

        Route::post(
            'students/add',
            [BatchStudentAssignmentController::class, 'add']
        )->name('students.add');
        Route::delete(
            'students/{student}',
            [BatchStudentAssignmentController::class, 'remove']
        )
            ->whereNumber('student')
            ->name('students.remove');

        Route::put(
            'students/{student}/approve',
            [BatchStudentApprovalController::class, 'approve']
        )
            ->whereNumber('student')
            ->name('students.approve');
        Route::delete(
            'students/{student}/reject',
            [BatchStudentApprovalController::class, 'reject']
        )
            ->whereNumber('student')
            ->name('students.reject');
        Route::patch(
            'students/{student}/batch-type',
            [BatchStudentAssignmentController::class, 'updateBatchType']
        )
            ->whereNumber('student')
            ->name('students.updateBatchType');
    };

    Route::prefix('batches/{batch}')->name('batches.')->group($scopedRoutes);
};

$adminBatchRoutes = static function () use ($adminAssignmentRoutes): void {
    $adminBatchesIndexAction = [AdminBatchesController::class, 'index'];
    $adminBatchesCreateAction = [AdminBatchesController::class, 'create'];
    $adminBatchesCreateRedirectAction = [
        AdminBatchesController::class,
        'redirectToCourseCreate',
    ];

    Route::get('batches', $adminBatchesIndexAction)->name('batches.index');
    Route::get('batches/create', $adminBatchesCreateAction)->name('batches.create');
    Route::post('batches/create', $adminBatchesCreateRedirectAction)
        ->name('batches.create.redirect');

    Route::get('batches/{batch}', [AdminBatchDetailsController::class, 'show'])
        ->whereNumber('batch')
        ->name('batches.show');
    Route::get('batches/{batch}/edit', [AdminBatchDetailsController::class, 'edit'])
        ->whereNumber('batch')
        ->name('batches.edit');
    Route::put('batches/{batch}', [AdminBatchDetailsController::class, 'update'])
        ->whereNumber('batch')
        ->name('batches.update');

    Route::delete('batches/{batch}', [AdminBatchDetailsController::class, 'destroy'])
        ->whereNumber('batch')
        ->name('batches.destroy');

    // Canonical per-course create/store under the Batches section
    Route::get('batches/create/{course}', [CourseBatchController::class, 'create'])
        ->name('batches.create.course');
    Route::post('batches/{course}', [CourseBatchController::class, 'store'])
        ->name('batches.store.course');

    // Backwards-compatible redirect to keep the old URL working.
    Route::get(
        'courses/{course}/batches/create',
        function (Course $course) {
            return redirect()->route('dashboard.batches.create.course', $course);
        }
    )->name('courses.batches.create');

    Route::resource('courses.batches', CourseBatchController::class)
        ->except(['create']);
    $adminAssignmentRoutes();
};

$mentorBatchRoutes = static function (): void {
    $mentorIndexAction = [MyMentorBatchesController::class, 'index'];
    $mentorShowAction = [MyMentorBatchesController::class, 'show'];

    Route::get('batches', $mentorIndexAction)->name('batches.index');
    Route::get('batches/{batch}', $mentorShowAction)->name('batches.show');
};

$studentBatchRoutes = static function (): void {
    $studentIndexAction = [MyStudentBatchesController::class, 'index'];
    $studentShowAction = [MyStudentBatchesController::class, 'show'];

    Route::get('batches', $studentIndexAction)->name('batches.index');
    Route::get('batches/{batch}', $studentShowAction)->name('batches.show');
};

$dashboardRoutes = static function () use (
    $adminBatchRoutes,
    $mentorBatchRoutes,
    $studentBatchRoutes
): void {
    $adminGroup = Route::middleware(['role:admin']);
    $adminGroup->group($adminBatchRoutes);

    Route::resource('batches.schedules', ClassScheduleController::class)
        ->parameters(['schedules' => 'classSchedule'])
        ->except(['destroy']);

    Route::get('batches/{batch}/live-link', [BatchLiveClassLinkController::class, 'edit'])
        ->name('batches.live_link.edit');
    Route::put('batches/{batch}/live-link', [BatchLiveClassLinkController::class, 'update'])
        ->name('batches.live_link.update');

    $mentorGroup = Route::middleware(['role:mentor']);
    $mentorGroup = $mentorGroup->prefix('mentor');
    $mentorGroup = $mentorGroup->name('mentor.');
    $mentorGroup->group($mentorBatchRoutes);

    $studentGroup = Route::middleware(['role:student']);
    $studentGroup = $studentGroup->prefix('student');
    $studentGroup = $studentGroup->name('student.');
    $studentGroup->group($studentBatchRoutes);
};

$webRoutes = static function () use ($dashboardRoutes): void {
    $dashboardGroup = Route::prefix('dashboard')->name('dashboard.');
    $dashboardGroup->group($dashboardRoutes);
};

Route::middleware(['auth', 'verified', 'backend.locale'])->group($webRoutes);
