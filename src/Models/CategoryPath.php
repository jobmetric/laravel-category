<?php

namespace JobMetric\Category\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * JobMetric\Category\Models\CategoryPath
 *
 * @property int type
 * @property int category_id
 * @property int path_id
 * @property int level
 */
class CategoryPath extends Pivot
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'category_id',
        'path_id',
        'level'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'string',
        'category_id' => 'integer',
        'path_id' => 'integer',
        'level' => 'integer'
    ];

    public function getTable()
    {
        return config('category.tables.category_path', parent::getTable());
    }

    /**
     * Get the category that owns the path.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the path that owns the category.
     *
     * @return BelongsTo
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'path_id');
    }
}
