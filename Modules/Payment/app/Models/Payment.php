<?php

namespace Modules\Payment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Course\Models\CourseOrder;

class Payment extends Model
{
    protected $guarded = ['*'];

    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'reversed_at' => 'datetime'];

    public function courseOrder(): BelongsTo
    {
        return $this->belongsTo(CourseOrder::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->whereHas('courseOrder', fn (Builder $orders) => $orders->where('student_id', $studentId));
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('transaction_reference', 'like', "%{$search}%")
                    ->orWhereHas('courseOrder.student', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                if (preg_match('/^(?:#?(INV|PAY)-)?([0-9]+)$/i', $search, $match)) {
                    $query->orWhere(strtoupper($match[1]) === 'PAY' ? 'id' : 'course_order_id', $match[2]);
                }
            });
        }
        foreach (['status', 'payment_method'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (! empty($filters['from'])) {
            $query->where('paid_at', '>=', $filters['from'].' 00:00:00');
        }
        if (! empty($filters['to'])) {
            $query->where('paid_at', '<', \Carbon\Carbon::parse($filters['to'])->addDay()->startOfDay());
        }

        return $query;
    }
}
