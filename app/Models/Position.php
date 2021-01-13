<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'order', 'per_party_count', 'choose_max_count'];

    public function officials()
    {
        return $this->hasMany('App\Models\Official');
    }
}
