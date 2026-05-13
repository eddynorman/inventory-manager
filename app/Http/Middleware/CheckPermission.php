<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        if (!$user->is_active) {

            Auth::logout();

            abort(403, 'Your account is inactive.');
        }

        if (!$user->hasPermission($permission)) {

            abort(
                403,
                'You do not have permission to perform this action.'
            );
        }

        return $next($request);
    }
}
