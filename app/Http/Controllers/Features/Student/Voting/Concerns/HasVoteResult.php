<?php

namespace App\Http\Controllers\Features\Student\Voting\Concerns;

use Illuminate\Support\Facades\URL;

trait HasVoteResult
{
    private function getVoteResultLink(int $historyId, $parameters = [])
    {
        return (new URL())::temporarySignedRoute(
            'vote.final.report',
            now()->addDay(),
            ['history' => $historyId] + $parameters
        );
    }
}
