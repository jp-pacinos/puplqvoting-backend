<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentVote extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['history_id', 'official_id'];

    public function voteHistory()
    {
        return $this->belongsTo('App\Models\VoteHistory', 'history_id');
    }

    public function official()
    {
        return $this->belongsTo('App\Models\Official');
    }
}
