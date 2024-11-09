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
        Schema::create(config('taxonomy.tables.taxonomy'), function (Blueprint $table) {
            $table->id();

            $table->string('type')->index();
            /**
             * The type field is used to distinguish different types of taxonomies.
             * For example, the type field of the product taxonomy is "product".
             */

            $table->unsignedBigInteger('parent_id')->nullable()->index();
            /**
             * The parent_id field is used to store the parent taxonomy ID.
             * If the parent_id field is 0, it means that the taxonomy is a top-level taxonomy.
             */

            $table->integer('ordering')->default(0);
            /**
             * The display ordering all taxonomies of the same level can be considered with this field.
             */

            $table->boolean('status')->default(true)->index();
            /**
             * If the taxonomy is not active, it will not be displayed in the taxonomy list.
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
        Schema::dropIfExists(config('taxonomy.tables.taxonomy'));
    }
};
