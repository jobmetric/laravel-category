<?php

namespace JobMetric\Taxonomy\Exceptions;

use Exception;
use Throwable;

class TaxonomyTypeNotMatchException extends Exception
{
    public function __construct(string $type, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(trans('taxonomy::base.exceptions.taxonomy_type_not_match', [
            'type' => $type,
        ]), $code, $previous);
    }
}
