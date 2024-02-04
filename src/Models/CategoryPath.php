<?php

namespace JobMetric\Category\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * JobMetric\Category\Models\CategoryPath
 *
 * @property int id
 * @property int type
 * @property int category_id
 * @property int path_id
 * @property int level
 */
class CategoryPath extends Model
{
    use HasFactory;

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
}
