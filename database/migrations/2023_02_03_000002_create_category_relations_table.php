<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('category.tables.category_relation'), function (Blueprint $table) {
            $table->foreignId('category_id')->index()
                ->references('id')->on(config('category.tables.category'))->cascadeOnDelete()->cascadeOnUpdate();

            $table->morphs('categorizable');
            /**
             * relatable to:
             *
             * Product
             * Post
             * ...
             */

            $table->string('collection')->nullable();
            /**
             * for another collection file
             *
             * null value for base collection
             */

            $table->unique([
                'category_id',
                'categorizable_type',
                'categorizable_id',
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
