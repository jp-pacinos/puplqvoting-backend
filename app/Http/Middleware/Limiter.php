<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class Limiter
{
    protected $except = [
        'api/auth/login/*',
        'api/auth/login/admin/*',

        // students group update, delete
        'api/r/students/group/update',
        'api/r/students/group/delete',

        // actions
        'api/r/sessions/*/actions/*',
        // settings
        'api/r/sessions/*/settings/*'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isMethod = $request->isMethod('post') || $request->isMethod('patch') || $request->isMethod('put');

        if ($isMethod && !$this->inExceptArray($request)) {
            $query = DB::selectOne(DB::raw('SELECT SUM(n_live_tup) as count FROM pg_stat_user_tables'));

            if ($query->count > 8800) {
                \abort(
                    403,
                    'The maximum number of records is reached. You are limited to do actions, please remove some of the data.'
                );
            }
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
