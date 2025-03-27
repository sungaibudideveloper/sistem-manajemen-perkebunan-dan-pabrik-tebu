<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Home';
        $user = DB::table('username')->where('usernm', '=', Auth::user()->usernm)
            ->value('name');
        $companyRaw = DB::table('usercomp')->where('usernm', '=', Auth::user()->usernm)
            ->value('kd_comp');
        $company = explode(',', $companyRaw);
        sort($company);
        $showPopup = !$request->session()->has('dropdown_value');
        $period = DB::table('perusahaan')->where('kd_comp', '=', session('dropdown_value'))
            ->value('tgl');
        $now = Carbon::now()->toDateString();

        return view('home', compact('title', 'now', 'period', 'user', 'showPopup', 'company'));
    }

    public function setSession(Request $request)
    {
        $request->validate([
            'dropdown_value' => 'required|string',
        ]);

        session(['dropdown_value' => $request->dropdown_value]);

        return redirect()->route('home');
    }
}
