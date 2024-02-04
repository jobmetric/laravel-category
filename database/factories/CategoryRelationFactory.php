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
            'relatable_type' => null,
            'relatable_id' => null,
            'category_id' => null,
            'collection' => null,
        ];
    }

    /**
     * set relatable type
     *
     * @param string $relatable_type
     *
     * @return static
     */
    public function setRelatableType(string $relatable_type): static
    {
        return $this->state(fn(array $attributes) => [
            'relatable_type' => $relatable_type,
        ]);
    }

    /**
     * set relatable id
     *
     * @param int $relatable_id
     *
     * @return static
     */
    public function setRelatableId(int $relatable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'relatable_id' => $relatable_id,
        ]);
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
