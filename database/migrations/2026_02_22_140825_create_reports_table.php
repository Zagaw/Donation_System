<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('reportId');
            $table->string('title');
            $table->string('type'); // 'overview', 'donations', 'requests', 'users'
            $table->string('format'); // 'CSV'
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('date_range'); // 'week', 'month', 'quarter', 'year'
            $table->timestamp('generated_at');
            $table->unsignedBigInteger('generated_by'); // user ID
            $table->timestamps();

            $table->foreign('generated_by')->references('userId')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};