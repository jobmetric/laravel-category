<?php

namespace JobMetric\Category\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Category\Models\Category;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => null,
            'parent_id' => 0,
            'type' => null,
            'ordering' => 0,
            'status' => true,
            'semaphore' => null,
        ];
    }

    /**
     * set slug
     *
     * @param string $slug
     *
     * @return static
     */
    public function setSlug(string $slug): static
    {
        return $this->state(fn(array $attributes) => [
            'slug' => $slug
        ]);
    }

    /**
     * set parent_id
     *
     * @param int $parent_id
     *
     * @return static
     */
    public function setParent(int $parent_id): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => $parent_id
        ]);
    }

    /**
     * set type
     *
     * @param string $type
     *
     * @return static
     */
    public function setType(string $type): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => $type
        ]);
    }

    /**
     * set ordering
     *
     * @param int $ordering
     *
     * @return static
     */
    public function setOrdering(int $ordering): static
    {
        return $this->state(fn(array $attributes) => [
            'ordering' => $ordering
        ]);
    }

    /**
     * set status
     *
     * @param bool $status
     *
     * @return static
     */
    public function setStatus(bool $status): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => $status
        ]);
    }

    /**
     * set semaphore
     *
     * @param int $user_id
     * @param string $datetime
     *
     * @return static
     */
    public function setSemaphore(int $user_id, string $datetime): static
    {
        return $this->state(fn(array $attributes) => [
            'semaphore' => [
                'user_id' => $user_id,
                'datetime' => $datetime
            ]
        ]);
    }
}
