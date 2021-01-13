<?php

namespace Database\Seeders;

use App\Models\Party;
use App\Models\Course;
use App\Models\Session;
use App\Models\Official;
use App\Models\Position;
use App\Models\UserStudent;
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

        $this->courseSeeder();
        $this->studentSeeder();

        $this->partySeeder();
        $this->officialSeeder();
    }

    public function sessionSeeder()
    {
        $this->call(SessionSeeder::class);
    }

    public function courseSeeder()
    {
        $courses = Course::factory()->count(18)->make();
        Course::insertOrIgnore($courses->toArray());
    }

    public function studentSeeder()
    {
        echo 'Creating 2500+ students, this may take a while...'."\n";
        $students = UserStudent::factory()->count(2800)->make();

        UserStudent::insertOrIgnore($students->toArray());

        // we can't set the timestamps in the factory
        // manually updating the timestamps fix the problem
        UserStudent::chunkById(500, function ($students) {
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
}
