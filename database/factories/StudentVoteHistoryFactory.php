<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\StudentVoteHistory;
use App\Models\UserStudent;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentVoteHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentVoteHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_id' => UserStudent::factory(),
            'session_id' => Session::factory(),
            'verified_at' => null
        ];
    }

    /**
     * @return Factory
     */
    public function verified()
    {
        return $this->state(['verified_at' => now()]);
    }

    /**
     * @param App\Models\UserStudent|int $student
     * @return Factory
     */
    public function student($student)
    {
        return $this->state([
            'student_id' => $student instanceof UserStudent ? $student->id : $student
        ]);
    }

    /**
     * @param App\Models\Session|int $session
     * @return Factory
     */
    public function session($session)
    {
        return $this->state([
            'session_id' => $session instanceof Session ? $session->id : $session
        ]);
    }
}
