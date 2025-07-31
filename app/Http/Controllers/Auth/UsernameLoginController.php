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

        if (Auth::attempt($request->only('userid', 'password'))) {
            $user = Auth::user();
            $session =  DB::table('sessions')->where('user_id', $user->userid)->exists();
            if ($session) {
                Auth::logout();
                return back()->withErrors(['error' => 'Akun ini sedang digunakan di perangkat lain.']);
            }

            // Redirect berdasarkan jabatan
            if ($user->idjabatan == 5) {
                return redirect()->route('mandor.splash');
            }

            return redirect()->route('home');
        }

        return redirect()->back()->withInput()->withErrors([
            'login_error' => 'Username atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}