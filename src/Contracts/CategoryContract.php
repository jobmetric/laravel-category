<?php

namespace JobMetric\Category\Contracts;

interface CategoryContract
{
    /**
     * category allows the type.
     *
     * @return array
     */
    public function categoryAllowTypes(): array;
}
