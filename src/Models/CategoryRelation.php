<?php

namespace JobMetric\Category\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * JobMetric\Category\Models\CategoryRelation
 *
 * @property int relatable_type
 * @property int relatable_id
 * @property int category_id
 * @property int collection
 */
class CategoryRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'categorizable_type',
        'categorizable_id',
        'collection'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'category_id' => 'integer',
        'categorizable_type' => 'string',
        'categorizable_id' => 'integer',
        'collection' => 'string'
    ];

    public function getTable()
    {
        return config('category.tables.category_relation', parent::getTable());
    }

    /**
     * Get the category that owns the relation.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the categorizable model that owns the category.
     *
     * @return MorphTo
     */
    public function categorizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include categories of a given type.
     *
     * @param Builder $query
     * @param string $collection
     * @return Builder
     */
    public function scopeByCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }
}
