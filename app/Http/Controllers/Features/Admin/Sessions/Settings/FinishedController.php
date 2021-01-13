<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Settings;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FinishedController extends Controller
{
    public function store(Request $request, Session $session)
    {
        $validated = $request->validate([
            /**
             * 1 - completed
             * 2 - cancelled
             * 3 - not yet
             */
            'status' => 'required|numeric|in:1,2,3',
        ]);

        $fields = $this->resolve($validated['status']);

        $session->update($fields);

        return \response()->json([
            'message' => 'Election status updated.',
            'session' => $fields,
        ]);
    }

    private function resolve($type)
    {
        $fields = [
            'completed_at' => null,
            'cancelled_at' => null,
        ];

        if ($type == 1 || $type == 2) {
            $fields['active'] = 0;
            $fields['started_at'] = null;
            $fields['registration_at'] = null;
        }

        if ($type == 1) {
            $fields['completed_at'] = now();
            return $fields;
        }

        if ($type == 2) {
            $fields['cancelled_at'] = now();
            return $fields;
        }

        return $fields;
    }
}
