<?php

namespace App\Http\Controllers\Features\Student\App;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return config('spa.student') ? redirect(config('spa.student')) : \response('', 204);
    }
}
