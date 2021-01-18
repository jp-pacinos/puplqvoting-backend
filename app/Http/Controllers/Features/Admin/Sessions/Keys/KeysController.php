<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Keys;

use App\Models\Session;
use App\Models\UserStudent;
use Illuminate\Http\Request;
use App\Models\StudentVoteKey;
use App\Services\StudentsFilter;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Features\Student\App\Concerns\HasStudentCode;

class KeysController extends Controller
{
    use HasStudentCode;

    public function index(Request $request, Session $session, UserStudent $userStudent, StudentsFilter $filter)
    {
        $parameters = $request->validate([
            'perpage' => 'nullable|numeric',
            'studentnumber' => 'nullable|string',
            'course' => 'nullable|numeric',
            'year' => 'nullable|numeric',  // not available
            'gender' => 'nullable|string', // MALE | FEMALE | null
            'voter' => 'nullable|numeric', // 1 = canvote | 0 = cantvote | null = all
            'code' => 'nullable|numeric',  // 1 = have code | 2 = have no code | null = all
        ]);

        $perPage = $parameters['perpage'] ?? 10;
        $perPage = ($perPage >= 10 && $perPage <= 500) ? $perPage : 10;

        $query = $userStudent
            ->leftJoin('student_vote_keys', 'student_vote_keys.student_id', '=', 'user_students.id')
            ->where('student_vote_keys.session_id', '=', $session->id)
            ->when($session->haveRegistration(), function ($q) {
                return $q->join('registrations', 'registrations.student_id', '=', 'user_students.id');
            })
            ->when($parameters['code'] ?? null, function ($q) use ($parameters) {
                if ($parameters['code'] == 1) {
                    return $q->whereNotNull('student_vote_keys.confirmation_code');
                }
                return $q->whereNull('student_vote_keys.confirmation_code');
            })
            ->select([
                'student_vote_keys.id', 'student_vote_keys.confirmation_code',
                'student_number', 'firstname', 'lastname', 'middlename', 'suffix',
                'sex', 'can_vote', 'course_id',
            ]);

        $students = $filter->apply($query, $parameters);
        $studentsCount = $students->count();

        $data = $students->orderBy('lastname')->simplePaginate($perPage)->toArray();
        $data['total'] = $studentsCount;

        return response()->json($data);
    }

    public function store(Request $request, Session $session)
    {
        $request->validate([
            'studentid' => 'required|numeric|exists:user_students,id',
        ]);

        if ($session->haveRegistration()) {
            $count = $session->registrations()->where(['student_id' => $request->studentid])->count();
            if ($count == 0) {
                return \abort(403, 'Cannot create key. The student must be registered in this election.');
            }
        }

        return response()->json([
            'message' => 'Key generated',
            'data' => $this->getStudentCode($request->studentid, $session->id),
        ], 201);
    }

    public function destroy(Session $session, StudentVoteKey $studentKey)
    {
        $result = $studentKey->delete();

        return response()->json(['message' => 'Key deleted', 'success' => $result]);
    }
}
