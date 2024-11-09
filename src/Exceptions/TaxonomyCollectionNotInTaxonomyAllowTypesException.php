<?php

namespace JobMetric\Taxonomy\Exceptions;

use Exception;
use Throwable;

class TaxonomyCollectionNotInTaxonomyAllowTypesException extends Exception
{
    public function __construct(string $collection, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('taxonomy::base.exceptions.taxonomy_collection_not_in_taxonomy_allow_types', [
            'collection' => $collection,
        ]), $code, $previous);
    }
}
