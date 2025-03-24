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
                'login_id' => 'LEMUST04',
                'username' => 'LEMUST04',
                'password' => Hash::make('Pass@123'),
                'role' => 'admin',
                'employee_ID' => null,
                'created_at' => '2025-03-19 11:15:14',
                'updated_at' => '2025-03-19 11:15:14',
            ],
            [
                'login_id' => 'RUMAST26',
                'username' => 'RUMAST26',
                'password' => Hash::make('Pass@123'),
                'role' => 'admin',
                'employee_ID' => null,
                'created_at' => '2025-03-19 11:15:18',
                'updated_at' => '2025-03-19 11:17:00',
            ],
            [
                'login_id' => 'TEST02',
                'username' => 'TEST02',
                'password' => Hash::make('Pass@123'),
                'role' => 'employee',
                'employee_ID' => null,
                'created_at' => '2025-03-19 11:15:16',
                'updated_at' => '2025-03-19 11:15:16',
            ],
            [
                'login_id' => 'TEST01',
                'username' => 'TEST01',
                'password' => Hash::make('Pass@123'),
                'role' => 'admin',
                'employee_ID' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

    }
}
