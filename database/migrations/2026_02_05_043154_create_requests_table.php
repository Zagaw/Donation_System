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
            $table->text('description')->nullable();

            // REQUIRED NRC fields
            $table->string('nrcNumber');
            $table->string('nrcFrontImage');
            $table->string('nrcBackImage');

            $table->enum('status', ['pending', 'approved', 'rejected',  'matched', 'executed', 'completed'])
                  ->default('pending');

            $table->timestamps();

            $table->foreign('receiverId')
                ->references('id')->on('receivers')
                ->onDelete('cascade');
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
