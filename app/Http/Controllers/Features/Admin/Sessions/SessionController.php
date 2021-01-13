<?php

namespace App\Http\Controllers\Features\Admin\Sessions;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Session $session)
    {
        $request->validate(['search' => 'string']);

        $filteredSessions = $session->when($request->search, function ($query) use ($request) {
            return $query->where('name', 'LIKE', '%'.$request->search.'%');
        });

        $sessionsCount = $filteredSessions->count();

        $data = $filteredSessions->orderBy('year', 'desc')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(7)
            ->toArray();

        $data['total'] = $sessionsCount;

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Session $session)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'year' => 'required|numeric|digits:4',
            'registration' => 'required|boolean',
            'verification_type' => 'required|in:open,code,email',
            'description' => 'nullable|string',
        ]);

        $newSession = $session->create($data);

        return response()->json([
            'message' => 'Session created.',
            'session' => $newSession->refresh(),
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function show(Session $session)
    {
        return response()->json($session);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Session $session)
    {
        /**
         * see: App\Http\Controllers\Features\Admin\Sessions\Settings
         * see: App\Http\Controllers\Features\Admin\Sessions\Actions
         */

        // $data = $request->validate([
        //     'name' => 'required|string',
        //     'year' => 'required|numeric|digits:4o',
        //     'active' => 'required|boolean',
        //     'closed' => 'required|boolean',
        //     'description' => 'nullable|string',
        // ]);

        // $status = $session->update($data);

        // return response()->json([
        //     'message' => 'Session updated.',
        //     'success' => $status,
        // ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Session $session)
    {
        $request->validate([
            'confirmation' => 'required',
        ]);

        if (\strtolower($session->name) !== \strtolower($request->confirmation)) {
            \abort(403, 'You cannot delete this election');
        }

        $status = $session->delete();

        return response()->json([
            'message' => 'Session deleted.',
            'success' => $status,
        ]);
    }
}
