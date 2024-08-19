<?php

namespace JobMetric\Category\Tests;

use App\Models\Product;
use Throwable;

class HasCategoryTest extends BaseCategory
{
    /**
     * @throws Throwable
     */
    public function test_tags_trait_relationship()
    {
        $product = new Product();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $product->tags());
    }

    /**
     * @throws Throwable
     */
    public function test_attach(): void
    {
        $product = $this->create_product();

        $tag = $this->create_tag_for_has();

        $attach = $product->attachTag($tag->id, 'product_tag');

        $this->assertIsArray($attach);

        $this->assertDatabaseHas(config('tag.tables.tag_relation'), [
            'tag_id' => $tag->id,
            'taggable_id' => $product->id,
            'taggable_type' => Product::class,
            'collection' => 'product_tag'
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_detach(): void
    {
        $product = $this->create_product();

        $tag = $this->create_tag_for_has();

        $product->attachTag($tag->id, 'product_tag');

        $detach = $product->detachTag($tag->id);

        $this->assertIsArray($detach);

        $this->assertDatabaseMissing(config('tag.tables.tag_relation'), [
            'tag_id' => $tag->id,
            'taggable_id' => $product->id,
            'taggable_type' => Product::class,
            'collection' => 'product_tag'
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_get_tag_by_collection(): void
    {
        $product = new Product();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $product->getTagByCollection('product_tag'));
    }
}
