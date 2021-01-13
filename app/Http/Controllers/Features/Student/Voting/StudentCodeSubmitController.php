<?php

namespace App\Http\Controllers\Features\Student\Voting;

use Illuminate\Http\Request;
use App\Models\StudentVoteHistory;
use App\Http\Controllers\Controller;

class StudentCodeSubmitController extends Controller
{
    use Concerns\HasVoteResult, Concerns\HasToVerifyVote;

    public function __construct()
    {
        $this->middleware('session.verification:code');

        $this->middleware('student.canvote');
    }

    public function store(Request $request, StudentVoteHistory $history)
    {
        $request->validate(['code' => 'required']);

        $student = $request->user();

        if (! $student->isValidConfirmationKey($request->code, $history->session->id)) {
            abort(403, 'The confirmation code is invalid.');
        }

        $student->tokens()->delete();

        $resultLink = $this->getVoteResultLink($history->id);

        $verifiedHistory = $this->firstOrCreateVerifiedHistory(
            $history
            // function () use ($student, $resultLink) { // 'code' verification is typically use offine
            // $student->notify(new VoteCompleted($resultLink));
            // }
        );

        if ($history->id != $verifiedHistory->id) {
            abort(403, 'You cannot verify a vote more than once.');
        }

        return response()->json([
            'message' => 'Vote completed',
            'resultLink' => $resultLink,
        ], 201);
    }
}
