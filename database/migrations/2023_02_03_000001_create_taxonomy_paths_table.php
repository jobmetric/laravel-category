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
        Schema::create(config('taxonomy.tables.taxonomy_path'), function (Blueprint $table) {
            $table->string('type')->index();
            /**
             * The type field is used to distinguish different types of taxonomies.
             * For example, the type field of the product taxonomy is "product".
             */

            $table->unsignedBigInteger('taxonomy_id')->index();
            /**
             * The taxonomy_id field is used to store the taxonomy ID of the path.
             */

            $table->unsignedBigInteger('path_id')->index();
            /**
             * The path_id field is used to store the taxonomy ID of the path.
             */

            $table->unsignedInteger('level')->default(0);
            /**
             * The level field is used to store the level of the taxonomy.
             * For example, the level of the top-level taxonomy is 0, and the level of the sub-taxonomy is 1.
             */

            $table->unique([
                'type',
                'taxonomy_id',
                'path_id'
            ], 'TAXONOMY_PATHS_UNIQUE_KEY');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('taxonomy.tables.taxonomy_path'));
    }
};
