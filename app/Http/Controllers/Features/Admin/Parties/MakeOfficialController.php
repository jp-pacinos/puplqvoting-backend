<?php

namespace App\Http\Controllers\Features\Admin\Parties;

use App\Models\Party;
use App\Models\Official;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class MakeOfficialController extends Controller
{
    public function store(Request $request, Party $party)
    {
        $request->validate([
            'student_id' => 'required|numeric|exists:user_students,id',
        ]);

        $this->validateElectionNotEnded($party);

        // dahil foreign key ang position_id sa officials table
        // hindi pweding mag assign ng null value, except kung mag assign nalang ng value
        $openPositionId = $this->getOpenPositionId($party);

        if (! $openPositionId) {
            return response()->json([
                'message' => 'The Party is already full.',
                'errors' => ['student_id' => 'There is no more slots for this student.'],
            ], 422);
        }

        $official = $party->officials()->create([
            'display_picture' => null,
            'student_id' => $request->student_id,
            'position_id' => $openPositionId,
        ]);

        return response()->json([
            'message' => 'Student added!',
            'official' => $official,
        ], 201);
    }

    public function destroy(Party $party, Official $official)
    {
        if ($official->party_id != $party->id) {
            abort(403, 'The official you want to remove may not exist or from other party.');
        }

        $this->validateElectionNotEnded($party);

        if ($official->getRawOriginal('display_picture')) {
            Storage::disk('public')->delete($official->getRawOriginal('display_picture'));
        }

        $status = $official->delete();

        return response()->json([
            'message' => 'Official removed.',
            'success' => $status,
        ]);
    }

    private function getOpenPositionId(Party $party)
    {
        $selected = $party->officials->countBy('position_id');
        $requiredPositions = $this->requiredNumberByPositions();

        $openPositionId = null;
        foreach ($requiredPositions as $id => $perPartyCount) {
            if (($selected[$id] ?? false) && ($selected[$id] >= $perPartyCount)) {
                continue;
            }
            $openPositionId = $id;
            break;
        }

        return $openPositionId;
    }

    private function requiredNumberByPositions()
    {
        return Position::select(['id', 'per_party_count'])
            ->orderBy('order', 'asc')
            ->get()
            ->mapWithKeys(function ($position) {
                return [$position->id => $position->per_party_count];
            });
    }

    private function validateElectionNotEnded($party)
    {
        if ($party->session->isOpen()) {
            $message = 'Please stop the election first. Please be aware that it make breaking changes.';
            \abort(403, $message);
        }

        if ($party->session->isRegistrationOpen()) {
            $message = 'Please stop the registration first. Please be aware that it make breaking changes.';
            \abort(403, $message);
        }

        if ($party->session->isEnded()) {
            $message = 'You cannot add/remove officials if the election is ended. It will cause breaking changes.';
            \abort(403, $message);
        }
    }
}
