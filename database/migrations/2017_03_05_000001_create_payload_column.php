<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
