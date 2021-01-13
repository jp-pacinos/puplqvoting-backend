<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Stats\Concerns;

use App\Models\Session;
use App\Models\StudentVote;
use Illuminate\Support\Facades\DB;

trait HasStudentVotes
{
    /**
     * @param \App\Models\Session $session
     * @param array $options
     * @return mixed
     */
    protected function detailedStudentVoteStats(Session $session, array $options = [])
    {
        return $this->baseStudentVoteStats($session, true, $options);
    }

    /**
     * @param \App\Models\Session $session
     * @param array $options
     * @return mixed
     */
    protected function studentVoteStats(Session $session, array $options = [])
    {
        return $this->baseStudentVoteStats($session, false, $options);
    }

    /**
     * @param int $sessionId
     * @param bool $detailed include officials names
     * @param array $options
     * @return mixed
     */
    protected function baseStudentVoteStats(Session $session, bool $detailed, array $options = [])
    {
        $partyIds = $options['partyIds'] ?? $session->parties->modelKeys();

        $studentVotes = StudentVote::select([
            'student_votes.*',
            'student_vote_histories.session_id',
            'user_students.course_id',
            'user_students.sex',
        ])
            ->join('student_vote_histories', 'student_vote_histories.id', '=', 'student_votes.history_id')
            ->join('user_students', 'user_students.id', '=', 'student_vote_histories.student_id')
            ->where('student_vote_histories.session_id', '=', $session->id)
            ->whereNotNull('student_vote_histories.verified_at');

        return DB::query()->select([
            'officials.*',
            DB::raw('COUNT(`student_votes`.`id`) AS `votes`'),
        ])
            ->fromSub(function ($query) use ($detailed) {
                $query
                    ->select(['officials.id', 'officials.position_id', 'officials.student_id', 'officials.party_id'])
                    ->from('officials')
                    ->when($detailed, function ($q) {
                        return $q->join('user_students', 'user_students.id', '=', 'officials.student_id')->addSelect([
                            'user_students.lastname',
                            'user_students.firstname',
                            'user_students.middlename',
                            'user_students.suffix',
                        ]);
                    });
            }, 'officials')
            ->leftJoinSub($studentVotes, 'student_votes', function ($join) {
                $join->on('student_votes.official_id', '=', 'officials.id');
            })
            ->when(
                \is_array($partyIds),
                function ($query) use ($partyIds) {
                    return $query->whereIn('officials.party_id', $partyIds);
                },
                function ($query) use ($partyIds) {
                    return $query->where('officials.party_id', '=', $partyIds);
                }
            )
            ->when($options['positionId'] ?? null, function ($query) use ($options) {
                return $query->where('officials.position_id', '=', $options['positionId']);
            })
            ->when($options['officialId'] ?? null, function ($query) use ($options) {
                return $query->where('officials.id', '=', $options['officialId']);
            })
            ->when($options['courseId'] ?? null, function ($query) use ($options) {
                return $query->where('student_votes.course_id', '=', $options['courseId']);
            })
            ->when($options['gender'] ?? null, function ($query) use ($options) {
                return $query->where('student_votes.sex', '=', $options['gender']);
            })
            ->groupBy('officials.id')
            ->orderBy('officials.position_id')
            ->orderBy('officials.party_id')
            ->get();
    }
}
