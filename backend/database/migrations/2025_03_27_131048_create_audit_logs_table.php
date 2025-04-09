<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('audit_log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('action_type');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('original_data')->nullable();
            $table->text('updated_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};