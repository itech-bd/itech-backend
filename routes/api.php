<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\PublicSiteController;
use App\Http\Controllers\Api\V1\Student\StudentBatchController;
use App\Http\Controllers\Api\V1\Student\StudentCourseController;
use App\Http\Controllers\Api\V1\Student\StudentDashboardController;
use App\Http\Controllers\Api\V1\Student\StudentInvoiceController;
use App\Http\Controllers\Api\V1\Student\StudentMentorController;
use App\Http\Controllers\Api\V1\Student\StudentProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('api.locale')->group(function (): void {
    Route::prefix('public')->name('api.v1.public.')->group(function (): void {
        Route::get('bootstrap', [PublicSiteController::class, 'bootstrap'])->name('bootstrap');
        Route::get('home', [PublicSiteController::class, 'home'])->name('home');
        Route::get('pages/{slug}', [PublicSiteController::class, 'page'])
            ->where('slug', '[a-z0-9-]+')
            ->name('pages.show');

        Route::get('courses', [PublicSiteController::class, 'courses'])->name('courses.index');
        Route::get('courses/{course}', [PublicSiteController::class, 'course'])->name('courses.show');

        Route::get('mentors', [PublicSiteController::class, 'mentors'])->name('mentors.index');
        Route::get('mentors/{mentor}', [PublicSiteController::class, 'mentor'])->name('mentors.show');
        Route::get('profiles/{publicUrl}', [PublicSiteController::class, 'publicProfile'])
            ->where('publicUrl', '[a-z0-9]+(?:-[a-z0-9]+)*')
            ->name('profiles.show');

        Route::get('reviews', [PublicSiteController::class, 'reviews'])->name('reviews.index');
        Route::get('news', [PublicSiteController::class, 'news'])->name('news.index');
        Route::get('news/{newsUpdate}', [PublicSiteController::class, 'newsItem'])->name('news.show');

        Route::post('contact', [PublicSiteController::class, 'contact'])
            ->middleware('throttle:10,1')
            ->name('contact.store');
    });

    Route::prefix('auth')->name('api.v1.auth.')->group(function (): void {
        Route::post('register', [AuthController::class, 'register'])
            ->middleware('throttle:10,1')
            ->name('register');
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login');
        Route::post('resend-verification', [AuthController::class, 'resendVerification'])
            ->middleware('throttle:6,1')
            ->name('verification.resend');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
            ->middleware('throttle:6,1')
            ->name('password.forgot');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])
            ->middleware('throttle:6,1')
            ->name('password.reset');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
        });
    });

    Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
        Route::get('checkout/courses/{course}', [CheckoutController::class, 'preview'])
            ->name('api.v1.checkout.preview');
        Route::post('checkout/courses/{course}', [CheckoutController::class, 'store'])
            ->name('api.v1.checkout.store');
        Route::get('checkout/orders/{order}', [CheckoutController::class, 'order'])
            ->name('api.v1.checkout.orders.show');

        Route::prefix('student')
            ->middleware('role:student')
            ->name('api.v1.student.')
            ->group(function (): void {
                Route::get('dashboard', StudentDashboardController::class)->name('dashboard');

                Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');
                Route::get('courses/{course}', [StudentCourseController::class, 'show'])->name('courses.show');

                Route::get('batches', [StudentBatchController::class, 'index'])->name('batches.index');
                Route::get('batches/{batch}', [StudentBatchController::class, 'show'])->name('batches.show');

                Route::get('mentors', [StudentMentorController::class, 'index'])->name('mentors.index');

                Route::get('invoices', [StudentInvoiceController::class, 'index'])->name('invoices.index');
                Route::get('invoices/{order}', [StudentInvoiceController::class, 'show'])->name('invoices.show');
                Route::get('invoices/{order}/download', [StudentInvoiceController::class, 'download'])->name('invoices.download');

                Route::get('profile', [StudentProfileController::class, 'show'])->name('profile.show');
                Route::post('profile', [StudentProfileController::class, 'update'])->name('profile.update');
                Route::patch('profile/details', [StudentProfileController::class, 'updateDetails'])->name('profile.details.update');
                Route::patch('profile/public-url', [StudentProfileController::class, 'updatePublicUrl'])->name('profile.public-url.update');
                Route::put('profile/address', [StudentProfileController::class, 'updateAddress'])->name('profile.address.update');
                Route::put('profile/password', [StudentProfileController::class, 'updatePassword'])->name('profile.password.update');

                Route::post('profile/educations', [StudentProfileController::class, 'storeEducation'])->name('profile.educations.store');
                Route::patch('profile/educations/{education}', [StudentProfileController::class, 'updateEducation'])->name('profile.educations.update');
                Route::delete('profile/educations/{education}', [StudentProfileController::class, 'destroyEducation'])->name('profile.educations.destroy');

                Route::post('profile/experiences', [StudentProfileController::class, 'storeExperience'])->name('profile.experiences.store');
                Route::patch('profile/experiences/{experience}', [StudentProfileController::class, 'updateExperience'])->name('profile.experiences.update');
                Route::delete('profile/experiences/{experience}', [StudentProfileController::class, 'destroyExperience'])->name('profile.experiences.destroy');

                Route::post('profile/skills', [StudentProfileController::class, 'storeSkill'])->name('profile.skills.store');
                Route::patch('profile/skills/{skill}', [StudentProfileController::class, 'updateSkill'])->name('profile.skills.update');
                Route::delete('profile/skills/{skill}', [StudentProfileController::class, 'destroySkill'])->name('profile.skills.destroy');
            });
    });
});
