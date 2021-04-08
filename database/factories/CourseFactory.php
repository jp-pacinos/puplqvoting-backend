<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $degreeAcronym = \rand(0, 10) >= 3 ? ['Bachelor of Science in ', 'BS'] : ['Diploma in ', 'DI'];
        $jobTitle = $this->faker->jobTitle;

        $acronym = $degreeAcronym[1];

        $expJobTitle = \explode(' ', $jobTitle);

        if (\count($expJobTitle) > 1) {
            $max = 2;
            $counter = 0;

            foreach ($expJobTitle as $value) {
                if ($max == $counter) {
                    break;
                }

                // it will exclude 'and' 'in' etc...
                if ($value[0] === \strtoupper($value[0])) {
                    $acronym .= $value[0];
                    $counter++;
                }
            }
        } else {
            $acronym .= $jobTitle[0] . $jobTitle[1];
        }

        return [
            'name' => $degreeAcronym[0] . $jobTitle,
            'acronym' => \strtoupper($acronym) . $this->faker->randomNumber(3),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
