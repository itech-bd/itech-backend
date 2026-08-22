<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\CourseOrder;
use Modules\Profile\Models\Address;
use Modules\Profile\Models\Education;
use Modules\Profile\Models\Experience;
use Modules\Profile\Models\UserProfile;

class Student extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'legacy_user_id',
        'name',
        'email',
        'email_verified_at',
        'profile_image',
        'password',
        'must_change_password',
        'remember_token',
        'gender',
        'date_of_birth',
        'mobile_number',
        'father_name',
        'father_mobile',
        'mother_name',
        'mother_mobile',
        'bio',
        'public_url',
        'house_number',
        'street',
        'city',
        'post_office',
        'zip_code',
        'country',
        'skills',
        'educations',
        'experiences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'skills' => 'array',
            'educations' => 'array',
            'experiences' => 'array',
        ];
    }

    public function legacyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'legacy_user_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'legacy_user_id');
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'user_id', 'legacy_user_id');
    }

    public function legacyEducations(): HasMany
    {
        return $this->hasMany(Education::class, 'user_id', 'legacy_user_id');
    }

    public function legacyExperiences(): HasMany
    {
        return $this->hasMany(Experience::class, 'user_id', 'legacy_user_id');
    }

    public function batches(): BelongsToMany
    {
        return $this->belongsToMany(Batch::class, 'batch_students', 'student_id', 'batch_id')
            ->withPivot(['status', 'batch_type', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }

    public function studentBatches(): BelongsToMany
    {
        return $this->batches();
    }

    public function courseOrders(): HasMany
    {
        return $this->hasMany(CourseOrder::class, 'student_id');
    }

    public function getRoleNames(): Collection
    {
        return collect(['student']);
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        return in_array('student', Arr::wrap($roles), true);
    }

    public function hasAnyRole(...$roles): bool
    {
        return collect($roles)->flatten()->contains('student');
    }

    public function hasAnyPermission(...$permissions): bool
    {
        return false;
    }

    public function getAllPermissions(): Collection
    {
        return collect();
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        $path = $this->profile_image;
        if (! is_string($path) || $path === '') {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        return Route::has('public.media')
            ? route('public.media', ['path' => $normalized])
            : Storage::disk('public')->url($normalized);
    }

    public function getInitialsAttribute(): string
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) >= 2) {
            return Str::upper(Str::substr($parts[0], 0, 1).Str::substr($parts[count($parts) - 1], 0, 1));
        }

        return Str::upper(Str::substr($name, 0, 2));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function preferredLocale(): string
    {
        return (string) app()->getLocale();
    }
}
