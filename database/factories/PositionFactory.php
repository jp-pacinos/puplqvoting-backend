<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = config('app.env') == 'testing' ? 'PositionTest' : 'Position';
        $order = $this->faker->randomNumber(3);
        $perParty = \rand(0, 100) >= 10 ? 1 : 12;

        return [
            'name' => $name . ' ' . $order,
            'order' => $order,
            'per_party_count' => $perParty,
            'choose_max_count' => $perParty,
        ];
    }
}
