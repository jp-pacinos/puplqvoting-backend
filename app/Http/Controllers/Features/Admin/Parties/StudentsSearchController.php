<?php

namespace App\Http\Controllers\Features\Admin\Parties;

use App\Models\UserStudent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class StudentsSearchController extends Controller
{
    public function index(Request $request, UserStudent $userStudent)
    {
        $parameters = $request->validate([
            'studentnumber' => 'nullable|string',
            'lastname' => 'nullable|string',
            'firstname' => 'nullable|string',
            'middlename' => 'nullable|string',
            'courseid' => 'nullable|numeric|exists:courses,id',
        ]);

        $students = $this->search($parameters, $userStudent->select([
            'id', 'student_number', 'lastname', 'firstname', 'middlename', 'suffix', 'sex', 'course_id',
        ]));

        $studentsCount = $students->count();

        $data = $students->orderBy('student_number', 'desc')->simplePaginate(5)->toArray();
        $data['total'] = $studentsCount;

        return response()->json($data);
    }

    private function search($parameters, Builder $userStudent)
    {
        $studentNumber = $parameters['studentnumber'] ?? false;
        $lastname = $parameters['lastname'] ?? false;
        $firstname = $parameters['firstname'] ?? false;
        $middlename = $parameters['middlename'] ?? false;
        $courseId = $parameters['courseid'] ?? false;

        $userStudent->when($studentNumber, function ($query) use ($studentNumber) {
            return $query->where('student_number', 'like', '%'.$studentNumber.'%');
        })
            ->when($lastname, function ($query) use ($lastname) {
                return $query->where('lastname', 'like', '%'.$lastname.'%');
            })
            ->when($firstname, function ($query) use ($firstname) {
                return $query->where('firstname', 'like', '%'.$firstname.'%');
            })
            ->when($middlename, function ($query) use ($middlename) {
                return $query->where('middlename', 'like', '%'.$middlename.'%');
            })
            ->when($courseId, function ($query) use ($courseId) {
                return $query->where('course_id', $courseId);
            });

        return $userStudent;
    }
}
