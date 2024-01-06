<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JobMetric\Category\Models\Category;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('category.tables.category_relation'), function (Blueprint $table) {
            $table->morphs('relatable');
            /**
             * relatable to:
             *
             * Product
             * Post
             * ...
             */

            $table->foreignId('category_id')->index()
                ->references('id')->on((new Category)->getTable())->cascadeOnDelete()->cascadeOnUpdate();

            $table->string('collection', 100)->nullable();
            /**
             * for another collection file
             *
             * null value for base collection
             */

            $table->unique([
                'relatable_type',
                'relatable_id',
                'category_id',
                'collection'
            ], 'CATEGORY_RELATION_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('category.tables.category_relation'));
    }
};
