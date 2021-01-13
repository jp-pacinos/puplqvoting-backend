<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\StudentActiveSession;

class VotingOpen
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

        if ($session == null || ! $session->isOpen()) {
            abort(403, 'Election is not yet started.');
        }

        if ($session->isEnded()) {
            abort(403, 'Election is closed.');
        }

        return $next($request);
    }
}
