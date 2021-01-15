<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Actions;

use App\Models\Session;
use App\Http\Controllers\Controller;
use App\Services\StudentActiveSession;

class StartElectionController extends Controller
{
    public function store(Session $session)
    {
        $active = (new Session())->getActive();

        $isActiveSeesion = $session->id == ($active->id ?? false);
        if (! $isActiveSeesion) {
            \abort(403, 'Please unselect the current election.');
        }

        if ($session->isEnded()) {
            \abort(403, 'You cannot start a election that is already done.');
        }

        $session->update(['started_at' => now()]);

        StudentActiveSession::setActive($session);

        return \response()->json([
            'message' => 'Election started.',
            'started_at' => $session->started_at,
        ]);
    }

    public function destroy(Session $session)
    {
        $session->update(['started_at' => null]);

        return \response()->json([
            'message' => 'Election stopped.',
            'started_at' => $session->started_at,
        ]);
    }
}
