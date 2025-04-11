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

        session(['companycode' => $request->dropdown_value]);

        return redirect()->route('home')->with('success', 'Preference saved successfully!');
    }
}
