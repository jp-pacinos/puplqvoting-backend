<?php

namespace Tests\Concerns;

use App\Models\UserStudent;

/**
 * @covers App\Models\UserStudent
 */
trait InteractsWithStudent
{
    /**
     * @var App\Models\UserStudent
     */
    protected $actingStudent;

    /**
     * Set the currently logged in user for the application.
     *
     * @return $this
     */
    protected function actingAsStudent()
    {
        $this->actingStudent = UserStudent::factory()->canVote()->create();

        return $this->actingAs($this->actingStudent);
    }

    /**
     * Add Authorization to Headers using Admin credentials
     *
     * @return $this
     */
    protected function withAuthStudent()
    {
        $this->actingStudent = UserStudent::factory()->canVote()->create();

        $token = $this->actingStudent->createToken('student-token')->plainTextToken;

        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }
}
