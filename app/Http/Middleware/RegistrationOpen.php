<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\StudentActiveSession;

class RegistrationOpen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $session = (new StudentActiveSession())->getInstance();

        if (! $session->isRegistrationOpen()) {
            abort(403, 'Registration is not yet started.');
        }

        return $next($request);
    }
}
