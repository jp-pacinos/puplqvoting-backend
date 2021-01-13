<?php

namespace App\Http\Controllers\Features\Student\Voting\Concerns;

use App\Notifications\VoteEmailVerification;

trait VoteByType
{
    /**
     * voteByOpen function
     *
     * @param \App\Models\UserStudent $student
     * @param array $validatedOfficials
     * @param int $sessionId
     *
     * @return \App\Models\StudentVoteHistory
     */
    private function voteByOpen($student, $validatedOfficials, int $sessionId)
    {
        $history = $student->voteHistories()->create(['session_id' => $sessionId, 'verified_at' => now()]);
        $officials = $this->formatOfficials($validatedOfficials, $history->id);
        $student->votes()->insert($officials);

        return $history;
    }

    /**
     * voteByCode function
     *
     * @param \App\Models\UserStudent $student
     * @param array $validatedOfficials
     * @param int $sessionId
     *
     * @return \App\Models\StudentVoteHistory
     */
    private function voteByCode($student, $validatedOfficials, int $sessionId)
    {
        $history = $student->voteHistories()->create(['session_id' => $sessionId]);
        $officials = $this->formatOfficials($validatedOfficials, $history->id);
        $student->votes()->insert($officials);

        return $history;
    }

    /**
     * voteByEmail function
     *
     * @param \App\Models\UserStudent $student
     * @param array $validatedOfficials
     * @param int $sessionId
     *
     * @return \App\Models\StudentVoteHistory
     */
    private function voteByEmail($student, $validatedOfficials, int $sessionId)
    {
        $history = $student->voteHistories()->create(['session_id' => $sessionId]);
        $officials = $this->formatOfficials($validatedOfficials, $history->id);

        $result = $student->votes()->insert($officials);
        if ($result) {
            $student->notify(new VoteEmailVerification($history->id));
        }

        return $history;
    }

    private function formatOfficials($validatedOfficials, int $historyId): array
    {
        $officials = [];
        foreach ($validatedOfficials as $officialGroup) {
            foreach ($officialGroup as $officialId) {
                $officials[] = ['history_id' => $historyId, 'official_id' => $officialId];
            }
        }
        return $officials;
    }
}
