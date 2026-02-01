<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonorsTable extends Migration
{
    public function up()
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->constrained('users', 'userId')->onDelete('cascade');
            $table->enum('donorType', ['personal', 'organization']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('donors');
    }
}
