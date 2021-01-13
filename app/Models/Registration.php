<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
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
