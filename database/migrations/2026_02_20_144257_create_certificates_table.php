<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id('certificateId');
            $table->unsignedBigInteger('matchId');
            $table->unsignedBigInteger('donorId');
            $table->string('certificateNumber')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('itemName');
            $table->integer('quantity');
            $table->string('category')->nullable();
            $table->string('recipientName'); // Receiver's name
            $table->date('issueDate');
            $table->string('filePath')->nullable(); // Path to PDF file
            $table->enum('status', ['generated', 'sent', 'viewed'])->default('generated');
            $table->timestamps();

            $table->foreign('matchId')->references('matchId')->on('matches')->onDelete('cascade');
            $table->foreign('donorId')->references('id')->on('donors')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};