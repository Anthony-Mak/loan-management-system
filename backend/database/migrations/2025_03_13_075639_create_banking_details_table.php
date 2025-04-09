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
        Schema::create('banking_details', function (Blueprint $table) {
            $table->id('banking_id');
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->boolean('has_zwmb_account')->default(false);
            $table->integer('years_with_zwmb')->nullable();
            $table->string('branch', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->boolean('had_previous_loan')->default(false);
            $table->decimal('previous_loan_amount', 10, 2)->nullable();
            $table->decimal('current_balance', 10, 2)->nullable();
            $table->date('loan_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->decimal('monthly_repayment', 10, 2)->nullable();
            $table->string('other_bank', 100)->nullable();
            $table->string('other_account_type', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banking_details');
    }
};
