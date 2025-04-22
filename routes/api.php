<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Herbisida;

Route::get('/herbisida', function(Request $request) {
    $company = $request->query('companycode');
    return Herbisida::where('companycode', $company)
        ->select('itemcode', 'itemname')
        ->orderBy('itemcode')
        ->get();
});