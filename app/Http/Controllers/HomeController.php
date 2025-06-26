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
        $user = DB::table('user')->where('userid', '=', Auth::user()->userid)
            ->value('name');
        $companyRaw = DB::table('usercompany')->where('userid', '=', Auth::user()->userid)
            ->value('companycode');
        $company = explode(',', $companyRaw);
        sort($company);
        $showPopup = !$request->session()->has('companycode');
        $period = DB::table('company')->where('companycode', '=', session('companycode'))->value('updatedat');
        $now = Carbon::now()->toDateString();

        return view('home', compact('title', 'now', 'period', 'user', 'showPopup', 'company'));
    }

    public function setSession(Request $request)
    {
        $request->validate([
            'dropdown_value' => 'required|string',
        ]);

        session(['companycode' => $request->dropdown_value]);

        return redirect()->route('home');
    }
}
