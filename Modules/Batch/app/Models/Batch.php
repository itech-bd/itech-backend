<?php

namespace Modules\Batch\Models;

use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Course\Models\Course;
// use Modules\Batch\Database\Factories\BatchFactory;

class Batch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'name',
        'start_date',
        'end_date',
        'class_days',
        'class_time',
        'live_class_link',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'class_days' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'batch_mentors', 'batch_id', 'mentor_id')
            ->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'batch_students', 'batch_id', 'student_id')
            ->withPivot(['status', 'batch_type', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function autoGenerateClassSchedules(int $createdBy): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }

        $classDays = array_values(array_filter((array) ($this->class_days ?? [])));
        if (count($classDays) === 0) {
            return 0;
        }

        if ($this->classSchedules()->exists()) {
            return 0;
        }

        $now = now();
        $rows = [];
        $i = 1;

        foreach (CarbonPeriod::create($this->start_date, $this->end_date) as $date) {
            $weekday = $date->format('l');

            if (! in_array($weekday, $classDays, true)) {
                continue;
            }

            $rows[] = [
                'batch_id' => $this->id,
                'class_date' => $date->toDateString(),
                'topic' => 'Class ' . $i . ' - ' . $weekday,
                'live_class_link' => null,
                'recorded_video_link' => null,
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $i++;
        }

        if (count($rows) === 0) {
            return 0;
        }

        ClassSchedule::query()->insert($rows);

        return count($rows);
    }

    // protected static function newFactory(): BatchFactory
    // {
    //     // return BatchFactory::new();
    // }
}
