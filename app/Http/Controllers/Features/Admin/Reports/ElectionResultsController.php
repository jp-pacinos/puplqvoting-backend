<?php

namespace App\Http\Controllers\Features\Admin\Reports;

use App\Models\Session;
use App\Http\Controllers\Features\Admin\Sessions\Stats\Concerns\HasBasicStats;
use App\Http\Controllers\Features\Admin\Sessions\Stats\Concerns\HasStudentVotes;

class ElectionResultsController
{
    use HasBasicStats, HasStudentVotes;

    public function index(Session $session)
    {
        $basicStatus = $this->basicStats($session);

        $votes = $this->detailedStudentVoteStats($session);

        return app('dompdf.wrapper')
            ->loadView('reports.admin-election-results', [])
            ->stream('Election Results for 2021'.'.pdf');
    }
}
