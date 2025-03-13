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
        Schema::create('employees', function (Blueprint $table) {
            $table->string('employee_id', 30)->primary();         
            $table->string('title')->nullable();
            $table->string('full_name')->nullable();
            $table->string('national_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->integer('dependents')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('accommodation_type')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('cell_phone')->nullable();
            $table->string('email', 100)->unique();
            $table->string('next_of_kin')->nullable();
            $table->string('next_of_kin_address')->nullable();
            $table->string('next_of_kin_cell')->nullable();
            $table->integer('branch_id')->nullable();
            $table->decimal('salary_gross', 10, 2)->nullable();
            $table->decimal('salary_net', 10, 2)->nullable();     
            $table->string('department', 50)->nullable();
            $table->string('position', 50)->nullable();
            $table->date('hire_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
