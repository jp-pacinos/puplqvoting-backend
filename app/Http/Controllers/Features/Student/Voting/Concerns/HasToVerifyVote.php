<?php

namespace App\Http\Controllers\Features\Student\Voting\Concerns;

use App\Models\StudentVoteHistory;

trait HasToVerifyVote
{
    /**
     * firstOrCreateVerifiedHistory function
     *
     * @param \App\Models\StudentVoteHistory $history
     * @param callable $firstVerified
     *
     * @return \App\Models\StudentVoteHistory
     */
    private function firstOrCreateVerifiedHistory(StudentVoteHistory $history, callable $firstTimeVerified = null)
    {
        $verified = StudentVoteHistory::select(['id'])->where([
            'session_id' => $history->session_id,
            'student_id' => $history->student_id,
        ])
            ->whereNotNull('verified_at')
            ->first();

        if ($verified != null) {
            return $verified;
        }

        $this->makeVerifed($history);

        if (\is_callable($firstTimeVerified)) {
            $firstTimeVerified($history);
        }

        return $history;
    }

    private function makeVerifed(StudentVoteHistory $history)
    {
        $history->update(['verified_at' => now()]);

        StudentVoteHistory::whereNull('verified_at')
            ->where(['student_id' => $history->student_id, 'session_id' => $history->session_id])
            ->delete();
    }
}
