<?php

namespace App\Models;

use App\Casts\StorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Official extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['display_picture', 'student_id', 'position_id', 'party_id'];

    protected $casts = [
        'display_picture' => StorageUrl::class,
    ];

    public static function booted()
    {
        static::deleting(function ($official) {
            if ($official->getRawOriginal('display_picture')) {
                Storage::disk('public')->delete($official->getRawOriginal('display_picture'));
            }
        });
    }

    public function position()
    {
        return $this->belongsTo('App\Models\Position');
    }

    public function party()
    {
        return $this->belongsTo('App\Models\Party');
    }

    public function student()
    {
        return $this->belongsTo('App\Models\UserStudent');
    }
}
