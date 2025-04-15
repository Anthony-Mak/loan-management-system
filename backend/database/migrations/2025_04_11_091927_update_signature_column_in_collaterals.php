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
        Schema::table('collaterals', function (Blueprint $table) {
            // Change signature field to store URLs
            $table->string('signature', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collaterals', function (Blueprint $table) {
            // Revert change if needed
            $table->string('signature')->nullable()->change();
        });
    }
};