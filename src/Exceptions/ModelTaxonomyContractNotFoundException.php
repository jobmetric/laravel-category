<?php

namespace JobMetric\Taxonomy\Exceptions;

use Exception;
use Throwable;

class ModelTaxonomyContractNotFoundException extends Exception
{
    public function __construct(string $model, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('taxonomy::base.exceptions.model_taxonomy_contract_not_found', [
            'model' => $model
        ]), $code, $previous);
    }
}
