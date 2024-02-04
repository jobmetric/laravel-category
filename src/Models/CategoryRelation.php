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
        'relatable_type',
        'relatable_id',
        'category_id',
        'collection'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'relatable_type' => 'string',
        'relatable_id' => 'integer',
        'category_id' => 'integer',
        'collection' => 'string'
    ];

    public function getTable()
    {
        return config('category.tables.category_relation', parent::getTable());
    }

    /**
     * Get the relatable model that owns the category.
     *
     * @return MorphTo
     */
    public function relatable(): MorphTo
    {
        return $this->morphTo();
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
