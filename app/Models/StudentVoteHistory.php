<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentVoteHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['verified_at', 'session_id', 'student_id'];

    public function session()
    {
        return $this->belongsTo('App\Models\Session');
    }

    public function student()
    {
        return $this->belongsTo('App\Models\UserStudent', 'student_id');
    }

    public function votes()
    {
        return $this->hasMany('App\Models\StudentVote', 'history_id');
    }
}
