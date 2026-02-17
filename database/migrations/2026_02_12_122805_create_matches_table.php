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
        Schema::create('matches', function (Blueprint $table) {
            $table->id('matchId');

            $table->unsignedBigInteger('donationId')->nullable();
            $table->unsignedBigInteger('requestId');

            // nullable → manual matching has no interest
            $table->unsignedBigInteger('interestId')->nullable();

            $table->enum('matchType', ['interest', 'manual'])->default('manual');

            $table->enum('status', ['approved', 'executed', 'completed'])
                ->default('approved');

            $table->timestamps();

            $table->foreign('donationId')->references('donationId')->on('donations')->nullable();
            $table->foreign('requestId')->references('requestId')->on('requests');
            $table->foreign('interestId')->references('interestId')->on('interests')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
