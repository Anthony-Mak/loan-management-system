<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('loan_types')->insert([
            [
                'name' => 'Personal Loan',
                'description' => 'General purpose loan for personal needs',
                'interest_rate' => 3.00,
                'max_amount' => 5000.00,
                'max_term' => 24,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Home Improvement Loan',
                'description' => 'Loan for home repairs and improvements',
                'interest_rate' => 2.00,
                'max_amount' => 15000.00,
                'max_term' => 36,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Car Loan',
                'description' => 'Loan for vehicle purchase',
                'interest_rate' => 9.50,
                'max_amount' => 10000.00,
                'max_term' => 60,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Educational Loan',
                'description' => 'Loan for educational purposes',
                'interest_rate' => 7.00,
                'max_amount' => 8000.00,
                'max_term' => 48,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
