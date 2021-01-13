<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Settings;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VerificationTypeController extends Controller
{
    public function store(Request $request, Session $session)
    {
        $request->validate(['type' => 'required|in:open,code,email']);

        $session->update(['verification_type' => $request->type]);

        return \response()->json([
            'message' => 'Verification type updated.',
            'verification_type' => $session->verification_type,
        ]);
    }
}
