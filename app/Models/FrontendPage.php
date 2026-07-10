<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Frontend page model.
 *
 * @category Model
 * @package  App\Models
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class FrontendPage extends Model
{
    use HasFactory;

    /**
     * Get the route key for model binding.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
    ];

    /**
     * Get all sections for this page.
     *
     * @return HasMany
     */
    public function sections(): HasMany
    {
        return $this->hasMany(
            \App\Models\FrontendSection::class,
            'frontend_page_id'
        );
    }

    /**
     * Get active sections for this page.
     *
     * @return HasMany
     */
    public function activeSections(): HasMany
    {
        return $this->sections()->active();
    }
}
