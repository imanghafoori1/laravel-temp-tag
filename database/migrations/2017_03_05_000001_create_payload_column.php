<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayloadColumn extends Migration
{
    public function up(): void
    {
        Schema::table('temp_tags', function (Blueprint $table) {
            $table->json('payload')->nullable();
            $table->dropColumn('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('temp_tags', function (Blueprint $table) {
            $table->dropColumn('payload');
        });
    }
}
