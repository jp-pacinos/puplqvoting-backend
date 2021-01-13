<?php

namespace App\Http\Controllers\Features\Admin\Sessions;

use App\Models\Party;
use App\Models\Session;
use App\Models\Official;
use App\Http\Controllers\Controller;

class ElectionController extends Controller
{
    use Stats\Concerns\HasBasicStats, Stats\Concerns\HasStudentVotes;

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function index(Session $session, Party $party, Official $official)
    {
        $parties = $party->select(['id', 'name'])
            ->where('session_id', '=', $session->id)
            ->orderBy('name')
            ->get();

        $partyIds = $parties->modelKeys();

        $officials = $official->select(['id', 'party_id', 'position_id', 'student_id'])
            ->with('student:id,lastname,firstname,middlename,suffix')
            ->whereIn('party_id', $partyIds)
            ->get();

        return response()->json([
            'election' => $session,
            'parties' => $parties,
            'officials' => $officials,
            'stats' => [
                'basic' => $this->basicStats($session),
                'votes' => $this->studentVoteStats($session, ['partyIds' => $partyIds]),
            ],
        ]);
    }
}
