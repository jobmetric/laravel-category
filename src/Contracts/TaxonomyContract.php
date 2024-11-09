<?php

namespace JobMetric\Taxonomy\Contracts;

interface TaxonomyContract
{
    /**
     * taxonomy allows the type.
     *
     * @return array
     */
    public function taxonomyAllowTypes(): array;
}
