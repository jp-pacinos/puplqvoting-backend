<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class AppRestoreController extends Controller
{
    public function index()
    {
        Artisan::call('migrate:fresh --seed --force');

        return \response()->json([
            'message' => 'App restored successfully.',
        ]);
    }
}
