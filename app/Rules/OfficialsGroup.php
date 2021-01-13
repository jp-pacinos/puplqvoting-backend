<?php

namespace App\Rules;

use App\Models\Official;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Validation\Rule;

/**
 * validate each official in the same position and session
 */
class OfficialsGroup implements Rule
{
    protected $cache;
    protected $cacheExpiry;

    protected $positionId;
    protected $officials;

    /**
     * Create a new rule instance.
     * @param integer $positionId
     *
     * @return void
     */
    public function __construct($positionId, $seesionId)
    {
        $this->cache = new Cache();
        $this->cacheExpiry = config('cache.stored-expiry');

        $this->positionId = $positionId;
        $this->officials = $this->getOfficials($positionId, $seesionId);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  integer  $officialId
     * @return bool
     */
    public function passes($attribute, $officialId)
    {
        return ! is_null($this->officials[$officialId.'-'.$this->positionId] ?? null);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The official may be invalid or unknown.';
    }

    /**
     * get and cache officials based on session_id and position_id
     *
     * @param integer $positionId
     *
     * @return App\Models\Official
     */
    protected function getOfficials($positionId, $sessionId)
    {
        return $this->cache::remember(
            'ActiveSessionOfficial\getOfficials('.$positionId.', '.$sessionId.')',
            $this->cacheExpiry,
            function () use ($positionId, $sessionId) {
                return Official::join('parties', 'officials.party_id', '=', 'parties.id')
                    ->select(['officials.id', 'officials.position_id'])
                    ->where(['officials.position_id' => $positionId, 'parties.session_id' => $sessionId])
                    ->get()
                    ->mapWithKeys(function ($official) {
                        return [$official->id.'-'.$official->position_id => true];
                    });
            }
        );
    }
}
