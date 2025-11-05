<?php

namespace App\Http\Controllers\Masterdata\Aplikasi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Submenu;

class SubmenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Submenu::query()
            ->join('menu', 'submenu.menuid', '=', 'menu.menuid')
            ->select('submenu.*', 'menu.name as menu_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('submenu.name', 'like', '%' . $search . '%')
                    ->orWhere('submenu.slug', 'like', '%' . $search . '%')
                    ->orWhere('menu.name', 'like', '%' . $search . '%');
            });
        }

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('submenu.submenuid')->paginate($perPage);

        // Ambil semua menu untuk dropdown
        $allMenu = DB::table('menu')->select('menuid', 'name')->orderBy('name')->get();

        return view('aplikasi.submenu.index', [
            'title' => 'Submenu',
            'navbar' => 'Aplikasi',
            'nav' => 'Submenu',
            'data' => $data,
            'perPage' => $perPage,
            'allMenu' => $allMenu, // <--- penting untuk dropdown di Blade
        ]);
    }



    public function store(Request $request)
    {
        $nextId = DB::table('submenu')->max('submenuid') + 1;

        $nameexist = DB::table('submenu')
            ->where('name', $request->submenuname)
            ->where('menuid', $request->menuid)
            ->exists();

        if ($nameexist) {
            return redirect()->back()->with('error', 'Nama submenu sudah ada.');
        }

        if ($request->parentid == null) { 
            $parentid = null; // Set parentid to null if not provided
        } else {
            $parentid = $request->parentid;
        }

        DB::table('submenu')->insert([
            'submenuid' => $nextId,
            'menuid' => $request->menuid,
            'parentid' => $parentid,
            'name' => $request->submenuname,
            'slug' => $request->slug,
            'updatedby' => null,
            'updatedat' => null,
        ]);

        return redirect()->back()->with('success', 'Data Submenu berhasil ditambahkan.');
    }

    public function update(Request $request, $submenuid)
    {
        if ($request->input('submenuname') == null) {
            return redirect()->back()->with('error', 'Nama submenu tidak boleh kosong.');
        }

        $validate = DB::table('submenu')
            ->where('name', $request->submenuname)
            ->where('menuid', $request->menuid)
            ->exists();

        if ($validate) {
            return redirect()->back()->with('error', 'Nama subsubmenu sudah ada.');
        }


        if ($request->parentid == null) {
            $parentid = null; // Set parentid to null if not provided
        } else {
            $parentid = $request->parentid;
        }

        $submenu = submenu::where('submenuid', $submenuid)->firstOrFail();
        $submenu->name = $request->input('submenuname');
        $submenu->slug = $request->input('slug');
        $submenu->menuid = $request->input('menuid');
        $submenu->parentid = $parentid;
        $submenu->save();

        Parent::h_flash('Data Berhasil Disimpan!.', 'success');

        return redirect()->route('usermanagement.submenu.index')->with('success', 'Submenu berhasil diupdate');
    }

    public function destroy($submenuid, $name)
    {
        DB::table('submenu')->where('submenuid', $submenuid)->where('name', $name)->delete();

        return redirect()->route('usermanagement.submenu.index')->with('success', 'Data Submenu berhasil dihapus.');
    }
}
