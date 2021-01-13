<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\StudentActiveSession;

class StudentAllowedToVote
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
        $student = $request->user();

        if ($session->haveRegistration()) {
            if (! $student->isRegistered($session->id)) {
                abort(403, 'You\'re not registered in this election.');
            }
        }

        if ($student->isVoteVerified($session->id)) {
            abort(403, 'You\'re already voted.');
        }

        if (! $student->canVote()) {
            abort(403, 'You can\'t vote.');
        }

        return $next($request);
    }
}
