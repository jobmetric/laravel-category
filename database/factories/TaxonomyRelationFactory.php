<?php

namespace JobMetric\Taxonomy\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Taxonomy\Models\TaxonomyRelation;

/**
 * @extends Factory<TaxonomyRelation>
 */
class TaxonomyRelationFactory extends Factory
{
    protected $model = TaxonomyRelation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'taxonomy_id' => null,
            'taxonomizable_type' => null,
            'taxonomizable_id' => null,
            'collection' => null,
        ];
    }

    /**
     * set taxonomy id
     *
     * @param int $taxonomy_id
     *
     * @return static
     */
    public function setTaxonomyId(int $taxonomy_id): static
    {
        return $this->state(fn(array $attributes) => [
            'taxonomy_id' => $taxonomy_id,
        ]);
    }

    /**
     * set taxonomizable
     *
     * @param string $taxonomizable_type
     * @param int $taxonomizable_id
     *
     * @return static
     */
    public function setTaxonomizable(string $taxonomizable_type, int $taxonomizable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'taxonomizable_type' => $taxonomizable_type,
            'taxonomizable_id' => $taxonomizable_id,
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
