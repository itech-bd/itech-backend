<?php

namespace Modules\Batch\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Batch\Database\Factories\ClassScheduleFactory;

class ClassSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'batch_id',
        'class_date',
        'topic',
        'live_class_link',
        'recorded_video_link',
        'created_by',
    ];

    protected $casts = [
        'class_date' => 'date',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // protected static function newFactory(): ClassScheduleFactory
    // {
    //     // return ClassScheduleFactory::new();
    // }
}
