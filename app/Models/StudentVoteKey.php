<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentVoteKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'confirmation_code',
        'session_id',
        'student_id',
    ];

    public function session()
    {
        return $this->belongsTo('App\Models\Session');
    }

    public function student()
    {
        return $this->belongsTo('App\Models\UserStudent');
    }
}
