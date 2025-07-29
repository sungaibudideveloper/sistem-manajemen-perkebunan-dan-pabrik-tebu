<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MandorAccessManagement
{
    public function handle(Request $request, Closure $next)
    {
        // Skip middleware untuk route login/logout
        $currentPath = $request->path();
        if (in_array($currentPath, ['login', 'logout']) || !auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        
        // Jika user adalah mandor (idjabatan = 5)
        if ($user->idjabatan == 5) {
            // Halaman yang boleh diakses mandor
            $allowedPaths = [
                'mandor',
                'logout', 
                'chat/send',
                'chat/messages',
                'notifications/unread-count',
                'notifications/read',
                'set-session',
            ];
            
            $isAllowed = false;
            foreach ($allowedPaths as $allowedPath) {
                if (str_starts_with($currentPath, $allowedPath)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            // Jika tidak diizinkan DAN bukan sudah di mandor dashboard, redirect
            if (!$isAllowed && $currentPath !== 'mandor/dashboard') {
                return redirect()->route('mandor.dashboard')
                    ->with('error', 'Akses ditolak. Anda hanya dapat mengakses halaman Mandor.');
            }
        } 
        // Jika user BUKAN mandor tapi mencoba akses halaman mandor
        else if (str_starts_with($currentPath, 'mandor')) {
            return redirect()->route('home')
                ->with('error', 'Akses ditolak. Halaman khusus untuk Mandor.');
        }

        return $next($request);
    }
}