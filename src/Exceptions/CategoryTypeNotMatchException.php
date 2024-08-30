<?php

namespace JobMetric\Category\Exceptions;

use Exception;
use Throwable;

class CategoryTypeNotMatchException extends Exception
{
    public function __construct(string $type, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(trans('category::base.exceptions.category_type_not_match', [
            'type' => $type,
        ]), $code, $previous);
    }
}
