<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckPermission;
use App\Models\User;

class UsernameLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'userid' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('userid', 'password');
        
        // Check if user exists and get isactive status
        $user = User::where('userid', $credentials['userid'])->first();
        
        // Check if user exists
        if (!$user) {
            return back()->withInput()->withErrors([
                'login_error' => 'Username atau password salah.',
            ]);
        }
        
        // Check if user is active
        if ($user->isactive != 1) {
            return back()->withInput()->withErrors([
                'login_error' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }
        
        // Check session SEBELUM login
        $existingSession = DB::table('sessions')
            ->where('user_id', $credentials['userid'])
            ->exists();
            
        if ($existingSession) {
            return back()->withErrors(['error' => 'Akun ini sedang digunakan di perangkat lain.']);
        }

        // Attempt login (password will be verified here)
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Regenerate session untuk security
            $request->session()->regenerate();

            if ($user->idjabatan == 20) {
                return redirect()->route('mandor.splash');
            }

            return redirect()->route('home');
        }

        return back()->withInput()->withErrors([
            'login_error' => 'Username atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Clear cache only if user exists
        if ($user) {
            CheckPermission::clearUserCache($user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Jika dari Inertia, force external redirect
        if ($request->header('X-Inertia')) {
            return \Inertia\Inertia::location(route('login'));
        }

        return redirect()->route('login');
    }
}