<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanctumTokenCan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (! $request->user()->tokenCan($guards[0])) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
