<?php

namespace JobMetric\Category\Exceptions;

use Exception;
use Throwable;

class CategoryIsDisableException extends Exception
{
    public function __construct(int $number, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(trans('category::base.exceptions.category_is_disable', [
            'number' => $number,
        ]), $code, $previous);
    }
}
