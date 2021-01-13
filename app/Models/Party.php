<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Party extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'session_id'];

    protected $casts = [
        'created_at' => 'datetime:F j, Y, D g:i a', // 'datetime:Y-m-d H:i, g:i A'
        'updated_at' => 'datetime:F j, Y, D g:i a', // 'datetime:Y-m-d H:i, g:i A'
    ];

    public static function booted()
    {
        static::deleting(function ($party) {
            $party->officials()->each(function ($official) {
                $official->delete();
            });
        });
    }

    public function session()
    {
        return $this->belongsTo('App\Models\Session');
    }

    public function officials()
    {
        return $this->hasMany('App\Models\Official');
    }
}
