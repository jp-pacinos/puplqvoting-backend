<?php

namespace Database\Factories;

use TypeError;
use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Session::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $thisYear = now()->year;
        $fromYear = ($thisYear - 7);
        $year = \rand($fromYear, $thisYear);
        $name = config('app.env') == 'testing' ? "ElectionTesting $year" : "Election $year";

        return [
            'name' => $name,
            'description' => \rand(0, 100) > 60 ? $this->faker->text : null,
            'year' => $year,
            'active' => false,
            'registration' => false,
            'verification_type' => $this->faker->randomElement(Session::$VERIFICATION_TYPES),
            'started_at' => null,
            'registration_at' => null,
            'completed_at' =>  null,
            'cancelled_at' => null
        ];
    }

    /**
     * @param bool $active
     * @return Factory
     */
    public function isActive($active = true)
    {
        return $this->state(['active' => $active]);
    }

    /**
     * @param string $type
     * @return Factory
     * @throws \Throwable
     */
    public function verificationTypeIs(string $type)
    {
        throw_if(!\in_array($type, Session::$VERIFICATION_TYPES), TypeError::class, 'Invalid verification type.');

        return $this->state(['verification_type' => $type]);
    }

    /**
     * @param string $type
     * @return Factory
     * @throws \TypeError
     */
    public function hasEnded(string $type = '')
    {
        if ($type) {
            if (!\in_array($type, ['completed', 'cancelled'])) {
                throw new TypeError('Invalid type. Available types "completed", "cancelled".');
            }

            return $this->state([$type . '_at' => now()]);
        }

        $isCompleted = \rand(0, 100) >= 22 ? now() : null;
        return $this->state([
            'completed_at' => $isCompleted ? now() : null,
            'cancelled_at' =>  $isCompleted ? null : now()
        ]);
    }
}
