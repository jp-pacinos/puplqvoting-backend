<?php

namespace App\Http\Controllers\Features\Student\Voting;

use App\Models\Party;
use App\Models\Official;
use App\Http\Controllers\Controller;
use App\Services\StudentActiveSession;
use App\Http\Requests\ValidOfficials as ValidOfficialsRequest;

class VotingController extends Controller
{
    use Concerns\VotingResource, Concerns\VoteByType, Concerns\HasVoteResult;

    /**
     * @var \App\Models\Session $session
     */
    protected $session;

    public function __construct(StudentActiveSession $session)
    {
        $this->middleware('student.canvote');

        $this->session = $session->getInstance();
    }

    /**
     * Get all party, officials in active session.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Party $party, Official $official)
    {
        $candidates = $this->getOfficials($official, $this->session->id);

        // return response()->json([
        //     'positions' => $this->getPositions($position, $this->session->id),
        //     'parties' => $this->getParties($party, $this->session->id),
        //     'candidates' => $this->getOfficials($official, $this->session->id),
        // ]);

        return response()->json([
            'positions' => $this->getAvailablePositions($candidates),
            'parties' => $this->getParties($party, $this->session->id),
            'candidates' => $candidates,
        ]);
    }

    public function store(ValidOfficialsRequest $request)
    {
        $validated = $request->validated();
        $student = $request->user();

        $voteHistory = null;
        switch ($this->session->verification_type) {
            case 'open':
                $voteHistory = $this->voteByOpen($student, $validated, $this->session->id);
                break;

            case 'code':
                $voteHistory = $this->voteByCode($student, $validated, $this->session->id);
                break;

            case 'email':
                $voteHistory = $this->voteByEmail($student, $validated, $this->session->id);
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
