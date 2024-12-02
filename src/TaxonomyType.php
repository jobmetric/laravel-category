<?php

namespace JobMetric\Taxonomy;

use Illuminate\Support\Traits\Macroable;
use JobMetric\Metadata\MetadataServiceType;
use JobMetric\PackageCore\Services\HierarchicalServiceType;
use JobMetric\PackageCore\Services\InformationServiceType;
use JobMetric\PackageCore\Services\ServiceType;
use JobMetric\Url\UrlServiceType;

class TaxonomyType extends ServiceType
{
    use Macroable,
        InformationServiceType,
        HierarchicalServiceType,
        UrlServiceType,
        MetadataServiceType;

    protected function serviceType(): string
    {
        return 'taxonomyTypeData';
    }
}
