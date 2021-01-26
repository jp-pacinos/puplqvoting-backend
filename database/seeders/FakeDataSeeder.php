<?php

namespace Database\Seeders;

use App\Models\Party;
use App\Models\Course;
use App\Models\Session;
use App\Models\Official;
use App\Models\Position;
use App\Models\UserStudent;
use Illuminate\Support\Arr;
use App\Models\Registration;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Database\Seeders\SessionSeeder;

class FakeDataSeeder extends Seeder
{
    private $sessionIds = [];
    private $positionIds = [];
    private $partyIds = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->sessionSeeder();
        $this->sessionIds = Session::all()->pluck('id');
        $this->positionIds = Position::all()->mapWithKeys(function ($position) {
            return [$position->id => $position->per_party_count];
        });

        $this->courseSeeder(10);
        $this->studentSeeder(2500);

        $this->partySeeder();
        $this->officialSeeder();

        $this->simulateElections();
    }

    public function sessionSeeder()
    {
        $this->call(SessionSeeder::class);
    }

    public function courseSeeder($count)
    {
        $courses = Course::factory()->count($count)->make();
        Course::insertOrIgnore($courses->toArray());
    }

    public function studentSeeder($count)
    {
        echo 'Creating '.$count.'+ students, this may take a while...'."\n";
        $students = UserStudent::factory()->count($count)->make();

        UserStudent::insertOrIgnore($students->toArray());

        // we can't set the timestamps in the factory
        // manually updating the timestamps fix the problem
        UserStudent::chunkById(250, function ($students) {
            foreach ($students as $student) {
                $student->update([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        });
    }

    public function partySeeder($partyCount = 2)
    {
        $faker = \Faker\Factory::create();

        // if we have 2 sessions, expect we will have (2 * $partyCount) parties.
        foreach ($this->sessionIds as $sessionId) {
            for ($i = 0; $i < $partyCount; $i++) {
                $party = Party::create([
                    'name' => 'Party '.\rand(1000, 5000),
                    'description' => $faker->text,
                    'session_id' => $sessionId,
                ]);

                $this->partyIds[] = $party->id;
            }
        }
    }

    public function officialSeeder()
    {
        echo 'Generating officials, make sure you have enough students!'."\n";

        foreach ($this->partyIds as $partyId) {
            // every party has 6 positions/officials
            foreach ($this->positionIds as $positionId => $perPartyCount) {
                // if a position has many officials, we loop by $perPartyCount
                for ($i = 0; $i < $perPartyCount; $i++) {
                    $studentId = $this->getRandomStudentId();

                    Official::create([
                        'display_picture' => null,
                        'student_id' => $studentId,
                        'position_id' => $positionId,
                        'party_id' => $partyId,
                    ]);
                }
            }
        }
    }

    public function getRandomStudentId()
    {
        $id = UserStudent::inRandomOrder()->first()['id'];

        if (Official::where(['student_id' => $id])->first()) {
            $id = $this->getRandomStudentId();
        }

        return $id;
    }

    public function simulateElections($count = false)
    {
        $sessions = Session::orderBy('year', 'desc')->when(\is_numeric($count), fn($q) => $q->limit($count))->get();

        foreach ($sessions as $session) {
            $this->makeElection($session);
        }
    }

    public function makeElection(Session $session)
    {
        echo 'Starting election on '.$session->name.' Please wait. This is dummy data for testing, you can cancel this anytime'."\n";

        $officials = Official::whereIn('party_id', $session->parties->modelKeys())->get()->groupBy('position_id');
        $positions = Position::all();

        $studentCount = UserStudent::where('can_vote', '=', 1)->count();
        $participating = \rand(0, 100) >= 87 ? 1 : '0.'.\rand(60, 100);
        $toParticipateCount = (int) \ceil($studentCount * $participating);

        UserStudent::where('can_vote', '=', 1)
            ->inRandomOrder()
            ->limit($toParticipateCount)
            ->chunk(500, function ($students) use ($session, $officials, $positions) {
                $haveRegistration = $session->haveRegistration();

                foreach ($students as $student) {
                    if ($haveRegistration) {
                        Registration::create([
                            'session_id' => $session->id,
                            'student_id' => $student->id,
                        ]);
                    }

                    $this->doVote($student, $session->id, $officials, $positions);
                }
            });

        echo 'Election on '.$session->name.' has ended'."\n";
    }

    public function doVote(UserStudent $student, $sessionId, $officialLists, $positions)
    {
        $history = $student->voteHistories()->create([
            'session_id' => $sessionId, 'verified_at' => \rand(0, 100) > 97 ? null : now(),
        ]);

        $officials = [];
        foreach ($positions as $position) {
            $options = $officialLists[$position->id];
            $officialsPicked = Arr::random($options->toArray(), $position->choose_max_count);

            foreach ($officialsPicked as $official) {
                $officials[] = ['history_id' => $history->id, 'official_id' => $official['id']];
            }
        }

        $student->votes()->insert($officials);
    }
}
