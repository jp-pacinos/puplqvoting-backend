<?php

namespace Tests\Concerns;

use Error;
use App\Models\Party;
use App\Models\Course;
use App\Models\Session;
use App\Models\Official;
use App\Models\Position;
use Illuminate\Support\Str;
use App\Models\Registration;
use App\Models\StudentVoteKey;
use App\Models\StudentVoteHistory;
use Database\Seeders\PositionSeeder;
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

    /**
     * Destroy active election
     *
     * @return void
     * @throws \Error
     */
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
     * @param string $verificationType
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public function electionHasStarted(
        $students = ['voted' => [], 'registered' => [], 'keys' => []],
        $hasRegistration = false,
        $withCandidates = false,
        $verificationType = 'open'
    ) {
        $election = $this->electionFactory()
            ->state(['started_at' => now(), 'registration' => $hasRegistration])
            ->verificationTypeIs($verificationType)
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

        $keys = $students['keys'] ?? [];
        if (\count($keys) != 0) {
            foreach ($keys as $id) {
                StudentVoteKey::factory()
                    ->student($id)
                    ->session($election->id)
                    ->create();
            }
        }

        if ($withCandidates) {
            $this->seed(PositionSeeder::class);
            Course::factory()->count(10)->create();

            $positions = Position::all();

            Party::factory()
                ->count(\rand(2, 3))
                ->session($election->id)
                ->create()
                ->each(fn ($party) => $positions->each(
                    fn ($position) => Official::factory()
                        ->count($position->per_party_count)
                        ->position($position->id)
                        ->party($party->id)
                        ->create()
                ));
        }

        return $election;
    }

    /**
     * @param array $students
     * @param bool $hasRegistration
     * @param string $verificationType
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Error
     */
    public function electionHasStartedWithCandidates(
        $students = [],
        $hasRegistration = false,
        $verificationType = 'open'
    ) {
        return $this->electionHasStarted($students, $hasRegistration, true, $verificationType);
    }

    /**
     * @param array $students
     * @param bool $hasRegistration
     * @param string $verificationType
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Error
     */
    public function electionHasStartedWithRegistration(
        $students = [],
        $withCandidates = false,
        $verificationType = 'open'
    ) {
        return $this->electionHasStarted($students, true, $withCandidates, $verificationType);
    }

    /**
     * @param string $type
     * @param array $students
     * @param bool $hasRegistration
     * @param bool $withCandidates
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Error
     */
    public function electionHasStartedWithVerificationType(
        $type,
        $students = [],
        $hasRegistration = false,
        $withCandidates = false
    ) {
        return $this->electionHasStarted($students, $hasRegistration, $withCandidates, $type);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Error
     */
    public function electionHasNotStarted()
    {
        return $this->electionFactory()->state(['started_at' => null])->create();
    }

    /**
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     * @throws \Error
     */
    public function electionHasEnded($type = '')
    {
        return $this->electionFactory()->state(['started_at' => now()])->hasEnded($type)->create();
    }
}
