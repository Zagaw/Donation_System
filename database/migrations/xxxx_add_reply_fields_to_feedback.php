<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->text('reply_message')->nullable();
            $table->timestamp('replied_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn(['reply_message','replied_at']);
        });
    }
};
