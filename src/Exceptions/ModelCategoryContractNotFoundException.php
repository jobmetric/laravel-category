<?php

namespace JobMetric\Category\Exceptions;

use Exception;
use Throwable;

class ModelCategoryContractNotFoundException extends Exception
{
    public function __construct(string $model, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('category::base.exceptions.model_category_contract_not_found', [
            'model' => $model
        ]), $code, $previous);
    }
}
