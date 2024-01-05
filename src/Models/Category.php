<?php

namespace JobMetric\Category\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * JobMetric\Category\Models\Category
 *
 * @property int id
 * @property int type
 * @property int ordering
 * @property int status
 * @property int semaphore
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
