<?php

namespace App\Services;

use Illuminate\Support\Str;

class CodeGenerator
{
    public static function make($length = 7)
    {
        return Str::random($length);
    }
}
