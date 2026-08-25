<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['owner', 'admin', 'super_admin'])) {
            return response()->json([
                'message' => 'Ushbu amalni bajarish uchun dacha egasi (owner) bo\'lishingiz talab etiladi.'
            ], 403);
        }

        return $next($request);
    }
}
