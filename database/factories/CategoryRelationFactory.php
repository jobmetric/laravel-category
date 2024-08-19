<?php

namespace JobMetric\Category\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Category\Models\CategoryRelation;

/**
 * @extends Factory<CategoryRelation>
 */
class CategoryRelationFactory extends Factory
{
    protected $model = CategoryRelation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => null,
            'categorizable_type' => null,
            'categorizable_id' => null,
            'collection' => null,
        ];
    }

    /**
     * set category id
     *
     * @param int $category_id
     *
     * @return static
     */
    public function setCategoryId(int $category_id): static
    {
        return $this->state(fn(array $attributes) => [
            'category_id' => $category_id,
        ]);
    }

    /**
     * set categorizable
     *
     * @param string $categorizable_type
     * @param int $categorizable_id
     *
     * @return static
     */
    public function setCategorizable(string $categorizable_type, int $categorizable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'categorizable_type' => $categorizable_type,
            'categorizable_id' => $categorizable_id,
        ]);
    }

    /**
     * set collection
     *
     * @param string|null $collection
     *
     * @return static
     */
    public function setCollection(?string $collection): static
    {
        return $this->state(fn(array $attributes) => [
            'collection' => $collection,
        ]);
    }
}
