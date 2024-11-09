<?php

namespace JobMetric\Taxonomy\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Taxonomy\Models\Taxonomy;

/**
 * @extends Factory<Taxonomy>
 */
class TaxonomyFactory extends Factory
{
    protected $model = Taxonomy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => null,
            'parent_id' => 0,
            'ordering' => 0,
            'status' => true
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
}
