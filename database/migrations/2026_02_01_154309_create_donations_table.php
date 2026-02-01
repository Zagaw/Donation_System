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
        Schema::create('donations', function (Blueprint $table) {
            $table->id('donationId');
            $table->unsignedBigInteger('donorId');
            $table->string('itemName');
            $table->string('category')->after('donorId');
            $table->integer('quantity');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])
          ->default('pending');
            // pending | approved | matched | completed
            $table->timestamps();

            $table->foreign('donorId')
                ->references('id')->on('donors')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
