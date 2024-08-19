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
        Schema::create(config('category.tables.category'), function (Blueprint $table) {
            $table->id();

            $table->string('type')->index();
            /**
             * The type field is used to distinguish different types of categories.
             * For example, the type field of the product category is "product".
             */

            $table->unsignedBigInteger('parent_id')->default(0)->index();
            /**
             * The parent_id field is used to store the parent category ID.
             * If the parent_id field is 0, it means that the category is a top-level category.
             */

            $table->integer('ordering')->default(0);
            /**
             * The display ordering all categories of the same level can be considered with this field.
             */

            $table->boolean('status')->default(true)->index();
            /**
             * If the category is not active, it will not be displayed in the category list.
             */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('category.tables.category'));
    }
};
