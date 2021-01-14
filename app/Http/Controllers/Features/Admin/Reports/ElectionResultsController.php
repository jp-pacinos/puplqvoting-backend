<?php

namespace App\Http\Controllers\Features\Admin\Reports;

use App\Models\Session;
use App\Models\Position;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Features\Admin\Sessions\Stats\Concerns\HasBasicStats;
use App\Http\Controllers\Features\Admin\Sessions\Stats\Concerns\HasStudentVotes;

class ElectionResultsController extends Controller
{
    use HasBasicStats, HasStudentVotes;

    public function __construct()
    {
        $this->middleware('signed');
    }

    public function index(Session $session)
    {
        $parties = $this->getParties($session);

        $basicStats = $this->basicStats($session);
        $votes = $this->detailedStudentVoteStats($session, ['sortByVotes' => 'desc']);

        $data = [
            'year' => $session->year,
            'createdAt' => now()->format('F j, Y, D g:i a'),
            'basicStats' => $basicStats,
        ] + $this->format($votes, $parties);

        return app('dompdf.wrapper')
            ->loadView('reports.admin-election-results', $data)
            ->stream('Election Results for '.$session->year.'.pdf');
    }

    private function format($votes, $parties)
    {
        $positions = $this->getPositions();

        $summaryVotes = [
            // positionOrder => [position => '', officials: [[], [], [], ...]]
        ];
        $partiesVotes = [
            // partyid => [[official], [], [], ...]
        ];
        $totalCountByParty = [
            // partyid => 0
        ];
        $candidatesByParty = 0;

        $votesByPositions = $votes->mapToGroups(function ($official) use ($parties, $positions) {
            return [$official->position_id => [
                'name' => $this->fullname($official),
                'votes' => $official->votes,
                'party_name' => $parties[$official->party_id]->name,
                'position_name' => $positions[$official->position_id]->name,
            ]];
        });

        foreach ($positions as $position) {
            if (\is_null($votesByPositions[$position->id]) ?? null) {
                continue;
            }
            $candidatesByParty += $position->choose_max_count;
            $officials = $votesByPositions[$position->id];

            $summaryVotes[] = [
                'position' => $position->name,
                'officials' => $officials->slice(0, $position->choose_max_count),
            ];

            foreach ($officials as $official) {
                $partiesVotes[$official['party_name']][] = $official;

                if (\is_null($totalCountByParty[$official['party_name']] ?? null)) {
                    $totalCountByParty[$official['party_name']] = 0;
                }

                $totalCountByParty[$official['party_name']] += $official['votes'];
            }
        }

        return [
            'summaryVotes' => $summaryVotes,
            'partiesVotes' => $partiesVotes,
            'totalCountByParty' => $totalCountByParty,
            'candidatesCountByParty' => $candidatesByParty,
        ];
    }

    private function getPositions()
    {
        return Cache::remember(
            'ElectionResultsController/getPositions()',
            config('cache.stored-expiry'),
            function () {
                return Position::select(['id', 'name', 'order', 'choose_max_count'])
                    ->orderBy('order', 'asc')
                    ->get()
                    ->mapWithKeys(function ($position) {
                        return [$position->id => $position];
                    });
            }
        );
    }

    private function getParties(Session $session)
    {
        return $session->parties()->select(['id', 'name'])->get()->mapWithKeys(function ($party) {
            return [$party->id => $party];
        });
    }

    private function fullname($student)
    {
        return $student->lastname.' '.$student->firstname.' '.$student->middlename ?? ''.$student->suffix ?? '';
    }
}
