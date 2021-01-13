<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Stats;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StreamStats extends Controller
{
    use Concerns\HasBasicStats, Concerns\HasStudentVotes;

    public function index(Request $request, Session $session)
    {
        $parameters = $request->validate([
            'partyId' => 'nullable|numeric',
            'positionId' => 'nullable|numeric',
            'officialId' => 'nullable|numeric',
            'courseId' => 'nullable|nullable',
            'gender' => 'nullable|string|in:MALE,FEMALE',
        ]);

        $data = ['basic' => $this->basicStats($session)];

        if (\count($parameters) == 0) {
            $stats = $this->studentVoteStats($session);
            $data['votes'] = $stats;
            $data['summary'] = $stats; // plain results

            return \response()->json($data);
        }

        // if have filters
        $options = [
            'partyIds' => $parameters['partyId'] ?? null,
            'positionId' => $parameters['positionId'] ?? null,
            'officialId' => $parameters['officialId'] ?? null,
            'courseId' => $parameters['courseId'] ?? null,
            'gender' => $parameters['gender'] ?? null,
        ];

        $data['votes'] = $this->studentVoteStats($session, $options); // filtered
        $data['summary'] = $this->studentVoteStats($session);         // plain result

        return \response()->json($data);
    }
}
