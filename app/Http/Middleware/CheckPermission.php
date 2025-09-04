<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user || (!$user->hasPermission($permission) && !$user->hasAnyRole(['super']))) {
            abort(403);
        }
        return $next($request);
    }
}


