<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('employees')->insert([
            'employee_id' => '09',
            'title' => 'Mr',
            'full_name' => 'John Doe',
            'national_id' => '521173685E16',
            'date_of_birth' => '2001-9-03',
            'gender' => 'male',
            'marital_status' => 'married',
            'dependents' => 4,
            'physical_address' => '56 Aspindale Block 5',
            'accommodation_type' => null, // Not provided in the information
            'postal_address' => null, // Not provided in the information
            'cell_phone' => '263787716181',
            'email' => 'johndoe@gmail.com',
            'next_of_kin' => 'Jane Doe',
            'next_of_kin_address' => '56 Aspindale Block 5',
            'next_of_kin_cell' => '26371653767', // Not provided in the information
            'branch_id' => 7, // Not provided in the information
            'salary_gross' => null, // Not provided in the information
            'salary_net' => null, // Not provided in the information
            'department' => 'Procurement',
            'position' => 'Manager',
            'hire_date' => '2020-07-09',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}