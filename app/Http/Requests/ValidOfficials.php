<?php

namespace App\Http\Requests;

use App\Models\Position;
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
                $sessionId = (new StudentActiveSession())->id();
                $positions = (new Position())->select(['id', 'choose_max_count as maxCount'])->get();

                $rules = $positions->mapWithKeys(function ($position) use ($sessionId) {
                    $key = 'position-'.$position['id'];

                    return [
                        $key => ['required', 'array', 'size:'.$position['maxCount']],
                        $key.'.*' => [
                            'required',
                            'numeric',
                            'distinct',
                            new OfficialsGroup($position['id'], $sessionId),
                        ],
                    ];
                });

                return $rules->toArray();
            }
        );
    }
}
