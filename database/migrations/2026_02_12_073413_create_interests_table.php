<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id('interestId');
            $table->unsignedBigInteger('donorId');
            $table->unsignedBigInteger('requestId');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'completed'
            ])->default('pending');

            $table->timestamps();

            $table->foreign('donorId')
                ->references('id')->on('donors')
                ->onDelete('cascade');

            $table->foreign('requestId')
                ->references('requestId')->on('requests')
                ->onDelete('cascade');

            // Prevent duplicate interest
            $table->unique(['donorId', 'requestId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interests');
    }
};

