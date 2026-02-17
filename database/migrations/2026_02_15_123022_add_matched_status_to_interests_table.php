<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support modifying enums directly, so we need to alter the column
        DB::statement("ALTER TABLE interests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'matched', 'completed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE interests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending'");
    }
};
