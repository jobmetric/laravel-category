<?php

namespace JobMetric\Taxonomy\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Taxonomy\Models\TaxonomyPath;

/**
 * @extends Factory<TaxonomyPath>
 */
class TaxonomyPathFactory extends Factory
{
    protected $model = TaxonomyPath::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => null,
            'taxonomy_id' => 0,
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
     * set taxonomy_id
     *
     * @param int $taxonomy_id
     *
     * @return static
     */
    public function setTaxonomyId(int $taxonomy_id): static
    {
        return $this->state(fn(array $attributes) => [
            'taxonomy_id' => $taxonomy_id
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
