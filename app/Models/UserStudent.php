<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserStudent extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_number',
        'firstname',
        'lastname',
        'middlename',
        'suffix',
        'sex',
        'birthdate',
        'email',
        'can_vote',
        'course_id',
    ];

    protected $casts = [
        'birthdate' => 'date:Y-m-d',
        'created_at' => 'datetime:F j, Y, D g:i a', // 'datetime:Y-m-d H:i, g:i A'
        'updated_at' => 'datetime:F j, Y, D g:i a', // 'datetime:Y-m-d H:i, g:i A'
    ];

    public function course()
    {
        return $this->belongsTo('App\Models\Course');
    }

    public function votes()
    {
        return $this->hasManyThrough(
            'App\Models\StudentVote',
            'App\Models\StudentVoteHistory',
            'student_id',
            'history_id'
        );
    }

    public function voteHistories()
    {
        return $this->hasMany('App\Models\StudentVoteHistory', 'student_id');
    }

    public function registrations()
    {
        return $this->hasMany('App\Models\Registration', 'student_id');
    }

    public function voteKeys()
    {
        return $this->hasMany('App\Models\StudentVoteKey', 'student_id');
    }

    /**
     * a student can be a official
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function officials()
    {
        return $this->hasMany('App\Models\Official', 'student_id');
    }

    public function basicDetails()
    {
        return $this->addSelect([
            "{$this->getTable()}.id",
            'course_id',
            'student_number',
            'firstname',
            'lastname',
            'middlename',
            'suffix',
            'sex',
            'can_vote',
        ]);
    }

    public function maintenanceDetails()
    {
        return $this->basicDetails()
            ->leftJoin('officials', "{$this->getTable()}.id", '=', 'officials.student_id')
            ->addSelect([
                'officials.id as official_id',
            ]);
    }

    public function verifiedVoteHistory($sessionId)
    {
        return $this->voteHistories()
            ->where('session_id', '=', $sessionId)
            ->whereNotNull('verified_at')
            ->first();
    }

    public function canVote()
    {
        return $this->can_vote;
    }

    public function isVoteVerified($sessionId)
    {
        return $this->verifiedVoteHistory($sessionId) != null;
    }

    public function isRegistered($sessionId)
    {
        return $this->registrations()->select('id')->where('session_id', '=', $sessionId)->first() != null;
    }

    public function isValidConfirmationKey($key, $sessionId)
    {
        return $this->voteKeys()
            ->select('id')
            ->where([
                ['confirmation_code', '=', $key],
                ['session_id', '=', $sessionId],
            ])
            ->first() != null;
    }
}
