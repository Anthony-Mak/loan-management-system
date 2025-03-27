<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users
        DB::table('users')->insert([
            [
                'login_id' => 'HR01',
                'username' => 'HR01',
                'password' => Hash::make('Pass@123'),
                'role' => 'hr',
                'employee_ID' => null,
                'created_at' => '2025-03-19 11:15:14',
                'updated_at' => '2025-03-19 11:15:14',
            ],
           
        ]);

    }
}
