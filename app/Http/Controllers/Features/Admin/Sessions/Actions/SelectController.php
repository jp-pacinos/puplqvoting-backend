<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Actions;

use App\Models\Session;
use App\Http\Controllers\Controller;

class SelectController extends Controller
{
    public function store(Session $session)
    {
        $active = (new Session())->getActive();

        if (($active->id ?? false) == $session->id) {
            return \response()->json([
                'message' => 'Election is already selected.',
            ]);
        }

        if ($active != null) {
            \abort(403, 'Please unselect the current election.');
        }

        if ($session->isEnded()) {
            \abort(403, 'You cannot start an election that is already done.');
        }

        $session->update(['active' => 1]);

        return \response()->json([
            'message' => 'Election selected.',
        ], 201);
    }

    public function destroy(Session $session)
    {
        $session->update(['active' => 0]);

        return \response()->json([
            'message' => 'Election unselected.',
        ]);
    }
}
