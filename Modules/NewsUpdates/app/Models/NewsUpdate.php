<?php

namespace Modules\NewsUpdates\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsUpdate extends Model
{
    use SoftDeletes;

    protected $table = 'news_updates';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'image_path',
        'status',
        'published_at',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
