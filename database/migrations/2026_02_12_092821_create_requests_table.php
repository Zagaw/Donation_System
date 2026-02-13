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
        Schema::create('requests', function (Blueprint $table) {
            $table->id('requestId');
            $table->unsignedBigInteger('receiverId');
            $table->string('itemName');
            $table->string('category');
            $table->integer('quantity');
            $table->text('description');
            $table->string('status')->default('pending');
            $table->string('nrcNumber')->nullable();
            $table->string('nrcFrontImage')->nullable();
            $table->string('nrcBackImage')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('receiverId')->references('userId')->on('users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
