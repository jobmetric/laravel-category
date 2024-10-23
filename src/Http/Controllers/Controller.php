<?php

namespace JobMetric\Category\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use JobMetric\PackageCore\Controllers\HasResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, HasResponse;
}
