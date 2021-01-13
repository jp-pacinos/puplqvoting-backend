<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Stats;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentVoteStats extends Controller
{
    use Concerns\HasStudentVotes;

    public function index(Request $request, Session $session)
    {
        $parameters = $request->validate([
            'partyId' => 'nullable|numeric',
            'positionId' => 'nullable|numeric',
            'officialId' => 'nullable|numeric',
            'courseId' => 'nullable|nullable',
            'gender' => 'nullable|string|in:MALE,FEMALE',
        ]);

        $options = [
            'partyIds' => $parameters['partyId'] ?? null,
            'positionId' => $parameters['positionId'] ?? null,
            'officialId' => $parameters['officialId'] ?? null,
            'courseId' => $parameters['courseId'] ?? null,
            'gender' => $parameters['gender'] ?? null,
        ];

        return \response()->json(
            $this->studentVoteStats($session, $options)
        );
    }
}
