<?php

namespace App\Http\Controllers\Aplikasi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('menu');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('slug', 'like', '%' . $request->search . '%')
                ;
        }

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('menuid')->paginate($perPage);

        return view('aplikasi.menu.index', [
            'title' => 'Menu',
            'navbar' => 'Aplikasi',
            'nav' => 'Menu',
            'data' => $data,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:50',
        ]);

        $nextId = DB::table('menu')->max('menuid') + 1;

        $nameexist = DB::table('menu')
            ->where('name', $request->name)
            ->exists();

        if ($nameexist) {
            return redirect()->back()->with('error','Nama menu sudah ada.');
        }

        DB::table('menu')->insert([
            'menuid' => $nextId,
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, $menuid)
    {
        $request->validate([
            'name' => 'required|unique:menu,name,' . $menuid . ',menuid',
            'slug' => 'required|unique:menu,slug,' . $menuid . ',menuid',
        ]);

        $menu = menu::where('menuid', $menuid)->firstOrFail();
        $menu->name = $request->input('name');
        $menu->slug = $request->input('slug');
        $menu->save();

        Parent::h_flash('Data Berhasil Disimpan!.', 'success');

        return redirect()->route('aplikasi.menu.index')->with('success', 'Menu berhasil diupdate');
    }

    public function destroy($menuid, $name)
    {
        DB::table('menu')->where('menuid', $menuid)->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
