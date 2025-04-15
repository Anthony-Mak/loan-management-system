<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            // Update the pledge_signature field to store URLs up to 255 characters
            $table->string('pledge_signature', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            // Revert back if needed
            $table->string('pledge_signature')->nullable()->change();
        });
    }
};