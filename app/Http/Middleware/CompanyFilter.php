<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyFilter
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->kd_comp) {
            
            $request->merge(['kd_comp' => $user->kd_comp]);
        }

        return $next($request);
    }
}
