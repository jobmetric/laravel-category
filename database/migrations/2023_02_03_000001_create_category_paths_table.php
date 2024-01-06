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
        Schema::create(config('category.tables.category_path'), function (Blueprint $table) {
            $table->id();

            $table->string('type')->index();
            /**
             * The type field is used to distinguish different types of categories.
             * For example, the type field of the product category is "product".
             */

            $table->unsignedBigInteger('category_id')->index();
            /**
             * The category_id field is used to store the category ID of the path.
             */

            $table->unsignedBigInteger('path_id')->index();
            /**
             * The path_id field is used to store the category ID of the path.
             */

            $table->unsignedInteger('level')->default(0);
            /**
             * The level field is used to store the level of the category.
             * For example, the level of the top-level category is 0, and the level of the sub-category is 1.
             */

            $table->unique([
                'type',
                'category_id',
                'path_id'
            ], 'CATEGORY_PATHS_UNIQUE_KEY');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('category.tables.category_path'));
    }
};
