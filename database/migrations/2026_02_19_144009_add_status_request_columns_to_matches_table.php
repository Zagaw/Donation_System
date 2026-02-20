<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->boolean('execution_requested')->default(false)->after('status');
            $table->timestamp('execution_requested_at')->nullable()->after('execution_requested');
            $table->string('execution_requested_by')->nullable()->after('execution_requested_at');
            
        });
    }

    public function down()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'execution_requested',
                'execution_requested_at',
                'execution_requested_by',
            ]);
        });
    }
};