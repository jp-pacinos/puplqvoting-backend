<?php

namespace App\Http\Controllers\Features\Student\Voting;

use App\Models\Party;
use App\Models\Official;
use App\Http\Controllers\Controller;
use App\Services\StudentActiveSession;
use App\Http\Requests\ValidOfficials as ValidOfficialsRequest;

class VotingController extends Controller
{
    use Concerns\VotingResource;
    use Concerns\VoteByType;
    use Concerns\HasVoteResult;

    public function __construct()
    {
        $this->middleware('student.canvote');
    }

    /**
     * Get all party, officials in active session.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Party $party, Official $official, StudentActiveSession $activeSession)
    {
        $session = $activeSession->getInstance();
        $candidates = $this->getOfficials($official, $session->id);

        // return response()->json([
        //     'positions' => $this->getPositions($position, $this->session->id),
        //     'parties' => $this->getParties($party, $this->session->id),
        //     'candidates' => $this->getOfficials($official, $this->session->id),
        // ]);

        return response()->json([
            'positions' => $this->getAvailablePositions($candidates),
            'parties' => $this->getParties($party, $session->id),
            'candidates' => $candidates,
        ]);
    }

    public function store(ValidOfficialsRequest $request, StudentActiveSession $activeSession)
    {
        $session = $activeSession->getInstance();
        $validated = $request->validated();
        $student = $request->user();

        $voteHistory = null;
        switch ($this->session->verification_type) {
            case 'open':
                $voteHistory = $this->voteByOpen($student, $validated, $session->id);
                break;

            case 'code':
                $voteHistory = $this->voteByCode($student, $validated, $session->id);
                break;

            case 'email':
                $voteHistory = $this->voteByEmail($student, $validated, $session->id);
                break;
        }

        $data = [
            'message' => 'Created successfully',
            'history_id' => $voteHistory->id,
            'verification_type' => $this->session->verification_type,
            'resultLink' => $this->getVoteResultLink($voteHistory->id),
        ];

        return response()->json($data, 201);
    }
}
