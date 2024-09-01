<?php

namespace JobMetric\Category\Events;

class CategoryAllowMemberCollectionEvent
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
     * @param array $defaultCategoryAllowMemberCollection
     */
    public function __construct(array $defaultCategoryAllowMemberCollection = [])
    {
        $this->allowMemberCollection = $defaultCategoryAllowMemberCollection;
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
