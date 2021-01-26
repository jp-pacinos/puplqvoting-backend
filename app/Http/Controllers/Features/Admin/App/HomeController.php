<?php

namespace App\Http\Controllers\Features\Admin\App;

class HomeController
{
    public function index()
    {
        return config('spa.admin') ? redirect(config('spa.admin')) : \response('', 204);
    }
}
