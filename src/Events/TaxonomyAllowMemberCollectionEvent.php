<?php

namespace JobMetric\Taxonomy\Events;

class TaxonomyAllowMemberCollectionEvent
{
    /**
     * The tay allow the member collection to be filled by the listener.
     *
     * @var array
     */
    public array $allowMemberCollection = [];

    /**
     * Create a new event instance.
     *
     * @param array $defaultTaxonomyAllowMemberCollection
     */
    public function __construct(array $defaultTaxonomyAllowMemberCollection = [])
    {
        $this->allowMemberCollection = $defaultTaxonomyAllowMemberCollection;
    }

    /**
     * Add an allowed member collection.
     *
     * @param array $allowMemberCollection Example: ['members' => 'multiple'] or ['owner' => 'single']
     *
     * @return static
     */
    public function AddAllowMemberCollection(array $allowMemberCollection): static
    {
        if (!in_array($allowMemberCollection, $this->allowMemberCollection)) {
            $this->allowMemberCollection = array_merge($this->allowMemberCollection, $allowMemberCollection);
        }

        return $this;
    }
}
