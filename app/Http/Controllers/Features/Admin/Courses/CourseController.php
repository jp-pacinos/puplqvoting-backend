<?php

namespace App\Http\Controllers\Features\Admin\Courses;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Course $course)
    {
        return response()->json($course->orderBy('name')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Course $course)
    {
        $newCourse = $request->validate([
            'name' => 'nullable|string|max:255',
            'acronym' => 'required|string|max:60|unique:courses',
        ]);

        $course->create($newCourse);

        return response()->json(['message' => 'course-created'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        return response()->json($course, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        $newCourse = $request->validate([
            'name' => 'string|max:255',
            'acronym' => ['required', 'string', 'max:60', Rule::unique('courses')->ignore($course->id)],
        ]);

        $course->update($newCourse);

        return response()->json(['message' => 'course-updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(['message' => 'course-deleted']);
    }
}
