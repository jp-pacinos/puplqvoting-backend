<?php

namespace Database\Factories;

use App\Models\Party;
use App\Models\Official;
use App\Models\Position;
use App\Models\UserStudent;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Official::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'display_picture' => null,
            'position_id' => Position::factory(),
            'student_id' => UserStudent::factory(),
            'party_id' => Party::factory(),
        ];
    }

    /**
     * @param App\Models\Position|int $position
     * @return Factory
     */
    public function position($position)
    {
        return $this->state([
            'position_id' => $position instanceof Position ? $position->id : $position,
        ]);
    }

    /**
     * @param App\Models\Student|int $student
     * @return Factory
     */
    public function student($student)
    {
        return $this->state([
            'student_id' => $student instanceof UserStudent ? $student->id : $student,
        ]);
    }

    /**
     * @param App\Models\Party|int $party
     * @return Factory
     */
    public function party($party)
    {
        return $this->state([
            'party_id' => $party instanceof Party ? $party->id : $party,
        ]);
    }
}
