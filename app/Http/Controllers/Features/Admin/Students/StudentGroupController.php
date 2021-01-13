<?php

namespace App\Http\Controllers\Features\Admin\Students;

use App\Models\UserStudent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Group update and delete of student records in admin site
 */
class StudentGroupController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserStudent $students)
    {
        $validated = $request->validate([
            'studentIds' => 'required|array',
            'studentIds.*' => 'required|numeric', // don't check every id, creates n+1
            'can_vote' => 'nullable|boolean',
            'sex' => 'nullable|string|in:MALE,FEMALE',
            'course_id' => 'nullable|numeric|exists:courses,id',
        ]);

        $studentIds = $validated['studentIds'];
        $attributes = [];

        if (\is_numeric($validated['can_vote'] ?? false)) {
            $attributes['can_vote'] = $validated['can_vote'];
        }

        if ($validated['sex'] ?? false) {
            $attributes['sex'] = $validated['sex'];
        }

        if ($validated['course_id'] ?? false) {
            $attributes['course_id'] = $validated['course_id'];
        }

        $updatedCount = $students->whereIn('id', $studentIds)->update($attributes);

        return response()->json(['message' => 'Updated', 'updatedCount' => $updatedCount]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, UserStudent $students)
    {
        $validated = $request->validate([
            'studentIds' => 'required|array',
            'studentIds.*' => 'required|numeric', // don't check every id, creates n+1
        ]);

        $studentIds = $validated['studentIds'];

        $deletedCount = $students->whereIn('id', $studentIds)->delete();

        return response()->json(['message' => 'Deleted', 'deletedCount' => $deletedCount]);
    }
}
