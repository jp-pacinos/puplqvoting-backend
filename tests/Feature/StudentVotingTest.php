<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Position;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\StudentVoteHistory;
use App\Notifications\VoteCompleted;
use Tests\Concerns\InteractsWithStudent;
use App\Notifications\VoteEmailVerification;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\InteractsWithActiveElection;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * To Test:
 *
 * Cases for listing and submitting votes
 * - Students cannot see lists of candidates if not authorized - 401
 * - Students cannot submit votes if not authorized - 401
 * - Students cannot submit votes that is incomplete - 422
 * - Students can see lists of candidates and not empty - 200
 *
 * Cases for submitting votes based on verification types
 * > Api response json format { message: string, history_id: int, verification_type: vtype, resultLink: string } - 200
 * > Can vote again if vote is not verified - 201
 * > Check PDF if accessible - 200
 * > Cannot vote more than once when verified - 403
 * > Email verification receive verify vote notif and vote completed nofif
 * > Open and Code verification should not receive vote comleted notif
 * > When vote verified, the student cannot fetch or submit votes
 * -
 * - (open) : Can vote once
 * - (email) : Can vote many times (n?) unless the email is verified
 * - (code) : Can vote many times (n?) unless the code is entered
 *
 * Cases for Election
 * - (started) : Students cannot submit votes when election is not started - 403
 * - (ended) : Students cannot submit votes when election is ended - 403
 *
 * @covers App\Http\Controllers\Features\Student\Voting\VotingController
 * @covers App\Http\Controllers\Features\Student\Voting\StudentCodeSubmitController
 * @covers App\Http\Controllers\Features\Student\Voting\StudentEmailVerifiedController
 */
class StudentVotingTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithStudent;
    use InteractsWithActiveElection;

    protected $route = '/api/voting/now';

    protected $routeCode = '/api/voting/now/code';

    public function testStudentsCannotSeeListsOfCandidatesIfNotAuthorized()
    {
        $this->electionHasStarted();

        $response = $this->getJson($this->route);
        $response->assertUnauthorized();
    }

    public function testStudentsCannotSubmitVotesIfNotAuthorized()
    {
        $this->electionHasStarted();

        $response = $this->postJson($this->route, $fakeVoteData = []);
        $response->assertUnauthorized();
    }

    public function testStudentsCannotSubmitVotesThatIsIncomplete()
    {
        $this->electionHasStartedWithCandidates();

        $this->actingAsStudent();
        $response = $this->postJson($this->route, $data = []);
        $response->assertStatus(422);
    }

    public function testStudentsCanSeeListsOfCandidatesAndNotEmpty()
    {
        $election = $this->electionHasStartedWithCandidates();

        $counts = (object) Position::all()->reduce(function ($carry, $position) {
            $positionsCount = $carry['positions'] ?? 0;
            $candidatesCount = $carry['candidates'] ?? 0;
            return [
                'positions' => ++$positionsCount,
                'candidates' => $candidatesCount += $position->per_party_count,
            ];
        });
        $counts->parties = $election->parties()->count();
        $counts->candidates *= $counts->parties;

        $this->actingAsStudent();

        $response = $this->getJson($this->route);
        $response
            ->assertOk()
            ->assertJsonStructure([
                'positions', 'parties', 'candidates',
            ])
            ->assertJsonCount($counts->positions, 'positions')
            ->assertJsonCount($counts->parties, 'parties')
            ->assertJsonCount($counts->candidates, 'candidates');
    }

    public function testStudentsCompleteVotingProcessInOpenVerification()
    {
        $this->electionHasStartedWithVerificationType('open', [], false, true);
        $this->actingAsStudent();

        // Fetch candidates data, positions, parties
        $response = $this->getVotingResource();

        // JSON response
        $data = (object) $response->json();

        // we selected candidates and store to a variable for later use
        $selectedCandidates = $this->selectCandidates((array) $data);

        // Listen for notifications
        Notification::fake();

        // Submit votes and assert response
        $response = $this->submitVote($selectedCandidates, 'open');

        // in open verification, no notification is sent.
        Notification::assertNothingSent();

        // JSON response from submit votes
        $data = (object) $response->json();

        // The resultLink is the link to pdf file, we can only check if it has content type of application/pdf
        $this->assertVoteResultPDF($data->resultLink);

        // check if vote is verified
        $this->assertVoteHistoryIsVerified($data->history_id);

        /**
         * Now that we know that the vote is already submitted, in open verification
         * the vote is verified and the student can vote once.
         * The next thing we do is to try to vote again.
         */
        $this->assertStudentCannotVoteAgainIfAlreadyVerfied($selectedCandidates);
    }

    public function testStudentsCompleteVotingProcessInCodeVerification()
    {
        $this->actingAsStudent();

        $election = $this->electionHasStartedWithVerificationType(
            'code',
            ['keys' => [$this->actingStudent->id]],
            false, // we disable registration
            true   // generate candidates, positions, parties
        );

        // Fetch candidates data, positions, parties
        $response = $this->getVotingResource();

        // JSON response
        $data = (object) $response->json();

        // we selected candidates and store to a variable for later use
        $selectedCandidates = $this->selectCandidates((array) $data);

        // Submit votes and assert response
        $response = $this->submitVote($selectedCandidates, 'code');

        // JSON response of submitted votes
        $data = (object) $response->json();

        // The resultLink is the link to pdf file, we can only check if it has content type of application/pdf
        $this->assertVoteResultPDF($data->resultLink);

        // We need to see that the vote is not verified
        $history1 = $this->assertVoteHistoryIsNotVerified($data->history_id);

        /**
         * Vote for second time
         * We make sure the student can vote for the second time
         * unless he/she decide to verify the vote by entering the code.
         */

        // Fetch candidates data, positions, parties
        $response = $this->getVotingResource();

        // JSON response
        $data = (object) $response->json();

        // Submit votes and assert response
        $response = $this->submitVote($selectedCandidates, 'code');

        // JSON response of submitted votes
        $data = (object) $response->json();

        // The resultLink is the link to pdf file, we can only check if it has content type of application/pdf
        $this->assertVoteResultPDF($data->resultLink);

        // We need to see that the vote is not verified
        $history2 = $this->assertVoteHistoryIsNotVerified($data->history_id);

        // check if there are two records in student vote histories
        $count = StudentVoteHistory::where([
            'student_id' => $this->actingStudent->id,
            'session_id' => $election->id,
        ])->count();

        // the count is two because we only do two vote submit
        $this->assertSame($count, 2);

        /**
         * This time, verify the vote by submitting verification code.
         * But first submit empty data and next wrong code.
         * After submitting the second vote, try to submit the first vote using same code.
         */

        $verifyRoute = $this->routeCode . "/{$history2->id}/verify";

        // submit empty
        $this->postJson($verifyRoute)->assertJsonValidationErrors(['code']);

        // submit wrong code
        $this->postJson($verifyRoute, ['code' => 'wrong code'])->assertForbidden();

        Notification::fake();

        $code = $this->getStudentCode($this->actingStudent->id, $election);
        $response = $this->postJson($verifyRoute, ['code' => $code])
            ->assertCreated()
            ->assertJsonStructure(['message', 'resultLink']);

        Notification::assertNothingSent();

        $data = (object) $response->json();

        // check pdf and if votes is verified
        $this->assertVoteResultPDF($data->resultLink);
        $this->assertVoteHistoryIsVerified($history2->id); // see $history

        // submit second vote
        $verifyRoute2 = $this->routeCode . "/{$history1->id}/verify";
        $response = $this->postJson($verifyRoute2, ['code' => $code])->assertForbidden();
        $this->assertAlreadyVoted($response);

        /**
         * Final steps
         * Block the student from fetching and submitting votes
         */
        $this->assertStudentCannotVoteAgainIfAlreadyVerfied($selectedCandidates);
    }

    public function testStudentsCompleteVotingProcessInEmailVerification()
    {
        $this->electionHasStartedWithVerificationType(
            'email',
            [],
            false, // we disable registration
            true   // generate candidates, positions, parties
        );

        $this->actingAsStudent();

        // Fetch candidates data, positions, parties
        $response = $this->getVotingResource();

        // JSON response
        $data = (object) $response->json();

        // we selected candidates and store to a variable for later use
        $selectedCandidates = $this->selectCandidates((array) $data);

        // Listen for notifications
        Notification::fake();

        // Submit votes and assert response
        $response = $this->submitVote($selectedCandidates, 'email');

        // JSON response of submitted votes
        $data = (object) $response->json();

        $verifyLink1 = '';

        // The student must receive verify vote notif
        Notification::assertSentTo(
            $this->actingStudent,
            VoteEmailVerification::class,
            function ($notification, $channels, $notifiable) use ($data, &$verifyLink1) {
                $mail = $notification->toMail($notifiable)->toArray();
                $verifyLink1 = $mail['actionUrl'];
                return $notification->voteHistoryId == $data->history_id;
            }
        );

        // The resultLink is the link to pdf file, we can only check if it has content type of application/pdf
        $this->assertVoteResultPDF($data->resultLink);

        // We need to see that the vote is not verified
        $firstVote = $this->assertVoteHistoryIsNotVerified($data->history_id);

        /**
         * Vote for second time
         * This time verify the email neither from first vote or second vote use \rand(0, 100) > 50
         * and also try to verify the vote that is unselected to see if the system reject the request
         */

        // Fetch candidates data, positions, parties
        $response = $this->getVotingResource();

        // JSON response
        $data = (object) $response->json();

        // we selected candidates and store to a variable for later use
        $selectedCandidates = $this->selectCandidates((array) $data);

        // Listen for notifications
        Notification::fake();

        // Submit votes and assert response
        $response = $this->submitVote($selectedCandidates, 'email');

        // JSON response of submitted votes
        $data = (object) $response->json();

        $verifyLink2 = '';

        // The student must receive verify vote notif
        Notification::assertSentTo(
            $this->actingStudent,
            VoteEmailVerification::class,
            function ($notification, $channels, $notifiable) use ($data, &$verifyLink2) {
                $mail = $notification->toMail($notifiable)->toArray();
                $verifyLink2 = $mail['actionUrl'];
                return $notification->voteHistoryId == $data->history_id;
            }
        );

        // The resultLink is the link to pdf file, we can only check if it has content type of application/pdf
        $this->assertVoteResultPDF($data->resultLink);

        // We need to see that the vote is not verified
        $secondVote = $this->assertVoteHistoryIsNotVerified($data->history_id);

        /**
         * Verify the votes
         * using two variables: $verifyLink1 and $verifyLink2
         * When the first link is verified the second link will be deleted to the database
         */

        $first = \rand(0, 100) > 50 ? 1 : 2;
        $second = $first === 1 ? 2 : 1;

        Notification::fake();

        // The student visit the link 3 times
        for ($i = 0; $i < 3; ++$i) {
            $this
                ->get(${"verifyLink$first"})
                ->assertOk()
                ->assertViewIs('vote-final')
                ->assertViewHas(['student', 'reportUrl']);
        }

        // But we need to send one notification
        Notification::assertSentToTimes($this->actingStudent, VoteCompleted::class, 1);

        // Listen to notif
        Notification::fake();

        // The student try to verify the second vote
        for ($i = 0; $i < 3; ++$i) {
            $this->get(${"verifyLink$second"})->assertNotFound();
        }

        Notification::assertNothingSent();

        /**
         * Final steps
         * Block the students from fetching and submitting votes
         */
        $this->assertStudentCannotVoteAgainIfAlreadyVerfied($selectedCandidates);
    }

    public function testStudentsCannotSubmitVotesWhenElectionIsNotStarted()
    {
        $this->electionHasNotStarted();
        $this->actingAsStudent();

        $response = $this->postJson($this->route, []);
        $response->assertForbidden()->assertJsonStructure(['message']);

        if (!Str::containsAll($response->decodeResponseJson()['message'], ['not', 'started'])) {
            $this->markTestIncomplete(
                'The response message must contains "not", "started". Example: Election is not yet started.'
            );
        }
    }

    public function testStudentsCannotSubmitVotesWhenElectionIsEnded()
    {
        $this->electionHasEnded();
        $this->actingAsStudent();

        $response = $this->postJson($this->route, []);
        $response->assertForbidden()->assertJsonStructure(['message']);

        if (!Str::containsAll($response->decodeResponseJson()['message'], ['closed'])) {
            $this->markTestIncomplete('The response message must contains "closed". Example: Election is closed.');
        }
    }

    /**
     * @return \Illuminate\Testing\TestResponse
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \Throwable
     * @throws \PHPUnit\Framework\Exception
     */
    private function getVotingResource()
    {
        return $this->getJson($this->route)->assertOk()->assertJsonStructure([
            'positions', 'parties', 'candidates',
        ]);
    }

    /**
     * @param array $selectedCandidates
     * @param string $vtype
     * @return \Illuminate\Testing\TestResponse
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \Throwable
     * @throws \PHPUnit\Framework\Exception
     */
    private function submitVote($selectedCandidates, $vtype)
    {
        return $this->postJson($this->route, $selectedCandidates)
            ->assertCreated()
            ->assertJsonPath('verification_type', $vtype)
            ->assertJsonStructure([
                'message', 'history_id', 'verification_type', 'resultLink',
            ]);
    }

    /**
     * @param int $historyId
     * @return \App\Models\StudentVoteHistory
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    private function assertVoteHistoryIsVerified($historyId)
    {
        $history = StudentVoteHistory::find($historyId)->first();
        $this->assertNotEquals($history->verified_at, null);

        return $history;
    }

    /**
     * @param int $historyId
     * @return \App\Models\StudentVoteHistory
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    private function assertVoteHistoryIsNotVerified($historyId)
    {
        $history = StudentVoteHistory::find($historyId)->first();
        $this->assertEquals($history->verified_at, null);

        return $history;
    }

    /**
     * @param string $resultLink
     * @return \Illuminate\Testing\TestResponse
     * @throws \LogicException
     * @throws \Symfony\Component\HttpFoundation\Exception\BadRequestException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    private function assertVoteResultPDF($resultLink)
    {
        return $this->get($resultLink)->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    /**
     * @param array $selectedCandidates
     * @return void
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\IncompleteTestError
     */
    private function assertStudentCannotVoteAgainIfAlreadyVerfied($selectedCandidates)
    {
        // Fetch candidates data, positions, parties
        $response = $this->getJson($this->route)->assertForbidden();
        $this->assertAlreadyVoted($response);

        // Submit votes and assert response
        $response = $this->postJson($this->route, $selectedCandidates)->assertForbidden();
        $this->assertAlreadyVoted($response);
    }

    /**
     * @param int $studentId
     * @param \App\Models\Session $election
     * @return string
     */
    private function getStudentCode($studentId, $election)
    {
        return $election->studentKeys()->where('student_id', $studentId)->first()->confirmation_code;
    }

    private function selectCandidates($data): array
    {
        $positions = collect($data['positions'])->mapWithKeys(fn ($position) => [$position['order'] => $position]);
        $candidates = collect($data['candidates'])->mapToGroups(fn ($c) => [$c['position_id'] => $c]);
        $votes = [];

        foreach ($positions as $position) {
            $candidateIds = Arr::pluck($candidates[$position['id']], 'id');
            $randomIdsByPosition = Arr::shuffle(\array_slice($candidateIds, 0, $position['choose_max_count']));
            $votes[$position['keyName']] = $randomIdsByPosition;
        }

        return $votes;
    }

    private function assertAlreadyVoted($response)
    {
        if (!Str::containsAll($response->decodeResponseJson()['message'], ['already', 'voted'])) {
            $this->markTestIncomplete(
                'The response message must contains "already" and "voted". Example: You\'re already voted.'
            );
        }
    }
}
