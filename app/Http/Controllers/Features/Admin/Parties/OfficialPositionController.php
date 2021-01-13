<?php

namespace App\Http\Controllers\Features\Admin\Parties;

use App\Models\Official;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OfficialPositionController extends Controller
{
    public function update(Request $request, Official $official)
    {
        $request->validate([
            'position_id' => 'required|numeric|exists:positions,id',
        ]);

        $official->update([
            'position_id' => $request->position_id,
        ]);

        return response()->json([
            'message' => 'Position updated',
        ]);
    }
}
