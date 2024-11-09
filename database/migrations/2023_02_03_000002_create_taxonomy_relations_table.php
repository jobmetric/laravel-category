<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('taxonomy.tables.taxonomy_relation'), function (Blueprint $table) {
            $table->foreignId('taxonomy_id')->index()
                ->references('id')->on(config('taxonomy.tables.taxonomy'))->cascadeOnDelete()->cascadeOnUpdate();

            $table->morphs('taxonomizable');
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

            $table->dateTime('created_at')->index()->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->unique([
                'taxonomy_id',
                'taxonomizable_type',
                'taxonomizable_id',
                'collection'
            ], 'TAXONOMY_RELATION_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('taxonomy.tables.taxonomy_relation'));
    }
};
