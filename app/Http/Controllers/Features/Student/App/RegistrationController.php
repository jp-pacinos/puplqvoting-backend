<?php

namespace App\Http\Controllers\Features\Student\App;

use App\Models\UserStudent;
use App\Models\Registration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\StudentActiveSession;

class RegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('registration.open');
    }

    public function index()
    {
        return view('student.registration');
    }

    public function store(Request $request, UserStudent $userStudent, StudentActiveSession $activeSession)
    {
        $validated = $request->validate([
            'student_number' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'birthdate' => 'required|date',
        ]);

        $student = $userStudent->where($validated)->first();

        if ($student == null) {
            return redirect()
                ->route('student.registration')
                ->withErrors(['errorMessage' => 'The student information is not found.']);
        }

        if (! $student->canVote()) {
            return redirect()
                ->route('student.registration')
                ->withErrors(['errorMessage' => 'You\'re unable to vote.']);
        }

        if ($student->isRegistered($activeSession->id())) {
            return redirect()
                ->route('student.registration')
                ->withErrors(['errorMessage' => 'You\'re already registered.']);
        }

        // register the student
        Registration::create([
            'student_id' => $student->id,
            'session_id' => $activeSession->id(),
        ]);

        return redirect()->route('student.registration')->withInput(['registered' => true]);
    }
}
