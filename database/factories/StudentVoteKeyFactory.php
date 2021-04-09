<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\UserStudent;
use App\Models\StudentVoteKey;
use App\Services\CodeGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentVoteKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentVoteKey::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'confirmation_code' => CodeGenerator::make(7),
            'session_id' => Session::factory(),
            'student_id' => UserStudent::factory(),
        ];
    }

    /**
     * @param App\Models\UserStudent|int $student
     * @return Factory
     */
    public function student($student)
    {
        return $this->state([
            'student_id' => $student instanceof UserStudent ? $student->id : $student,
        ]);
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
