<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\UserStudent;
use Tests\Concerns\InteractsWithActiveElection;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * To test:
 *
 * Cases for students
 * - Login but no student - 401
 * - Login but inputs required - 422
 * - Can login - 200
 * - Can login because registered - 200
 * - Can login but unable to vote - 403
 * - Can login but already voted - 403
 * - Can login but student is not registered - 401
 *
 * Others in Election
 * - Can login but Election is not yet started - 403
 * - Can login but Election is ended - 403
 *
 * @covers App\Http\Controllers\Auth\Student\LoginController
 */
final class StudentLoginTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithActiveElection;

    protected $loginRoute = '/api/auth/login/student';

    public function testStudentLoginButNoRecordInDatabase()
    {
        $this->electionHasStarted();

        $response = $this->postJson($this->loginRoute, $this->fakeStudent());
        $response->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function testStudentLoginWithIncompleteInputs()
    {
        $this->electionHasStarted();

        $response = $this->postJson($this->loginRoute);
        $response->assertStatus(422)->assertJsonValidationErrors([
            'student_number',
            'lastname',
            'firstname',
            'birthdate',
        ]);
    }

    public function testStudentCanLogin()
    {
        $this->electionHasStarted();

        $response = $this->postJson($this->loginRoute, $this->realStudent());
        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function testStudentCanLoginAndRegistered()
    {
        $student = $this->realStudent();
        $this->electionHasStartedWithRegistration(['registered' => [$student['test_id']]]);

        $response = $this->postJson($this->loginRoute, $student);
        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function testStudentCanLoginButUnableToVote()
    {
        $this->electionHasStarted();

        $response = $this->postJson($this->loginRoute, $this->realStudent($canVote = false));
        $response->assertForbidden()->assertJsonStructure(['message']);
    }

    public function testStudentCanLoginButAlreadyVoted()
    {
        $student = $this->realStudent();
        $this->electionHasStarted([
            // Lists of students that have voted.
            'voted' => [$student['test_id']],
        ]);

        $response = $this->postJson($this->loginRoute, $student);
        $response->assertForbidden()->assertJsonStructure(['message']);
    }

    public function testStudentCanLoginButNotRegistered()
    {
        $this->electionHasStartedWithRegistration();

        $response = $this->postJson($this->loginRoute, $this->realStudent());
        $response->assertForbidden()->assertJsonStructure(['message', 'registration_url']);
    }

    public function testStudentCanLoginButElectionIsNotYetStarted()
    {
        $this->electionHasNotStarted();

        $response = $this->postJson($this->loginRoute, $this->realStudent());
        $response->assertForbidden()->assertJsonStructure(['message']);
    }

    public function testStudentCanLoginButElectionIsEnded()
    {
        $this->electionHasEnded();

        $response = $this->postJson($this->loginRoute, $this->realStudent());
        $response->assertForbidden()->assertJsonStructure(['message']);
    }

    private function fakeStudent()
    {
        return [
            'student_number' => '0000-000-0',
            'lastname' => 'Lastname321',
            'firstname' => 'Firstname321',
            'middlename' => 'Middlename431',
            'birthdate' => now(),
        ];
    }

    private function realStudent($canVote = true)
    {
        $student = $canVote ?
            UserStudent::factory()->create() :
            UserStudent::factory()->unableToVote()->create();

        return collect($student->toArray())->only([
            'student_number',
            'lastname',
            'firstname',
            'middlename',
            'birthdate',
        ])->toArray() + ['test_id' => $student->id];
    }
}
