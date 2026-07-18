<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\Admin\WysiwygUploadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Modules\Batch\Models\Batch;
use Modules\Batch\Models\ClassSchedule;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;

Route::get(
    '/language/{lang}',
    function (string $lang) {
        if (in_array($lang, ['en', 'bn'], true)) {
            session(['locale' => $lang]);
        }

        return redirect()->back();
    }
)->name('language.switch');

Route::get('/media/{path}', [PublicMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('public.media');

Route::middleware('frontend.locale')->group(
    function () {
        Route::get('/', [SiteController::class, 'home'])->name('home');

        Route::get('/about', [SiteController::class, 'page'])
            ->defaults('slug', 'about')
            ->name('about');

        Route::get('/courses', [SiteController::class, 'page'])
            ->defaults('slug', 'courses')
            ->name('courses');

        Route::get('/courses/{courseId}', function (int $courseId) {
            $course = Course::query()->findOrFail($courseId);

            return redirect()->route('courses.show', $course, 301);
        })
            ->whereNumber('courseId')
            ->name('courses.show.legacy');

        Route::get('/courses/{course}', [SiteController::class, 'course'])
            ->name('courses.show');

        Route::middleware('auth')->group(
            function () {
                Route::get(
                    '/courses/{courseId}/checkout',
                    function (int $courseId) {
                        $course = Course::query()->findOrFail($courseId);

                        return redirect()->route('checkout.show', $course, 301);
                    }
                )
                    ->whereNumber('courseId')
                    ->name('checkout.show.legacy');

                Route::get(
                    '/courses/{course}/checkout',
                    [CheckoutController::class, 'show']
                )
                    ->name('checkout.show');

                Route::post(
                    '/courses/{course}/checkout',
                    [CheckoutController::class, 'store']
                )
                    ->name('checkout.store');

                Route::get(
                    '/checkout/orders/{order}',
                    [CheckoutController::class, 'success']
                )
                    ->whereNumber('order')
                    ->name('checkout.success');
            }
        );

        Route::get('/mentors', [SiteController::class, 'mentors'])->name('mentors');

        Route::get('/mentors/{mentor}', [SiteController::class, 'mentorShow'])
            ->name('mentors.show');

        Route::get('/reviews', [SiteController::class, 'page'])
            ->defaults('slug', 'reviews')
            ->name('reviews');

        Route::get('/solutions/software-solutions', [SiteController::class, 'page'])
            ->defaults('slug', 'software-solutions')
            ->name('solutions.software');

        Route::get('/solutions/it-solutions', [SiteController::class, 'page'])
            ->defaults('slug', 'it-solutions')
            ->name('solutions.it');

        Route::get('/solutions/web-hosting-solutions', [SiteController::class, 'page'])
            ->defaults('slug', 'web-hosting-solutions')
            ->name('solutions.hosting');

        Route::get('/news', [SiteController::class, 'news'])->name('news');
        Route::get('/news/data', [SiteController::class, 'newsData'])->name('news.data');
        Route::get('/news/{newsUpdate}', [SiteController::class, 'newsShow'])->name('news.show');

        Route::get('/privacy', [SiteController::class, 'page'])
            ->defaults('slug', 'privacy')
            ->name('privacy');

        Route::get('/terms', [SiteController::class, 'page'])
            ->defaults('slug', 'terms')
            ->name('terms');

        include __DIR__.'/auth.php';
    }
);

Route::get(
    '/dashboard',
    function () {
        $user = auth()->user();
        abort_unless($user, 403);

        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('admin');
        $isMentor = method_exists($user, 'hasRole') && $user->hasRole('mentor');
        $isStudent = method_exists($user, 'hasRole') && $user->hasRole('student');

        $stats = [];
        $quickLinks = [];
        $upcomingSchedules = collect();
        $recentOrders = collect();
        $recentBatches = collect();

        if ($isAdmin) {
            $stats = [
                'courses' => Course::query()->count(),
                'active_courses' => Course::query()->where('status', 'active')->count(),
                'batches' => Batch::query()->count(),
                'students' => DB::table('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'student')
                    ->count(),
                'pending_enrollments' => DB::table('batch_students')->where('status', 'pending')->count(),
                'paid_revenue' => (float) CourseOrder::query()->where('status', 'paid')->sum('amount'),
                'pending_invoices' => CourseOrder::query()->where('status', 'pending')->count(),
            ];

            $recentOrders = CourseOrder::query()
                ->with(['user:id,name,email', 'course:id,title', 'batch:id,name'])
                ->latest()
                ->limit(6)
                ->get();

            $recentBatches = Batch::query()
                ->with(['course:id,title'])
                ->withCount(['students', 'mentors', 'classSchedules'])
                ->latest()
                ->limit(5)
                ->get();

            $quickLinks = [
                ['label' => 'Create Course', 'href' => '/dashboard/courses/create', 'icon' => 'fa-solid fa-plus'],
                ['label' => 'Manage Batches', 'href' => '/dashboard/batches', 'icon' => 'fa-solid fa-calendar-days'],
                ['label' => 'Invoices', 'href' => '/dashboard/admin/invoices', 'icon' => 'fa-solid fa-receipt'],
                ['label' => 'Frontend Editor', 'href' => '/admin/frontend-editor', 'icon' => 'fa-solid fa-pen-ruler'],
            ];
        } elseif ($isStudent) {
            $studentBatchIds = DB::table('batch_students')
                ->where('student_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->pluck('batch_id');

            $approvedBatchIds = DB::table('batch_students')
                ->where('student_id', $user->id)
                ->where('status', 'approved')
                ->pluck('batch_id');

            $stats = [
                'courses' => Course::query()
                    ->whereHas('batches.students', fn ($query) => $query->where('users.id', $user->id))
                    ->count(),
                'batches' => $studentBatchIds->count(),
                'pending_batches' => DB::table('batch_students')
                    ->where('student_id', $user->id)
                    ->where('status', 'pending')
                    ->count(),
                'paid_invoices' => CourseOrder::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->count(),
                'paid_amount' => (float) CourseOrder::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->sum('amount'),
            ];

            $upcomingSchedules = ClassSchedule::query()
                ->with(['batch.course:id,title'])
                ->whereIn('batch_id', $approvedBatchIds)
                ->whereDate('class_date', '>=', now()->toDateString())
                ->orderBy('class_date')
                ->limit(6)
                ->get();

            $recentOrders = CourseOrder::query()
                ->where('user_id', $user->id)
                ->with(['course:id,title', 'batch:id,name'])
                ->latest()
                ->limit(4)
                ->get();

            $recentBatches = Batch::query()
                ->whereIn('id', $studentBatchIds)
                ->with(['course:id,title'])
                ->withCount(['classSchedules', 'mentors'])
                ->latest()
                ->limit(4)
                ->get();

            $quickLinks = [
                ['label' => 'Continue Courses', 'href' => '/dashboard/student/courses', 'icon' => 'fa-solid fa-graduation-cap'],
                ['label' => 'Class Schedule', 'href' => '/dashboard/student/batches', 'icon' => 'fa-solid fa-calendar-check'],
                ['label' => 'My Mentors', 'href' => '/dashboard/student/mentors', 'icon' => 'fa-solid fa-chalkboard-user'],
                ['label' => 'Invoices', 'href' => '/dashboard/student/invoices', 'icon' => 'fa-solid fa-file-invoice-dollar'],
            ];
        } elseif ($isMentor) {
            $mentorBatchIds = DB::table('batch_mentors')
                ->where('mentor_id', $user->id)
                ->pluck('batch_id');

            $stats = [
                'batches' => $mentorBatchIds->count(),
                'students' => DB::table('batch_students')
                    ->whereIn('batch_id', $mentorBatchIds)
                    ->where('status', 'approved')
                    ->distinct('student_id')
                    ->count('student_id'),
                'classes' => ClassSchedule::query()->whereIn('batch_id', $mentorBatchIds)->count(),
                'upcoming_classes' => ClassSchedule::query()
                    ->whereIn('batch_id', $mentorBatchIds)
                    ->whereDate('class_date', '>=', now()->toDateString())
                    ->count(),
            ];

            $upcomingSchedules = ClassSchedule::query()
                ->with(['batch.course:id,title'])
                ->whereIn('batch_id', $mentorBatchIds)
                ->whereDate('class_date', '>=', now()->toDateString())
                ->orderBy('class_date')
                ->limit(6)
                ->get();

            $recentBatches = Batch::query()
                ->whereIn('id', $mentorBatchIds)
                ->with(['course:id,title'])
                ->withCount(['students', 'classSchedules'])
                ->latest()
                ->limit(5)
                ->get();

            $quickLinks = [
                ['label' => 'My Batches', 'href' => '/dashboard/mentor/batches', 'icon' => 'fa-solid fa-users-rectangle'],
                ['label' => 'Profile', 'href' => '/profile', 'icon' => 'fa-solid fa-user-gear'],
            ];
        }

        return view('dashboard', compact(
            'isAdmin',
            'isMentor',
            'isStudent',
            'stats',
            'quickLinks',
            'upcomingSchedules',
            'recentOrders',
            'recentBatches'
        ));
    }
)->middleware(['auth', 'verified', 'backend.locale'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin', 'backend.locale'])
    ->prefix('admin')
    ->name('admin.')
    ->group(
        function () {
            Route::post(
                '/wysiwyg/upload',
                [WysiwygUploadController::class, 'upload']
            )->name('wysiwyg.upload');
        }
    );
