<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Keys;

use App\Models\Session;
use Illuminate\Http\Request;
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

        if ($session->haveRegistration()) {
            $studentsCount = $session->registrations()
                ->whereIn('student_id', $studentIds)
                ->count();

            if ($studentsCount != \count($studentIds)) {
                return \abort(403, 'Cannot create key. These students must be registered in this election.');
            }
        }

        $studentKeys = $session->studentKeys()->whereIn('student_id', $studentIds)
            ->whereNotNull('confirmation_code')
            ->get();

        $deniedIds = $studentKeys->pluck('student_id')->flip();

        $newkeys = [];
        $newStudentIds = [];
        foreach ($studentIds as $id) {
            if (\is_numeric($deniedIds[$id] ?? null)) {
                continue;
            }

            $newkeys[] = [
                'student_id' => $id,
                'session_id' => $session->id,
                'confirmation_code' => CodeGenerator::make(),
                'created_at' => \now(),
                'updated_at' => \now(),
            ];

            $newStudentIds[] = $id;
        }

        if (\count($newkeys) != 0) {
            $session->studentKeys()->insert($newkeys);
            $studentKeys = [
                ...$studentKeys,
                ...$session->studentKeys()->whereIn('student_id', $newStudentIds)->get(),
            ];
        }

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
