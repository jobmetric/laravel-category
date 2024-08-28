<?php

namespace JobMetric\Category\Exceptions;

use Exception;
use Throwable;

class CategoryUsedException extends Exception
{
    public function __construct(string $name, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(trans('category::base.exceptions.category_used', [
            'name' => $name,
        ]), $code, $previous);
    }
}
