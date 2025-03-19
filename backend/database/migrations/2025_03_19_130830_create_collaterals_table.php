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
        Schema::create('collaterals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('loan_id')->on('loan_applications')->onDelete('cascade');
            $table->string('asset_description');
            $table->decimal('estimated_value', 10, 2);
            $table->string('vehicle_registration_number')->nullable();
            $table->string('signature');
            $table->string('location');
            $table->timestamps();
        });

        Schema::table('loan_applications', function (Blueprint $table) {
            $table->boolean('pledge_acknowledged')->default(false);
            $table->string('pledge_signature')->nullable();
            $table->timestamp('pledge_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn(['pledge_acknowledged', 'pledge_signature', 'pledge_date']);
        });
        Schema::dropIfExists('collaterals');
    }
};
