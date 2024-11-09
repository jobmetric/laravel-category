<?php

namespace JobMetric\Taxonomy\Exceptions;

use Exception;
use Throwable;

class TaxonomyUsedException extends Exception
{
    public function __construct(string $name, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(trans('taxonomy::base.exceptions.taxonomy_used', [
            'name' => $name,
        ]), $code, $previous);
    }
}
