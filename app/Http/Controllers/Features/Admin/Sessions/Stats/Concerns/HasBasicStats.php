<?php

namespace App\Http\Controllers\Features\Admin\Sessions\Stats\Concerns;

use Exception;
use App\Models\Session;
use App\Models\UserStudent;

trait HasBasicStats
{
    protected function basicStats(Session $session)
    {
        $votedCount = $session->studentVoteHistories()->whereNotNull('verified_at')->count();

        $registeredCount = $session->haveRegistration()
            ? $session->registrations()->count()
            : UserStudent::select('id')->where('can_vote', '=', 1)->count();

        return [
            'votedCount' => $votedCount,
            'registeredCount' => $registeredCount,
            'notVotedCount' => $registeredCount - $votedCount,
            'progress' => $this->getBasicStatsProgress($votedCount, $registeredCount),
        ];
    }

    private function getBasicStatsProgress($votedCount, $registeredCount)
    {
        try {
            return \number_format(($votedCount / $registeredCount) * 100, 2);
        } catch (Exception $e) {
            return '0.00';
        }
    }
}
