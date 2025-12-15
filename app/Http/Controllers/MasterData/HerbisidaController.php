<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\Models\MasterData\Herbisida;
use App\Models\MasterData\HerbisidaGroup;

class HerbisidaController extends Controller
{

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $companycode = Session::get('companycode'); // NEW: Get company session

        $query = Herbisida::where('companycode', $companycode); // NEW: Filter by company session

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('itemcode', 'like', "%{$search}%")
                    ->orWhere('itemname',  'like', "%{$search}%");
                // REMOVED: companycode search karena sudah di-filter
            });
        }

        $herbisida = $query
            ->orderBy('itemcode')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search'  => $search,
            ]);

        return view('masterdata.herbisida.index', [
            'herbisida' => $herbisida,
            'title'     => 'Data Herbisida',
            'navbar'    => 'Master',
            'nav'       => 'Herbisida',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

    public function store(Request $request)
    {
        $companycode = Session::get('companycode'); // NEW: Get company session

        $request->validate([
            'itemcode' => 'required|string|max:30',
            'itemname' => 'required|string|max:50',
            'measure' => 'required|string|max:10',
        ]);
        // REMOVED: companycode validation karena dari session
        // REMOVED: dosageperha validation karena sudah tidak ada

        $exists = Herbisida::where('companycode', $companycode) // NEW: Use session company
            ->where('itemcode', $request->itemcode)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'itemcode' => 'Duplicate Entry, Item Code already exists'
                ]);
        }

        Herbisida::create([
            'companycode' => $companycode, // NEW: Use session company
            'itemcode' => $request->input('itemcode'),
            'itemname' => $request->input('itemname'),
            'measure' => $request->input('measure'),
            'isactive' => 1, // NEW: Set default active
            'inputby'      => Auth::user()->userid,
            'createdat'    => now(),
        ]);
        // REMOVED: dosageperha karena sudah tidak ada di table

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }

    public function update(Request $request, $companycode, $itemcode)
    {
        $sessionCompanycode = Session::get('companycode'); // NEW: Get company session

        // NEW: Security check - ensure user can only edit their company data
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        $herbi = Herbisida::where([
            ['companycode', $companycode],
            ['itemcode', $itemcode]
        ])->firstOrFail();

        $validated = $request->validate([
            'itemcode' => 'required|string|max:30',
            'itemname' => 'required|string|max:50',
            'measure' => 'required|string|max:10',
        ]);
        // REMOVED: companycode validation karena tidak bisa diubah
        // REMOVED: dosageperha validation karena sudah tidak ada

        // NEW: Simplified duplicate check - hanya cek itemcode karena company tetap sama
        if ($request->itemcode !== $herbi->itemcode) {
            $exists = Herbisida::where('companycode', $companycode)
                ->where('itemcode', $request->itemcode)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'itemcode' => 'Duplicate Entry, Item Code already exists'
                    ]);
            }
        }

        Herbisida::where('companycode', $companycode)
            ->where('itemcode', $itemcode)
            ->update([
                'companycode' => $companycode, // NEW: Company tetap sama (dari session)
                'itemcode' => $validated['itemcode'],
                'itemname' => $validated['itemname'],
                'measure' => $validated['measure'],
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);
        // REMOVED: dosageperha karena sudah tidak ada di table

        return redirect()->back()->with('success', 'Data berhasil di‑update.');
    }

    public function destroy(Request $request, $companycode, $itemcode)
    {
        $sessionCompanycode = Session::get('companycode'); // NEW: Get company session

        // NEW: Security check - ensure user can only delete their company data
        if ($companycode !== $sessionCompanycode) {
            abort(403, 'Unauthorized access to company data');
        }

        Herbisida::where([
            ['companycode', $companycode],
            ['itemcode', $itemcode]
        ])->delete();

        return redirect()->back()->with('success', 'Data berhasil di‑hapus.');
    }

    public function group(Request $request)
    {
        // Hapus filter companycode karena kolom tidak ada
        return Herbisidagroup::select('herbisidagroupid', 'herbisidagroupname', 'activitycode')
            ->orderBy('herbisidagroupid')
            ->get();
    }
}
