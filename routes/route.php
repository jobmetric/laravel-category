<?php

use Illuminate\Support\Facades\Route;
use JobMetric\Category\Http\Controllers\CategoryController;
use JobMetric\Panelio\Facades\Middleware;

/*
|--------------------------------------------------------------------------
| Laravel Category Routes
|--------------------------------------------------------------------------
|
| All Route in Laravel Category package
|
*/

// category
Route::prefix('p/{panel}/{section}')->group(function () {
    Route::prefix('category')->name('category.')->namespace('JobMetric\Category\Http\Controllers')->group(function () {
        Route::middleware(Middleware::getMiddlewares())->group(function () {
            Route::get('{type}/import', [CategoryController::class, 'import'])->name('import');
            Route::get('{type}/export', [CategoryController::class, 'export'])->name('export');
            Route::options('{type}', [CategoryController::class, 'options'])->name('options');
            Route::resource('{type}', CategoryController::class)->except(['show', 'destroy'])->parameter('{type}', 'jm_category:id');
        });
    });
});
