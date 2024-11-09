<?php

namespace JobMetric\Taxonomy;

use Illuminate\Support\Facades\Route;
use JobMetric\Taxonomy\Events\TaxonomyTypeEvent;
use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class TaxonomyServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @param PackageCore $package
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     * @throws RegisterClassTypeNotFoundException
     * @throws ViewFolderNotFoundException
     * @throws AssetFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-taxonomy')
            ->hasConfig()
            ->hasMigration()
            ->hasTranslation()
            ->hasRoute()
            ->hasView()
            ->hasAsset()
            ->registerClass('Taxonomy', Taxonomy::class);
    }

    /**
     * After register package
     *
     * @return void
     */
    public function afterRegisterPackage(): void
    {
        $this->app->singleton('taxonomyType', function () {
            $event = new TaxonomyTypeEvent;
            event($event);

            return $event->taxonomyType;
        });

        // Register model binding
        Route::model('jm_taxonomy', \JobMetric\Taxonomy\Models\Taxonomy::class);
        Route::model('jm_taxonomy_path', \JobMetric\Taxonomy\Models\TaxonomyPath::class);
        Route::model('jm_taxonomy_relation', \JobMetric\Taxonomy\Models\TaxonomyRelation::class);
    }
}
