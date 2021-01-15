<?php

namespace App\Http\Controllers\Features\Student\App;

use App\Models\StudentVoteKey;
use App\Http\Controllers\Controller;

class RegistrationShowCodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('signed');
    }

    public function index(StudentVoteKey $code)
    {
        $name = $this->fullname(
            $code->student()->select(['lastname', 'firstname', 'middlename', 'suffix'])->first()
        );

        return view('student.registration-code', [
            'studentName' => $name,
            'confirmationCode' => $code->confirmation_code,
        ]);
    }

    private function fullname($student)
    {
        return $student->lastname.' '.$student->firstname.' '.$student->middlename ?? ''.$student->suffix ?? '';
    }
}
