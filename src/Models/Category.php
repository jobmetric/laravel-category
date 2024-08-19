<?php

namespace JobMetric\Category\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JobMetric\Translation\Contracts\TranslationContract;
use JobMetric\Translation\HasTranslation;
use JobMetric\Url\HasUrl;

/**
 * JobMetric\Category\Models\Category
 *
 * @property int id
 * @property int type
 * @property int parent_id
 * @property int ordering
 * @property int status
 * @property int semaphore
 */
class Category extends Model implements TranslationContract
{
    use HasFactory, HasTranslation, HasUrl;

    protected $fillable = [
        'parent_id',
        'type',
        'ordering',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'string',
        'parent_id' => 'integer',
        'ordering' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getTable()
    {
        return config('category.tables.category', parent::getTable());
    }

    public function translationAllowFields(): array
    {
        return [
            'title',
            'description',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ];
    }

    /**
     * Scope a query to only include categories of a given type.
     *
     * @param Builder $query
     * @param string $type
     *
     * @return Builder
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Get the parent category.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }

    /**
     * Get the parent category.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the paths of the category.
     *
     * @return HasMany
     */
    public function paths(): HasMany
    {
        return $this->hasMany(CategoryPath::class, 'category_id');
    }
}
