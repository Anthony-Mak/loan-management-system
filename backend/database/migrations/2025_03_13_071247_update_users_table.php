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
            $table->renameColumn('id', 'user_id');

            // Add login_id column to store the old string identifiers
            $table->string('login_id', 255)->unique()->after('user_id');

            
            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password');
            }
            
            // Add your new columns
            $table->unsignedBigInteger('employee_id')->after('login_id');
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

             // Remove the columns we added
            $table->dropColumn(['user_id', 'employee_id', 'username', 'role', 'last_login']);

            $table->renameColumn('user_id', 'id');
            
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
