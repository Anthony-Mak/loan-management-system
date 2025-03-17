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
        Schema::table('users', function (Blueprint $table) {
            // Remove columns you don't want
            $table->dropColumn(['name', 'email', 'email_verified_at', 'remember_token']);
            
            // Rename id to user_id and change type (requires dropping and recreating)
            $table->dropColumn('id');
            $table->string('user_id', 255)->primary();
            
            // Add your new columns
            $table->string('employee_id', 30)->nullable();
            $table->string('username', 50)->unique();
            $table->enum('role', ['admin', 'hr', 'employee'])->default('employee')->comment('System roles: admin, hr, employee');
            $table->timestamp('last_login')->nullable();
            
            // Add foreign key
            $table->foreign('employee_id')->references('employee_id')->on('employees');        
        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the columns we added
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['user_id', 'employee_id', 'username', 'role', 'last_login']);
            
            // Add back the original columns
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
        });
    }
};
