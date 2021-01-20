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

        $studentKeys = $session->studentKeys()->whereIn('student_id', $studentIds)->get();
        if ($studentKeys->count() > 0) {
            $studentKeys = $studentKeys->map(fn($student) => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'session_id' => $session->id,
                'confirmation_code' => $student->confirmation_code ?? CodeGenerator::make(),
            ]);
        }

        $deniedIds = $studentKeys->count() > 0 ? $studentKeys->pluck('student_id')->flip() : [];
        foreach ($studentIds as $id) {
            if (\is_numeric($deniedIds[$id] ?? null)) {
                continue;
            }

            $studentKeys->push([
                'id' => null,
                'student_id' => $id,
                'session_id' => $session->id,
                'confirmation_code' => CodeGenerator::make(),
            ]);
        }

        $session->studentKeys()->upsert($studentKeys->toArray(), ['id', 'student_id', 'session_id'], ['confirmation_code']);
        $studentKeys = $session->studentKeys()->whereIn('student_id', $studentIds)->get();

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

        $affectedRows = $session
            ->studentKeys()
            ->whereIn('student_id', $validated['studentIds'])
            ->when(
                $session->haveRegistration(),
                fn($q) => $q->update(['confirmation_code' => null]),
                fn($q) => $q->delete()
            );

        return response()->json([
            'message' => 'Keys deleted',
            'affectedCount' => $affectedRows,
        ]);
    }
}
