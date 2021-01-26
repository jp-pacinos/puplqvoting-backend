<?php

namespace App\Http\Controllers\Auth\Student;

use App\Models\UserStudent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\StudentActiveSession;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('voting.open');
    }

    public function index(Request $request, UserStudent $userStudent, StudentActiveSession $activeSession)
    {
        $credentials = $request->validate([
            'student_number' => 'bail|required|string|max:20',
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'birthdate' => 'required|date',
        ]);

        $session = $activeSession->getInstance();
        $student = $userStudent->where($credentials)->first();

        // no student
        if ($student == null) {
            abort(401, 'The student information is not found.');
        }

        // but unable to vote
        if (! $student->canVote()) {
            abort(403, 'You\'re unable to vote.');
        }

        // can vote but not registered if have registration
        if ($session->haveRegistration()) {
            if (! $student->isRegistered($session->id)) {
                return \response()->json([
                    'message' => 'You\'re not registered in this election.',
                    'registration_url' => route('student.registration'),
                ], 403);
            }
        }

        // can vote but the vote is already verified
        if ($student->isVoteVerified($session->id)) {
            abort(403, 'You\'re already voted!');
        }

        // just authenticated
        return response()->json([
            'token' => $student->createToken('student-token')->plainTextToken,
        ]);
    }
}
