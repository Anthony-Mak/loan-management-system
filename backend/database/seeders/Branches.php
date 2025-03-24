<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Branches extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('branches')->insert([
            [
                'branch_name' => 'HEAD OFFICE',
                'location' => '31 Mutley Bend, Belvedere, Harare',
                'branch_code' => '3400',
            ],
            [
                'branch_name' => 'HARARE',
                'location' => '56-60 Samora Machel, Harare',
                'branch_code' => '3401',
            ],
            [
                'branch_name' => 'BULAWAYO',
                'location' => 'York House Cnr Herbet Chitepo - 8th Ave, Bulawayo',
                'branch_code' => '3402',
            ],
            [
                'branch_name' => 'GWERU',
                'location' => 'First Mutual Building-Office 7, Cnr Robert Mugabe & Fifth Street, Gweru',
                'branch_code' => '3403',
            ],
            [
                'branch_name' => 'MUTARE',
                'location' => 'Plumpton Building, 95 Herbet Chitepo Street, Mutare',
                'branch_code' => '3404',
            ],
            [
                'branch_name' => 'MASVINGO',
                'location' => 'Masvingo Trade Center, 267 Simon Muzenda, Masvingo',
                'branch_code' => '3405',
            ]
            
        ]);
    }
}
