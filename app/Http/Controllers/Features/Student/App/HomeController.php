<?php

namespace App\Http\Controllers\Features\Student\App;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function index()
    {
        return view('student.index');
    }
}
