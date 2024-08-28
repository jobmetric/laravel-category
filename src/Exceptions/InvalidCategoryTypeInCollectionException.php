<?php

namespace JobMetric\Category\Exceptions;

use Exception;
use Throwable;

class InvalidCategoryTypeInCollectionException extends Exception
{
    public function __construct(string $baseType, string $collection, string $collectionType, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('category::base.exceptions.invalid_category_type_in_collection', [
            'baseType' => $baseType,
            'collection' => $collection,
            'collectionType' => $collectionType,
        ]), $code, $previous);
    }
}
