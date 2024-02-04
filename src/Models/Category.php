<?php

namespace JobMetric\Category\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JobMetric\Translation\HasTranslation;

/**
 * JobMetric\Category\Models\Category
 *
 * @property int id
 * @property int slug
 * @property int parent_id
 * @property int type
 * @property int ordering
 * @property int status
 * @property int semaphore
 */
class Category extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = [
        'slug',
        'parent_id',
        'type',
        'ordering',
        'status',
        'semaphore'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parent_id' => 'integer',
        'type' => 'string',
        'ordering' => 'integer',
        'status' => 'boolean',
        'semaphore' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getTable()
    {
        return config('category.tables.category', parent::getTable());
    }

    /**
     * Scope a query to only include categories of a given slug.
     *
     * @param Builder $query
     * @param string $slug
     *
     * @return Builder
     */
    public function scopeOfSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
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
