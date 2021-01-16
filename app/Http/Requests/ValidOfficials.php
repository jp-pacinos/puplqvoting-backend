<?php

namespace App\Http\Requests;

use App\Models\Official;
use App\Rules\OfficialsGroup;
use Illuminate\Support\Facades\Cache;
use App\Services\StudentActiveSession;
use Illuminate\Foundation\Http\FormRequest;

class ValidOfficials extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->getRules();
    }

    private function getRules()
    {
        return Cache::remember(
            'StudentCanVote\getRules()',
            config('cache.stored-expiry'),
            function () {
                $session = (new StudentActiveSession())->getInstance();

                // $positions = (new Position())->select(['id', 'choose_max_count'])->get();
                $positions = Official::select(['positions.id', 'positions.choose_max_count'])
                    ->whereIn('officials.party_id', $session->parties->modelKeys())
                    ->join('positions', 'positions.id', '=', 'officials.position_id')
                    ->distinct()
                    ->get();

                $rules = $positions->mapWithKeys(function ($position) use ($session) {
                    $key = 'position-'.$position['id'];

                    return [
                        $key => ['required', 'array', 'size:'.$position['choose_max_count']],
                        $key.'.*' => [
                            'required',
                            'numeric',
                            'distinct',
                            new OfficialsGroup($position['id'], $session->id),
                        ],
                    ];
                });

                return $rules->toArray();
            }
        );
    }
}
