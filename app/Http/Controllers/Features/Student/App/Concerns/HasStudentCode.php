<?php

namespace App\Http\Controllers\Features\Student\App\Concerns;

use App\Models\Session;
use App\Models\StudentVoteKey;
use App\Services\CodeGenerator;

trait HasStudentCode
{
    /**
     * Get the code for this student. It will create one if dont have.
     *
     * @param int $studentId
     * @param int $sessionId
     *
     * @return App\Models\StudentVoteKey
     */
    private function getStudentCode($studentId, $sessionId)
    {
        $studentKey = StudentVoteKey::firstOrCreate([
            'session_id' => $sessionId,
            'student_id' => $studentId,
        ]);

        if (! $studentKey->confirmation_code) {
            $studentKey->confirmation_code = CodeGenerator::make(7);
            $studentKey->save();
        }

        return $studentKey;
    }
}
