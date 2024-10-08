<?php

namespace JobMetric\Category\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JobMetric\Category\Events\CategoryAllowMemberCollectionEvent;
use JobMetric\Category\Events\CategoryMediaAllowCollectionEvent;
use JobMetric\Comment\Contracts\CommentContract;
use JobMetric\Comment\HasComment;
use JobMetric\Layout\Contracts\LayoutContract;
use JobMetric\Layout\HasLayout;
use JobMetric\Like\HasLike;
use JobMetric\Media\Contracts\MediaContract;
use JobMetric\Media\HasFile;
use JobMetric\Media\MediaableWithType;
use JobMetric\Membership\Contracts\MemberContract;
use JobMetric\Membership\HasMember;
use JobMetric\Metadata\Contracts\MetaContract;
use JobMetric\Metadata\HasMeta;
use JobMetric\Metadata\MetaableWithType;
use JobMetric\PackageCore\Models\HasBooleanStatus;
use JobMetric\Star\HasStar;
use JobMetric\Translation\Contracts\TranslationContract;
use JobMetric\Translation\HasTranslation;
use JobMetric\Translation\TranslatableWithType;
use JobMetric\Url\HasUrl;

/**
 * JobMetric\Category\Models\Category
 *
 * @property int id
 * @property int type
 * @property int parent_id
 * @property int ordering
 * @property int status
 *
 * @method static find(int $int)
 */
class Category extends Model implements TranslationContract, MetaContract, MediaContract, CommentContract, MemberContract, LayoutContract
{
    use HasFactory,
        HasBooleanStatus,
        HasTranslation,
        TranslatableWithType,
        HasMeta,
        MetaableWithType,
        HasFile,
        MediaableWithType,
        HasComment,
        HasMember,
        HasLike,
        HasStar,
        HasLayout,
        HasUrl;

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

    /**
     * The "booted" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::retrieved(function (Category $category) {
            $category->init();
        });

        static::creating(function (Category $category) {
            $category->init();
        });
    }

    public function init(): void
    {
        $categoryTypes = getCategoryType();

        foreach ($categoryTypes as $type => $categoryType) {
            // Set the translation for the category type.
            $this->setTrans($type, $categoryType['translation']);

            // Set the metadata for the category type.
            $this->setMeta($type, $categoryType['metadata']);

            // Set the media collection for the category type.
            $this->setMediaCollection($type, $categoryType['media']);
        }
    }

    public function getTable()
    {
        return config('category.tables.category', parent::getTable());
    }

    /**
     * Check if a comment for a specific model needs to be approved.
     *
     * @return bool
     */
    public function needsCommentApproval(): bool
    {
        return true;
    }

    /**
     * allow the member collection.
     *
     * @return array
     */
    public function allowMemberCollection(): array
    {
        $event = new CategoryAllowMemberCollectionEvent([
            'owner' => 'single',
        ]);

        event($event);

        return $event->allowMemberCollection;
    }

    /**
     * Layout page type.
     *
     * @return string
     */
    public function layoutPageType(): string
    {
        return 'category';
    }

    /**
     * Layout collection field.
     *
     * @return string|null
     */
    public function layoutCollectionField(): ?string
    {
        return null;
    }

    /**
     * Get the category relations.
     *
     * @return HasMany
     */
    public function categoryRelations(): HasMany
    {
        return $this->hasMany(CategoryRelation::class, 'category_id', 'id');
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

    /**
     * Get the children category.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
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
