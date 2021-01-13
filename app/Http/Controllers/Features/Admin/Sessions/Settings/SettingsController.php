<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Settings;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index(Session $session)
    {
        return \response()->json($session);
    }

    public function update(Request $request, Session $session)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'year' => 'required|numeric|digits:4',
            'description' => 'nullable|string',
        ]);

        $status = $session->update($data);

        return response()->json([
            'message' => 'Election details updated.',
            'success' => $status,
        ]);
    }
}
