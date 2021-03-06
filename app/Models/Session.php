<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Session extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'year',
        'active',
        'registration',
        'registration_at',
        'verification_type',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    /**
     * enum values in verification_type field
     *
     * @var string[]
     */
    public static $VERIFICATION_TYPES = ['open', 'code', 'email'];

    public static function booted()
    {
        static::deleting(function ($session) {
            $session->parties()->each(function ($party) {
                $party->delete();
            });
        });
    }

    public function parties()
    {
        return $this->hasMany('App\Models\Party');
    }

    public function registrations()
    {
        return $this->hasMany('App\Models\Registration');
    }

    public function studentVoteHistories()
    {
        return $this->hasMany('App\Models\StudentVoteHistory');
    }

    public function studentKeys()
    {
        return $this->hasMany('App\Models\StudentVoteKey');
    }

    public function getActive()
    {
        return $this->where(['active' => 1])->first();
    }

    public function getActiveSessionId()
    {
        return $this->select(['id'])->where(['active' => 1])->first()['id'];
    }

    /**
     * Determines if this session have registration.
     *
     * @return bool
     */
    public function haveRegistration()
    {
        return (bool) $this->registration == (bool) 1;
    }

    /**
     * checks if the election is open
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->started_at != null;
    }

    /**
     * checks if the registration is open
     *
     * @return bool
     */
    public function isRegistrationOpen()
    {
        return $this->registration_at != null;
    }

    /**
     * checks if the election is completed or ended.
     *
     * @return bool
     */
    public function isEnded()
    {
        return !\is_null($this->completed_at) || !\is_null($this->cancelled_at);
    }
}
