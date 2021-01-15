<?php

namespace App\Http\Controllers\Features\Student\App;

use App\Models\UserStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Services\StudentActiveSession;

class RegistrationController extends Controller
{
    use Concerns\HasRegistration, Concerns\HasStudentCode;

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

        $session = $activeSession->getInstance();
        $isRegistered = $student->isRegistered($session->id);
        $isVerifyByCode = $session->verification_type == 'code';

        if ($isRegistered && $isVerifyByCode) {
            return $this->redirectToCodePage($student->id, $session->id);
        }

        if ($isRegistered) {
            return redirect()
                ->route('student.registration')
                ->withErrors(['errorMessage' => 'You\'re already registered.']);
        }

        $this->register($student->id, $session->id);

        if ($isVerifyByCode) {
            return $this->redirectToCodePage($student->id, $session->id);
        }

        return redirect()->route('student.registration')->withInput(['registered' => true]);
    }

    private function redirectToCodePage($studentId, $sessionId)
    {
        $key = $this->getStudentCode($studentId, $sessionId);

        return redirect(
            URL::temporarySignedRoute('student.registration.code', now()->addMinutes(15), ['code' => $key->id])
        );
    }
}
