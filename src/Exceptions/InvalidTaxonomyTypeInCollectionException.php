<?php

namespace JobMetric\Taxonomy\Exceptions;

use Exception;
use Throwable;

class InvalidTaxonomyTypeInCollectionException extends Exception
{
    public function __construct(string $baseType, string $collection, string $collectionType, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('taxonomy::base.exceptions.invalid_taxonomy_type_in_collection', [
            'baseType' => $baseType,
            'collection' => $collection,
            'collectionType' => $collectionType,
        ]), $code, $previous);
    }
}
