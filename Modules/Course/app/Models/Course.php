<?php

namespace Modules\Course\Models;

use App\Models\User;
use App\Support\TextEncoding;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Batch\Models\Batch;
// use Modules\Course\Database\Factories\CourseFactory;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'old_price',
        'discount_price',
        'online_old_price',
        'online_discount_price',
        'offline_old_price',
        'offline_discount_price',
        'thumbnail',
        'status',
        'created_by',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'online_old_price' => 'decimal:2',
        'online_discount_price' => 'decimal:2',
        'offline_old_price' => 'decimal:2',
        'offline_discount_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Course $course): void {
            $course->title = TextEncoding::repairMojibake($course->title) ?? $course->title;
            $course->description = TextEncoding::repairMojibake($course->description) ?? $course->description;
            $course->slug = TextEncoding::repairMojibake($course->slug) ?? $course->slug;
            $course->thumbnail = TextEncoding::repairMojibake($course->thumbnail) ?? $course->thumbnail;

            $currentSlug = is_string($course->slug) ? trim($course->slug) : '';

            if ($currentSlug === '') {
                $course->slug = static::makeUniqueSlug((string) $course->title, $course->getKey());

                return;
            }

            if (! $course->exists || $course->isDirty('slug')) {
                $course->slug = static::makeUniqueSlug($currentSlug, $course->getKey());
            }
        });
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'course_id');
    }

    public static function normalizeSlug(?string $value): string
    {
        $normalized = str_replace('&', ' and ', trim((string) $value));
        $slug = Str::slug($normalized);

        return $slug !== '' ? $slug : 'course';
    }

    public static function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = static::normalizeSlug($value);
        $slug = $baseSlug;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getRouteKey(): mixed
    {
        return $this->slug ?: $this->getKey();
    }

    public function resolveRouteBinding($value, $field = null): ?EloquentModel
    {
        $field = $field ?? $this->getRouteKeyName();

        $course = $this->newQuery()->where($field, $value)->first();
        if ($course || $field !== $this->getRouteKeyName() || ! is_numeric($value)) {
            return $course;
        }

        return $this->newQuery()->whereKey($value)->first();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $thumb = $this->thumbnail;
        if (!is_string($thumb)) {
            return null;
        }

        $thumb = trim($thumb);
        if ($thumb === '') {
            return null;
        }

        if (Str::startsWith($thumb, ['http://', 'https://'])) {
            return $thumb;
        }

        $normalized = ltrim($thumb, '/');
        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = Str::after($normalized, 'storage/');
        }

        return Storage::disk('public')->url($normalized);
    }

    // protected static function newFactory(): CourseFactory
    // {
    //     // return CourseFactory::new();
    // }
}
