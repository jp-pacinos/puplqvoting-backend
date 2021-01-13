<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        Session::create([
            'name' => 'Covid Season',
            'year' => 2020,
            'active' => true,
            'registration' => true,
            'verification_type' => 'email',
        ]);

        Session::create([
            'name' => 'Happy Unknown',
            'year' => 2019,
            'description' => $faker->text,
            'active' => false,
            'registration' => true,
            'verification_type' => 'code',
            'registration_at' => Carbon::now(),
            'completed_at' => Carbon::now(),
        ]);

        Session::create([
            'name' => 'Election 2018',
            'year' => 2018,
            'description' => $faker->text,
            'active' => false,
            'registration' => true,
            'verification_type' => 'code',
            'registration_at' => Carbon::now(),
            'completed_at' => Carbon::now(),
        ]);

        Session::create([
            'name' => 'Election 2018',
            'year' => 2018,
            'description' => $faker->text,
            'active' => false,
            'registration' => false,
            'verification_type' => 'open',
            'cancelled_at' => Carbon::now(),
        ]);

    }
}
