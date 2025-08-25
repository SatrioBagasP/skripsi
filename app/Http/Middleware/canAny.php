<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class canAny
{
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        // Cek apakah user punya salah satu dari gate
        foreach ($abilities as $ability) {
            if (Gate::allows($ability)) {
                return $next($request);
            }
        }

        abort(403); // kalau tidak ada satupun
    }
}
