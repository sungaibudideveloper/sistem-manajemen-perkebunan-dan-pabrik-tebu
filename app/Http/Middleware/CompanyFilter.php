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

        if ($user && $user->companycode) {
            
            $request->merge(['companycode' => $user->companycode]);
        }

        return $next($request);
    }
}
