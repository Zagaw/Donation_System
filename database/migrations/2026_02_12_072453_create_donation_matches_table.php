<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('donation_matches', function (Blueprint $table) {
        $table->id();

        $table->foreignId('donation_id')->constrained()->onDelete('cascade');
        $table->foreignId('request_id')->constrained()->onDelete('cascade');

        $table->foreignId('donor_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

        $table->enum('status', ['pending', 'completed'])->default('pending');

        $table->timestamps();

        //forerin key
        $table->foreignId('donorId')->references('userId')->on('users');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_matches');
    }
};
