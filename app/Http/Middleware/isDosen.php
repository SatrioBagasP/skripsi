<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isDosen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role_id = Auth::user()->role_id;

        if ($role_id >= 3 || $role_id == 1) {
            return $next($request);
        } else {
            if ($request->ajax()) {
                return response()->json(['message' => 'Anda tidak ada akses ke halaman ini'], 403);
            }
            return abort(403);
        }
    }
}
