<?php

namespace App\Http\Controllers\Features\Admin\Students;

use App\Models\UserStudent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\StudentsFilter;
use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, UserStudent $userStudent, StudentsFilter $filter)
    {
        $parameters = $request->validate([
            'perpage' => 'nullable|numeric',
            'studentnumber' => 'nullable|string',
            'course' => 'nullable|numeric',
            'year' => 'nullable|numeric',  // not available
            'gender' => 'nullable|string', // MALE | FEMALE | null
            'voter' => 'nullable|numeric', // 1 = canvote | 0 = cantvote | null = all
        ]);

        $perPage = $parameters['perpage'] ?? 10;
        $perPage = ($perPage >= 10 && $perPage <= 500) ? $perPage : 10;

        $students = $filter->apply($userStudent->maintenanceDetails(), $parameters);
        $studentsCount = $students->count();

        $data = $students->orderBy('lastname')->simplePaginate($perPage)->toArray();
        $data['total'] = $studentsCount;

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, UserStudent $userStudent)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|min:15|max:20|unique:user_students',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'sex' => 'nullable|string|in:MALE,FEMALE',
            'birthdate' => 'required|date',
            'email' => 'required|email|max:255|unique:user_students',
            'can_vote' => 'required|boolean',
            'course_id' => 'required|numeric|exists:courses,id',
        ]);

        $student = $userStudent->create($validated);

        return response()->json(['message' => 'Created', 'data' => $student], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserStudent  $student
     * @return \Illuminate\Http\Response
     */
    public function show(UserStudent $student)
    {
        return response()->json($student);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserStudent  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserStudent $student)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'sex' => 'required|string|in:MALE,FEMALE',
            'birthdate' => 'required|date',
            'can_vote' => 'required|boolean',
            'course_id' => 'required|numeric|exists:courses,id',
            'email' => ['required', 'email', 'max:255', Rule::unique('user_students')->ignore($student->id)],
            'student_number' => [
                'required',
                'string',
                'min:15',
                'max:20',
                Rule::unique('user_students')->ignore($student->id),
            ],
        ]);

        $student->update($validated);

        return response()->json(['message' => 'Updated', 'data' => $student]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserStudent  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserStudent $student)
    {
        $result = $student->delete();

        return response()->json(['message' => 'Deleted', 'success' => $result]);
    }
}
