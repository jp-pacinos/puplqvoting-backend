<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Settings;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegistrationController extends Controller
{
    public function store(Request $request, Session $session)
    {
        $validated = $request->validate([
            'type' => 'required|boolean',
        ]);

        $session->update([
            'registration' => $validated['type'],
            'registration_at' => null,
        ]);

        return \response()->json([
            'message' => 'Registration updated',
            'session' => [
                'registration' => $session->registration,
                'registration_at' => $session->registration_at,
            ],
        ]);
    }
}
