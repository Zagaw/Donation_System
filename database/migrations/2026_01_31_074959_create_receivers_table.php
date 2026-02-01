<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiversTable extends Migration
{
    public function up()
    {
        Schema::create('receivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->constrained('users', 'userId')->onDelete('cascade');
            $table->enum('receiverType', ['personal', 'organization']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receivers');
    }
}
