<?php

namespace App\Http\Controllers\Features\Student\Reports;

use App\Models\Party;
use App\Models\Position;
use App\Models\StudentVoteHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Services\StudentActiveSession;

class VoteResultController extends Controller
{
    public function __construct()
    {
        $this->middleware('signed');
    }

    public function index(StudentVoteHistory $history)
    {
        $history->load([
            'student:id,student_number,firstname,lastname,middlename,suffix',
            'votes.official' => function ($query) {
                return $query
                    ->select(['officials.id', 'student_id', 'position_id', 'party_id'])
                    ->with('student:id,student_number,firstname,lastname,middlename,suffix');
            },
        ]);

        $data = [
            'student' => (object) [
                'fullname' => $this->fullname($history['student']),
                'student_number' => $history['student']['student_number'],
            ],
            'votes' => $this->formatVotes($history['votes']),
            'isVerified' => $history->verified_at != null,
            'created_at' => $history->created_at->format('F j, Y, D g:i a'),
        ];

        return app('dompdf.wrapper')
            ->loadView('reports.student-vote-results', $data)
            ->stream('Vote Results - '.$data['student']->student_number.'.pdf');
    }

    private function formatVotes($votes)
    {
        $positions = $this->getPositions();
        $parties = $this->getParties();

        $newFormat = [];

        foreach ($votes as $vote) {
            $position = $positions[$vote->official->position_id];
            $official = $vote->official->student;
            $party = $parties[$vote->official->party_id];

            $newFormat[$position->order]['position'] = $position->name;
            $newFormat[$position->order]['officials'][] = [
                'name' => $this->fullname((object) $official),
                'party' => $party->name,
            ];
        }

        return $newFormat;
    }

    private function fullname($student)
    {
        return $student->lastname.' '.$student->firstname.' '.$student->middlename ?? ''.$student->suffix ?? '';
    }

    private function getPositions()
    {
        return Cache::remember(
            'VoteResultController/getPositions()',
            config('cache.stored-expiry'),
            function () {
                return Position::select('id', 'name', 'order')
                    ->orderBy('order', 'asc')
                    ->get()
                    ->mapWithKeys(function ($position) {
                        return [$position->id => $position];
                    });
            }
        );
    }

    private function getParties()
    {
        $sessionId = (new StudentActiveSession())->id();

        return Cache::remember(
            'VoteResultController/getParties('.$sessionId.')',
            config('cache.stored-expiry'),
            function () use ($sessionId) {
                return Party::select('id', 'name')
                    ->where('session_id', '=', $sessionId)
                    ->get()
                    ->mapWithKeys(function ($party) {
                        return [$party->id => $party];
                    });
            }
        );
    }
}
