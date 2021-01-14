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

        $thisYear = now()->year;
        $fromYear = $thisYear - 12;

        for ($i = $fromYear; $i < $thisYear; $i++) {
            if ($i == 2020 || $i === 2019 || $i == 2018) {
                continue;
            }

            Session::create([
                'name' => 'Election '.$i,
                'year' => $i,
                'active' => false,
                'description' => \rand(0, 100) > 60 ? $faker->text : null,
                'registration' => \rand(0, 100) > 40 ? true : false,
                'verification_type' => $this->randomVerificationType(),
                'cancelled_at' => \rand(0, 100) <= 5 ? now() : null,
                'completed_at' => \rand(0, 100) >= 10 ? now() : null,
            ]);
        }

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

    private function randomVerificationType()
    {
        $n = \rand(0, 100);

        if ($n >= 90) {
            return 'open';
        }

        if ($n >= 60) {
            return 'email';
        }

        return 'code';
    }
}
