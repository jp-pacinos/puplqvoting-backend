<?php

namespace App\Http\Controllers\Features\Admin\Sessions;

use App\Models\Session;
use App\Http\Controllers\Controller;

class SessionActiveController extends Controller
{
    public function index()
    {
        $session = (new Session())->getActive();

        if ($session == null) {
            \abort(404, 'No active election found.');
        }

        return response()->json($session);
    }
}
