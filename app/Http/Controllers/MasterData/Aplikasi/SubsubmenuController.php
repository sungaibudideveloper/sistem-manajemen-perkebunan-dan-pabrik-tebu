<?php

namespace App\Http\Controllers\Masterdata\Aplikasi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Submenu;
use App\Models\Subsubmenu;

class SubsubmenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Subsubmenu::query()
            ->join('submenu', 'subsubmenu.submenuid', '=', 'submenu.submenuid')
            ->select('subsubmenu.*', 'submenu.name as submenu_name'); // ✅ perbaikan

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subsubmenu.name', 'like', '%' . $search . '%')
                    ->orWhere('submenu.name', 'like', '%' . $search . '%');
            });
        }

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('subsubmenu.subsubmenuid')->paginate($perPage);

        // Ambil semua submenu untuk dropdown (bukan menu)
        $allMenu = DB::table('submenu')
            ->join('menu', 'submenu.menuid', '=', 'menu.menuid')
            ->select('submenu.*', 'menu.name as menuname')
            ->orderBy('submenu.menuid')
            ->get();

        return view('aplikasi.subsubmenu.index', [
            'title' => 'Subsubmenu',
            'navbar' => 'Aplikasi',
            'nav' => 'Subsubmenu',
            'data' => $data,
            'perPage' => $perPage,
            'allMenu' => $allMenu,
        ]);
    }




    public function store(Request $request)
    {
        // dd($request);
        $nextId = DB::table('subsubmenu')->max('subsubmenuid') + 1;

        $nameexist = DB::table('subsubmenu')
            ->where('name', $request->subsubmenuname)
            ->exists();

        if ($nameexist) {
            return redirect()->back()->with('error', 'Nama subsubmenu sudah ada.');
        }

        DB::table('subsubmenu')->insert([
            'subsubmenuid' => $nextId,
            'submenuid' => $request->submenuid,
            'name' => $request->subsubmenuname,
            'updatedby' => null,
            'updatedat' => null,
        ]);

        return redirect()->back()->with('success', 'Data Submenu berhasil ditambahkan.');
    }

    public function update(Request $request, $subsubmenuid)
    {
        $request->validate([
            'subsubmenuname' => 'required|unique:subsubmenu,name,' . $subsubmenuid . ',subsubmenuid',
        ]);

        $validate = DB::table('subsubmenu')
            ->where('name', $request->subsubmenuname)
            ->where('submenuid', $request->submenuid)
            ->where('subsubmenuid', $subsubmenuid)
            ->exists();

        if ($validate) {
            return redirect()->back()->with('error', 'Nama subsubmenu sudah ada.');
        }

        $subsubmenu = subsubmenu::where('subsubmenuid', $subsubmenuid)->firstOrFail();
        $subsubmenu->submenuid = $request->input('submenuid');
        $subsubmenu->name = $request->input('subsubmenuname');
        $subsubmenu->updatedby = Auth::user()->userid;
        $subsubmenu->updatedat = now();
        $subsubmenu->save();

        Parent::h_flash('Data Berhasil Disimpan!.', 'success');

        return redirect()->route('masterdata.subsubmenu.index')->with('success', 'Submenu berhasil diupdate');
    }

    public function destroy($subsubmenuid, $name)
    {
        DB::table('subsubmenu')->where('subsubmenuid', $subsubmenuid)->where('name', $name)->delete();

        return redirect()->back()->with('success', 'Data Subsubmenu berhasil dihapus.');
    }
}
