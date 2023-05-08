<?php

namespace App\Http\Middleware;

use App\Models\Publication;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPublicationOwner
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $publication = $request->route('publication');
        $userId = Auth::id();

        if (!$publication || $publication->user_id != $userId) {
            return abort(Response::HTTP_NOT_FOUND, __('responses.not_found'));
        }

        return $next($request);
    }
}
