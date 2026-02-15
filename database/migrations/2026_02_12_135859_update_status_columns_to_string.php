<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->string('status')->default('approved')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('string', function (Blueprint $table) {
            //
        });
    }
};
