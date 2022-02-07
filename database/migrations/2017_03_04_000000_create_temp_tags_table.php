<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('temp_tags')) {
            return null;
        }

        Schema::create('temp_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->string('taggable_type', 32);
            $table->unsignedBigInteger('taggable_id');
            $table->index(['taggable_type', 'taggable_id']);

            $table->json('payload')->nullable();
            $table->string('title', 32)->nullable();
            $table->timestamp('expired_at')->nullable()->index();
            $table->timestamp('created_at')->nullable();

            $table->unique(['taggable_type', 'taggable_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_tags');
    }
}
