<?php

namespace JobMetric\Category\Exceptions;

use Exception;
use Throwable;

class CategoryCollectionNotInCategoryAllowTypesException extends Exception
{
    public function __construct(string $collection, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('category::base.exceptions.category_collection_not_in_category_allow_types', [
            'collection' => $collection,
        ]), $code, $previous);
    }
}
