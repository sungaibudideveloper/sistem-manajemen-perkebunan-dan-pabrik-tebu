<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class KontraktorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $companycode = Session::get('companycode');
    
        $query = DB::table('kontraktor')
            ->where('companycode', $companycode);
    
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('namakontraktor', 'like', "%{$search}%");
            });
        }

        $kontraktor = $query
            ->orderBy('id')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);
    
        return view('master.kontraktor.index', [
            'kontraktor' => $kontraktor,
            'title'     => 'Data Kontraktor',
            'navbar'    => 'Master',
            'nav'       => 'Kontraktor',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = Session::get('companycode');
        
        $request->validate([
            'id' => 'required|string|max:10',
            'namakontraktor' => 'required|string|max:100',
        ]);

        $exists = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('id', $request->id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'id' => 'Duplicate Entry, ID Kontraktor sudah ada'
                ]);
        }

        DB::table('kontraktor')->insert([
            'companycode' => $companycode,
            'id' => strtoupper($request->input('id')),
            'namakontraktor' => $request->input('namakontraktor'),
            'isactive' => 1,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
        ]);

        return redirect()->back()->with('success', 'Data kontraktor berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $id)
    {   
        $sessionCompanycode = Session::get('companycode');
        
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }
        
        $kontraktor = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('id', $id)
            ->first();

        if (!$kontraktor) {
            abort(404, 'Data not found');
        }

        $validated = $request->validate([
            'id' => 'required|string|max:10',
            'namakontraktor' => 'required|string|max:100',
        ]);
        
        // Cek duplicate jika ID diubah
        if ($request->id !== $id) {
            $exists = DB::table('kontraktor')
                ->where('companycode', $companycode)
                ->where('id', $request->id)
                ->exists();
    
            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'id' => 'Duplicate Entry, ID Kontraktor sudah ada'
                    ]);
            }
        }
        
        DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('id', $id)
            ->update([
                'id' => strtoupper($validated['id']),
                'namakontraktor' => $validated['namakontraktor'],
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);
    
        return redirect()->back()->with('success', 'Data kontraktor berhasil di-update.');
    }

    public function destroy(Request $request, $companycode, $id)
    {
        $sessionCompanycode = Session::get('companycode');
        
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        // Cek apakah ada subkontraktor yang masih menggunakan kontraktor ini
        $hasSubkontraktor = DB::table('subkontraktor')
            ->where('companycode', $companycode)
            ->where('kontraktorid', $id)
            ->exists();

        if ($hasSubkontraktor) {
            return redirect()->back()
                ->withErrors([
                    'delete' => 'Tidak dapat menghapus kontraktor karena masih memiliki subkontraktor'
                ]);
        }

        DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('id', $id)
            ->delete();

        return redirect()->back()->with('success', 'Data kontraktor berhasil di-hapus.');
    }
}