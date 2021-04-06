<?php

namespace Tests\Concerns;

use App\Models\Session;
use App\Models\Registration;
use App\Models\StudentVoteHistory;
use App\Services\StudentActiveSession;

/**
 * @covers App\Models\Session
 */
trait InteractsWithActiveElection
{
    use InteractsWithAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        (new StudentActiveSession())->clear();
    }

    protected function tearDown(): void
    {
        (new StudentActiveSession())->clear();

        (new Session())->getActive()->delete();

        parent::tearDown();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    private function electionFactory()
    {
        return Session::factory()->isActive();
    }

    /**
     * @param array $students ['voted' => int[], 'registered' => int[]]
     * @param bool $hasRegistration
     * @return mixed
     */
    public function electionHasStarted($students = ['voted' => [], 'registered' => []], $hasRegistration = false)
    {
        $this->actingAsAdmin();

        $election = $this->electionFactory()
            ->state(['started_at' => now(), 'registration' => $hasRegistration])
            ->create();

        $voted = $students['voted'] ?? [];
        if (\count($voted) != 0) {
            foreach ($voted as $id) {
                StudentVoteHistory::factory()
                    ->student($id)
                    ->session($election->id)
                    ->verified()
                    ->create();
            }
        }

        $registered = $students['registered'] ?? [];
        if (\count($registered) != 0) {
            foreach ($registered as $id) {
                Registration::factory()
                    ->student($id)
                    ->session($election->id)
                    ->create();
            }
        }

        return $election;
    }

    public function electionHasStartedWithRegistration($students = ['voted' => [], 'registered' => []])
    {
        $this->actingAsAdmin();

        return $this->electionHasStarted($students, true);
    }

    public function electionHasNotStarted()
    {
        $this->actingAsAdmin();

        return $this->electionFactory()->state(['started_at' => null])->create();
    }

    public function electionHasEnded($type = '')
    {
        $this->actingAsAdmin();

        return $this->electionFactory()->state(['started_at' => now()])->hasEnded($type)->create();
    }
}
