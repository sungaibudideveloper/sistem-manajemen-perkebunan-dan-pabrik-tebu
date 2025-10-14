<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
        
        // Check session SEBELUM login
        $existingSession = DB::table('sessions')
            ->where('user_id', $credentials['userid'])
            ->exists();
            
        if ($existingSession) {
            return back()->withErrors(['error' => 'Akun ini sedang digunakan di perangkat lain.']);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Regenerate session untuk security
            $request->session()->regenerate();

            if ($user->idjabatan == 5) {
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