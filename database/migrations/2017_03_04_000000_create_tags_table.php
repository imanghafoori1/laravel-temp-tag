<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('temp_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->string('taggable_type', 25);
            $table->unsignedBigInteger('taggable_id');
            $table->index(['taggable_type', 'taggable_id']);

            $table->string('note', 120)->nullable();
            $table->string('title', 30)->nullable();
            $table->timestamp('expired_at')->nullable()->index();
            $table->timestamp('created_at')->nullable();

            $table->softDeletes();

            $table->unique(['taggable_type', 'taggable_id', 'title', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_tags');
    }
}
