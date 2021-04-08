<?php

namespace Database\Factories;

use App\Models\Party;
use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Party::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = config('app.env') == 'testing' ? 'PartyTest' : 'Party';

        return [
            'name' => $name . ' ' . $this->faker->randomNumber(),
            'description' => \rand(0, 100) > 70 ? $this->faker->text : null,
            'session_id' => Session::factory()
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        $name = config('app.env') == 'testing' ? 'PartyTest' : 'Party';

        return $this
            ->afterCreating(function (Party $party) use ($name) {
                $party->name = $name . ' ' . $party->id;
                $party->save();
            });
    }

    /**
     * @param App\Models\Session|int $session
     * @return Factory
     */
    public function session($session)
    {
        return $this->state([
            'session_id' => $session instanceof Session ? $session->id : $session,
        ]);
    }
}
