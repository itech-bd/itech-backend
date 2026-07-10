<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\VerifyEmailNotification;
use Modules\Profile\Models\Address;
use Modules\Profile\Models\Education;
use Modules\Profile\Models\Experience;
use Modules\Profile\Models\Skill;
use Modules\Profile\Models\UserProfile;
use Modules\Batch\Models\Batch;
use Spatie\Permission\Traits\HasRoles;

/**
 * User model.
 */
class User extends Authenticatable
    implements MustVerifyEmail
{
    /**
     * @use HasFactory<\Database\Factories\UserFactory>
     */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'user_id');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'user_id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class, 'user_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->withPivot(['proficiency_level']);
    }

    public function mentorBatches(): BelongsToMany
    {
        return $this->belongsToMany(Batch::class, 'batch_mentors', 'mentor_id', 'batch_id')
            ->withTimestamps();
    }

    public function studentBatches(): BelongsToMany
    {
        return $this->belongsToMany(Batch::class, 'batch_students', 'student_id', 'batch_id')
            ->withPivot(['status', 'batch_type', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }

    /**
     * Get the full URL for the user's profile image.
     *
     * @return string|null
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        $path = $this->profile_image;
        if (!is_string($path) || $path === '') {
            return null;
        }

        /**
         * @var \Illuminate\Filesystem\FilesystemAdapter $disk
         */
        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            return null;
        }

        return $disk->url($path);
    }

    /**
     * Get two-letter initials for the user (fallback avatar).
     */
    public function getInitialsAttribute(): string
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return '';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) >= 2) {
            $first = Str::substr($parts[0], 0, 1);
            $last = Str::substr($parts[count($parts) - 1], 0, 1);
            return Str::upper($first . $last);
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
