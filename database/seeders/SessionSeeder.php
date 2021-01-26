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
        $fromYear = ($thisYear - 7);

        for ($i = $fromYear; $i < $thisYear; $i++) {
            if ($i == ($thisYear - 1) || $i === ($thisYear - 2) || $i == ($thisYear - 3)) {
                continue;
            }

            $isCompleted = \rand(0, 100) >= 22 ? now() : null;

            Session::create([
                'name' => 'Election '.$i,
                'year' => $i,
                'active' => false,
                'description' => \rand(0, 100) > 60 ? $faker->text : null,
                'registration' => false,
                'verification_type' => $this->randomVerificationType(),
                'cancelled_at' => $isCompleted ? null : now(),
                'completed_at' => $isCompleted ? now() : null,
            ]);
        }

        Session::create([
            'name' => 'Election '.($thisYear - 1),
            'year' => ($thisYear - 1),
            'active' => true,
            'registration' => true,
            'verification_type' => 'code',
        ]);

        Session::create([
            'name' => 'Election '.($thisYear - 2),
            'year' => ($thisYear - 2),
            'description' => $faker->text,
            'active' => false,
            'registration' => false,
            'verification_type' => 'code',
            'registration_at' => Carbon::now(),
            'completed_at' => Carbon::now(),
        ]);

        Session::create([
            'name' => 'Election '.($thisYear - 3),
            'year' => ($thisYear - 3),
            'description' => $faker->text,
            'active' => false,
            'registration' => false,
            'verification_type' => 'email',
            'registration_at' => Carbon::now(),
            'completed_at' => Carbon::now(),
        ]);

        Session::create([
            'name' => 'Election '.($thisYear - 3),
            'year' => ($thisYear - 3),
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
