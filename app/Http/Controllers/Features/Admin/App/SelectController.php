<?php

namespace App\Http\Controllers\Features\Admin\App;

use App\Models\Course;
use App\Models\Session;
use App\Models\Position;
use App\Http\Controllers\Controller;

/**
 * Initial data to load on admin site
 */
class SelectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'sessions' => Session::select(['id', 'name', 'year', 'created_at'])->orderBy('year', 'desc')->orderBy('created_at', 'desc')->get(),
            'courses' => Course::select(['id', 'name', 'acronym'])->orderBy('name', 'asc')->get(),
            'positions' => Position::select(['id', 'name', 'order', 'per_party_count', 'choose_max_count'])
                ->orderBy('order', 'asc')
                ->get(),
        ]);
    }
}
