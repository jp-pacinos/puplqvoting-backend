<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Keys;

use App\Models\Session;
use Illuminate\Http\Request;
use App\Models\StudentVoteKey;
use App\Services\CodeGenerator;
use App\Http\Controllers\Controller;

class KeysGroupController extends Controller
{
    public function update(Request $request, Session $session)
    {
        $validated = $request->validate([
            'studentIds' => 'required|array',
            'studentIds.*' => 'required|numeric', // don't check every id, creates n+1
        ]);

        $studentIds = $validated['studentIds'];

        /**
         * if the election have registration and the count of students with no confirmation_key
         * not matched to count of $request->studentIds, then abort
         * It insure that we are giving new keys
         */
        $studentsCount = 0;

        if ($session->haveRegistration()) {
            $studentsCount = $session->registrations()
                ->whereIn('student_id', $studentIds)
                ->whereNull('confirmation_code')
                ->count();
        } else {
            $studentsCount = StudentVoteKey::where('session_id', '=', $session->id)
                ->whereIn('student_id', $studentIds)
                ->whereNull('confirmation_code')
                ->count();
        }

        if ($studentsCount != \count($studentIds)) {
            return \abort(403, 'Cannot create key. The student must be registered in this election.');
        }

        $studentKeys = StudentVoteKey::createMany(
            \collect($studentIds)->map(function ($id) use ($session) {
                return [
                    'session_id' => $session->id,
                    'student_id' => $id,
                    'confirmation_key' => CodeGenerator::make(),
                ];
            })
        );

        return response()->json([
            'message' => 'Keys generated',
            'data' => $studentKeys,
        ]);
    }

    public function destroy(Request $request, Session $session)
    {
        $validated = $request->validate([
            'studentIds' => 'required|array',
            'studentIds.*' => 'required|numeric', // don't check every id, creates n+1
        ]);

        $affectedRows = $session->studentKeys()->whereIn('student_id', $validated['studentIds'])->delete();

        return response()->json([
            'message' => 'Keys deleted',
            'affectedCount' => $affectedRows,
        ]);
    }
}
