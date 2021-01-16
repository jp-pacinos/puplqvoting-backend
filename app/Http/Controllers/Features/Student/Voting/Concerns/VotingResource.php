<?php

namespace App\Http\Controllers\Features\Student\Voting\Concerns;

use App\Models\Party;
use App\Models\Official;
use App\Models\Position;
use Illuminate\Support\Facades\Cache;

trait VotingResource
{
    private function getPositions(Position $position)
    {
        return Cache::remember('VotingController\getPositions()', config('cache.stored-expiry'), function () use ($position) {
            return $position->select(['id', 'name', 'order', 'choose_max_count'])
                ->orderBy('order', 'asc')
                ->get()
                ->map(function ($position) {
                    return [
                        'id' => $position['id'],
                        'name' => $position['name'],
                        'order' => $position['order'],
                        'choose_max_count' => $position['choose_max_count'],
                        'keyName' => 'position-'.$position['id'],
                    ];
                });
        });
    }

    private function getAvailablePositions($candidates)
    {
        return Cache::remember(
            'VotingController\getAvailablePositions()',
            config('cache.stored-expiry'),
            function () use ($candidates) {
                return Position::select(['id', 'name', 'order', 'choose_max_count'])
                    ->whereIn('id', $candidates->pluck('position_id')->unique())
                    ->orderBy('order', 'asc')
                    ->get()
                    ->map(function ($position) {
                        return [
                            'id' => $position['id'],
                            'name' => $position['name'],
                            'order' => $position['order'],
                            'choose_max_count' => $position['choose_max_count'],
                            'keyName' => 'position-'.$position['id'],
                        ];
                    });
            }
        );
    }

    private function getParties(Party $party, $sessionId)
    {
        return Cache::remember('VotingController\getParties()', config('cache.stored-expiry'), function () use ($party, $sessionId) {
            return $party
                ->select(['id', 'name'])
                ->where('session_id', '=', $sessionId)
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    private function getOfficials(Official $official, $sessionId)
    {
        return Cache::remember('VotingController\getOfficials()', config('cache.stored-expiry'), function () use ($official, $sessionId) {
            return $official
                ->select([
                    'officials.id as id',
                    'user_students.lastname',
                    'user_students.firstname',
                    'user_students.middlename',
                    'user_students.suffix',
                    'officials.position_id',
                    'officials.party_id',
                    'officials.display_picture',
                ])
                ->join('user_students', 'officials.student_id', '=', 'user_students.id')
                ->join('parties', 'officials.party_id', '=', 'parties.id')
                ->where('parties.session_id', '=', $sessionId)
                ->orderBy('user_students.lastname', 'asc')
                ->get();
        });
    }
}
