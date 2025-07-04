<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function setSession(Request $request)
    {
        $request->validate([
            'dropdown_value' => 'required|string',
        ]);

        $companyCode = $request->dropdown_value;

        $companyName = \DB::table('company')
            ->where('companycode', $companyCode)
            ->value('name') ?? $companyCode;

        session([
            'companycode' => $companyCode,
            'companyname' => $companyName,
        ]);

        return redirect()->route('home')->with('success', 'Preference saved successfully!');
    }

}
