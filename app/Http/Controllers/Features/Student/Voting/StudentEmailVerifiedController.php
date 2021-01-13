<?php

namespace App\Http\Controllers\Features\Student\Voting;

use App\Models\UserStudent;
use App\Models\StudentVoteHistory;
use App\Http\Controllers\Controller;
use App\Notifications\VoteCompleted;

class StudentEmailVerifiedController extends Controller
{
    use Concerns\HasVoteResult, Concerns\HasToVerifyVote;

    public function __construct()
    {
        $this->middleware('signed');
    }

    /**
     * index function
     *
     * @param \App\Models\StudentVoteHistory $history
     *
     * @return void
     */
    public function index(StudentVoteHistory $history)
    {
        $resultLink = $this->getVoteResultLink($history->id);

        $student = UserStudent::select(['id', 'firstname'])->find($history->student_id);
        $student->tokens()->delete();

        $verifiedHistory = $this->firstOrCreateVerifiedHistory($history, function () use ($student, $resultLink) {
            $student->notify(new VoteCompleted($resultLink));
        });

        // check if the verified voteHistoryId ($verified->id) is equal to
        // current history->id, if not it means that the user is trying to
        // verify more votes because of some voting type, you are allowed to
        // vote (change vote) as long as it is not yet verified.
        if ($history->id != $verifiedHistory->id) {
            abort(403, 'Forbidden. You can only vote once.');
        }

        return view('vote-final', ['student' => $student, 'reportUrl' => $resultLink]);
    }
}
