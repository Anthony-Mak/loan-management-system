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
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id('loan_id');
            $table->string('employee_id', 50); 
            $table->foreign('employee_id')->references('employee_id')->on('employees');
            $table->decimal('amount', 10, 2);
            $table->text('purpose')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('review')->nullable();
            $table->timestamp('application_date')->useCurrent();
            $table->timestamp('processed_date')->nullable();
            $table->string('processed_by', 50)->nullable();
            $table->foreign('processed_by')->references('employee_id')->on('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
