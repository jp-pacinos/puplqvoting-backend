<?php

namespace App\Http\Controllers\Features\Student\App\Concerns;

use App\Models\Registration;

trait HasRegistration
{
    /**
     * register the student to this election
     *
     * @param int $studentId
     * @param int $sessionId
     * @return App\Models\Registration
     */
    private function register($studentId, $sessionId)
    {
        return Registration::create([
            'student_id' => $studentId,
            'session_id' => $sessionId,
        ]);
    }
}
