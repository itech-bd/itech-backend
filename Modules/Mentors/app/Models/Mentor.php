<?php

namespace Modules\Mentors\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mentor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'name',
        'topic',
        'bio',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPublicRouteKeyAttribute(): string
    {
        $slug = trim((string) ($this->slug ?? ''));

        return $slug !== '' ? $slug : (string) $this->getKey();
    }
}
