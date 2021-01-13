<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\StudentActiveSession;

class ValidSessionVerificationType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $type)
    {
        $verificationType = (new StudentActiveSession())->getInstance()->verification_type;

        if ($verificationType != $type) {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}
