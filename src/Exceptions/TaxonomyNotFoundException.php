<?php

namespace JobMetric\Taxonomy\Exceptions;

use Exception;
use Throwable;

class TaxonomyNotFoundException extends Exception
{
    public function __construct(int $number, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(trans('taxonomy::base.exceptions.taxonomy_not_found', [
            'number' => $number,
        ]), $code, $previous);
    }
}
