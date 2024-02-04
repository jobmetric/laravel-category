<?php

namespace JobMetric\Category\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Category\Models\CategoryPath;

/**
 * @extends Factory<CategoryPath>
 */
class CategoryPathFactory extends Factory
{
    protected $model = CategoryPath::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => null,
            'category_id' => 0,
            'path_id' => 0,
            'level' => 0,
        ];
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
     * set category_id
     *
     * @param int $category_id
     *
     * @return static
     */
    public function setCategoryId(int $category_id): static
    {
        return $this->state(fn(array $attributes) => [
            'category_id' => $category_id
        ]);
    }

    /**
     * set path_id
     *
     * @param int $path_id
     *
     * @return static
     */
    public function setPathId(int $path_id): static
    {
        return $this->state(fn(array $attributes) => [
            'path_id' => $path_id
        ]);
    }

    /**
     * set level
     *
     * @param int $level
     *
     * @return static
     */
    public function setLevel(int $level): static
    {
        return $this->state(fn(array $attributes) => [
            'level' => $level
        ]);
    }
}
