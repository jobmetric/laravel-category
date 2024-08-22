<?php

namespace JobMetric\Category\Exceptions;

use Exception;
use Throwable;

class CategoryNotFoundException extends Exception
{
    public function __construct(int $number, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(trans('unit::base.exceptions.category_not_found', [
            'number' => $number,
        ]), $code, $previous);
    }
}
