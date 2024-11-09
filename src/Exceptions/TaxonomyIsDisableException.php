<?php

namespace JobMetric\Taxonomy\Exceptions;

use Exception;
use Throwable;

class TaxonomyIsDisableException extends Exception
{
    public function __construct(int $number, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(trans('taxonomy::base.exceptions.taxonomy_is_disable', [
            'number' => $number,
        ]), $code, $previous);
    }
}
