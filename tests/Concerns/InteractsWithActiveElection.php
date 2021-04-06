<?php

namespace Tests\Concerns;

use Error;
use App\Models\Session;
use Illuminate\Support\Str;
use App\Models\Registration;
use App\Models\StudentVoteHistory;
use App\Services\StudentActiveSession;

/**
 * @covers App\Models\Session
 */
trait InteractsWithActiveElection
{
    /**
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    private function electionFactory()
    {
        (new StudentActiveSession())->clear();

        $this->electionDestroy();

        return Session::factory()->isActive();
    }

    public function electionDestroy()
    {
        $session = (new Session())->getActive();
        if (!$session) {
            return;
        }

        $validName = $session && Str::startsWith($session->name, ['ElectionTest', 'ElectionTesting']);
        if (!$validName) {
            throw new Error(
                "Session $session->name"
                    . ' is not valid session to test. '
                    . 'Please use "ElectionTest" or "ElectionTesting" name in your tests.'
            );
        }

        $session->delete();
    }

    /**
     * @param array $students ['voted' => int[], 'registered' => int[]]
     * @param bool $hasRegistration
     * @param bool $withCandidates
     * @return mixed
     */
    public function electionHasStarted(
        $students = ['voted' => [], 'registered' => []],
        $hasRegistration = false,
        $withCandidates = false
    ) {
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

        if ($withCandidates) {
            // TODO generate candidates.
        }

        return $election;
    }

    public function electionHasStartedWithCandidates($students = [], $hasRegistration = false)
    {
        return $this->electionHasStarted($students, $hasRegistration, true);
    }

    public function electionHasStartedWithRegistration($students = [], $withCandidates = false)
    {
        return $this->electionHasStarted($students, true, $withCandidates);
    }

    public function electionHasNotStarted()
    {
        return $this->electionFactory()->state(['started_at' => null])->create();
    }

    public function electionHasEnded($type = '')
    {
        return $this->electionFactory()->state(['started_at' => now()])->hasEnded($type)->create();
    }
}
