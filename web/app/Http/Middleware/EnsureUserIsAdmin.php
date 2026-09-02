<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json([
                'message' => 'Ushbu amalni bajarish uchun administrator huquqi talab qilinadi.'
            ], 403);
        }

        return $next($request);
    }
}
