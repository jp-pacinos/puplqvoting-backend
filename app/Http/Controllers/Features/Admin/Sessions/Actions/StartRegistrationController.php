<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Actions;

use App\Models\Session;
use App\Http\Controllers\Controller;

class StartRegistrationController extends Controller
{
    public function store(Session $session)
    {
        $active = (new Session())->getActive();

        $isActiveSeesion = $session->id == ($active->id ?? false);
        if (! $isActiveSeesion) {
            \abort(403, 'Please unselect the current election.');
        }

        if ($session->isEnded()) {
            \abort(403, 'You cannot start a registration if the election is already done.');
        }

        $session->registration_at = \now();
        $session->save();

        return \response()->json([
            'message' => 'Registration started.',
            'registration_at' => $session->registration_at,
        ]);
    }

    public function destroy(Session $session)
    {
        $session->registration_at = null;
        $session->save();

        return \response()->json([
            'message' => 'Registration stopped.',
            'registration_at' => $session->registration_at,
        ]);
    }
}
