// database/migrations/2026_02_12_xxxxxx_create_certificates_table_new.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing table if it exists
        Schema::dropIfExists('certificates');
        
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('donation_id');
            $table->foreign('donation_id')->references('donationId')->on('donations')->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->string('recipient_name');
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('category');
            $table->string('donor_name')->nullable();
            $table->date('issue_date');
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'generated', 'sent', 'revoked'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};