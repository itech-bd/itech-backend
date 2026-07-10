<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Frontend section model.
 *
 * @category Model
 * @package  App\Models
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class FrontendSection extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'frontend_page_id',
        'section_key',
        'title_en',
        'title_bn',
        'content_en',
        'content_bn',
        'image_path',
        'icon',
        'button_text_en',
        'button_text_bn',
        'button_link',
        'status',
    ];

    /**
     * Computed, localized convenience attributes.
     *
     * @var list<string>
     */
    protected $appends = [
        'title',
        'content',
        'button_text',
    ];

    /**
     * Get the page this section belongs to.
     *
     * @return BelongsTo
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(FrontendPage::class, 'frontend_page_id');
    }

    /**
     * Scope a query to only include active sections.
     *
     * @param Builder $query The query builder.
     *
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Localize a base field (e.g. title/content/button_text) using locale.
     *
     * @param string      $baseField      Base field name without locale suffix.
     * @param string|null $locale         Locale override (en/bn).
     * @param string|null $fallbackLocale Fallback locale (en/bn).
     *
     * @return string|null
     */
    public function getLocalized(
        string $baseField,
        ?string $locale = null,
        ?string $fallbackLocale = 'bn'
    ): ?string {
        $locale = $locale ?: (string) app()->getLocale();
        $locale = strtolower(str_replace('_', '-', $locale));

        $primary = str_starts_with($locale, 'en') ? 'en' : 'bn';
        $fallback = $fallbackLocale === 'en' ? 'en' : 'bn';

        $primaryColumn = $baseField . '_' . $primary;
        $fallbackColumn = $baseField . '_' . $fallback;

        $primaryValue = $this->getAttributeValue($primaryColumn);
        if (is_string($primaryValue) && trim($primaryValue) !== '') {
            return $primaryValue;
        }

        $fallbackValue = $this->getAttributeValue($fallbackColumn);
        if (is_string($fallbackValue) && trim($fallbackValue) !== '') {
            return $fallbackValue;
        }

        if (is_string($primaryValue)) {
            return $primaryValue;
        }

        return is_string($fallbackValue) ? $fallbackValue : null;
    }

    /**
     * Computed: localized title (title_en/title_bn).
     *
     * @return string|null
     */
    public function getTitleAttribute(): ?string
    {
        return $this->getLocalized('title');
    }

    /**
     * Computed: localized content (content_en/content_bn).
     *
     * @return string|null
     */
    public function getContentAttribute(): ?string
    {
        return $this->getLocalized('content');
    }

    /**
     * Computed: localized button text (button_text_en/button_text_bn).
     *
     * @return string|null
     */
    public function getButtonTextAttribute(): ?string
    {
        return $this->getLocalized('button_text');
    }
}
