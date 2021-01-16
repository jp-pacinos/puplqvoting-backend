<?php

namespace App\Http\Controllers\Features\Admin\Parties;

use App\Models\Party;
use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PartyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Party $party)
    {
        $parameters = $request->validate(['s' => 'nullable|string', 'session' => 'nullable|numeric']);

        $search = $parameters['s'] ?? false;
        $sessionId = $parameters['session'] ?? false;

        $filteredParties = $party
            ->select('parties.*')
            ->join('sessions', 'sessions.id', '=', 'parties.session_id')
            ->when(
                $search,
                function ($query) use ($search) {
                    return $query->where('parties.name', 'like', '%'.$search.'%');
                }
            )
            ->when(
                $sessionId,
                function ($query) use ($sessionId) {
                    return $query->where('parties.session_id', $sessionId);
                }
            )
            ->orderBy('sessions.year', 'desc')
            ->orderBy('parties.name')
            ->latest();

        $partiesCount = $filteredParties->count();

        $data = $filteredParties->simplePaginate(12)->toArray();
        $data['total'] = $partiesCount;

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Party $party, Session $session)
    {
        $data = $request->validate([
            'name' => 'required|string|min:4|max:255',
            'description' => 'nullable|string',
            'session_id' => 'required|exists:sessions,id',
        ]);

        if ($session->find($data['session_id'])->isEnded()) {
            abort(403, 'You cannot add a party is this election because it is already finished.');
        }

        $newParty = $party->create($data);

        return response()->json([
            'message' => 'Party created.',
            'party' => $newParty,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Party  $party
     * @return \Illuminate\Http\Response
     */
    public function show(Party $party)
    {
        $party->load('officials.student:id,course_id,student_number,lastname,firstname,middlename,suffix');

        return response()->json($party);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Party  $party
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Party $party, Session $session)
    {
        $data = $request->validate([
            'name' => 'required|string|min:4|max:255',
            'description' => 'nullable|string',
            'session_id' => 'required|exists:sessions,id',
        ]);

        $isTransferring = $request->session_id != $party->session_id;

        if ($isTransferring && $party->session->isEnded()) {
            abort(403, 'You cannot transfer this party if the election is finished.');
        }

        if ($isTransferring && $session->find($request->session_id)->isEnded()) {
            abort(403, 'You cannot transfer this party to that election. It is already finished.');
        }

        $status = $party->update($data);

        return response()->json([
            'message' => 'Party updated.',
            'success' => $status,
            'party' => $party,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Party  $party
     * @return \Illuminate\Http\Response
     */
    public function destroy(Party $party)
    {
        if ($party->session->isEnded()) {
            abort(403, 'You cannot remove this party. The election is finished.');
        }

        $status = $party->delete();

        return response()->json([
            'message' => 'Party deleted.',
            'success' => $status,
        ]);
    }
}
