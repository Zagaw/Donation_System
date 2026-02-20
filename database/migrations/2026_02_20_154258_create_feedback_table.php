<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id('feedbackId');
            $table->unsignedBigInteger('userId');
            $table->enum('userRole', ['donor', 'receiver']);
            $table->unsignedBigInteger('matchId')->nullable();
            $table->integer('rating')->unsigned()->between(1, 5);
            $table->text('comment')->nullable();
            $table->enum('category', [
                'donation_experience',
                'request_experience',
                'matching_process',
                'communication',
                'platform_usability',
                'other'
            ])->default('other');
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected', 'featured'])->default('pending');
            $table->string('admin_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('matchId')->references('matchId')->on('matches')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback');
    }
};