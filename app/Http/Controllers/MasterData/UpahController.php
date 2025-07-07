<?php

namespace App\Http\Controllers\Masterdata;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Submenu;
use App\Models\Upah;
use App\Models\User;


class UpahController extends Controller
{
    public function index(Request $request)
    {
        $query = upah::query();

        if ($request->filled('search')) {
            $query->where('jenisupah', 'like', '%' . $request->search . '%')
                ->orWhere('tanggalefektif', 'like', '%' . $request->search . '%')
            ;
        }

        $perPage = $request->get('perPage', 10);
        $data = $query->orderBy('jenisupah')->paginate($perPage);
        $user = Auth::user()->userid;
        $userdata = User::where('userid', $user)->firstOrFail();
        return view('master.upah.index', [
            'title' => 'Menu',
            'navbar' => 'Aplikasi',
            'nav' => 'Menu',
            'data' => $data,
            'perPage' => $perPage,
            'userdata' => $userdata,
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->userid && in_array('Create Upah', json_decode(Auth::user()->permissions ?? '[]'))) {
            $upah = upah::query();
            $cek = $upah->where('jenisupah', $request->jenisupah)->exists();

            if ($cek) {
                return redirect()->back()->with('error', 'Jenis upah sudah ada.');
            }

            $upah->insert([
                'jenisupah' => $request->jenisupah,
                'harga' => $request->harga,
                'tanggalefektif' => $request->tanggalefektif,
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
            ]);

            return redirect()->route('masterdata.upah.index')->with('success', 'Data berhasil ditambahkan.');
        } else {
            return redirect()->route('masterdata.upah.index')->with('error', 'Anda tidak memiliki izin untuk menambah data upah.');
        }
    }
    public function update(Request $request, $jenisupah)
    {
        if (Auth::user()->userid && in_array('Edit Upah', json_decode(Auth::user()->permissions ?? '[]'))) {

            $upah = upah::where('jenisupah', $jenisupah)->first();

            $upah->jenisupah = $request->input('jenisupah');
            $upah->harga = $request->input('harga');
            $upah->tanggalefektif = $request->input('tanggalefektif');
            $upah->save();

            Parent::h_flash('Data Berhasil Disimpan!.', 'success');

            return redirect()->route('masterdata.upah.index')->with('success', 'Menu berhasil diupdate');
        } else {
            return redirect()->route('masterdata.upah.index')->with('error', 'Anda tidak memiliki izin untuk mengedit data upah.');
        }
    }
    public function destroy($jenisupah)
    {
        if (Auth::user()->userid && in_array('Hapus Upah', json_decode(Auth::user()->permissions ?? '[]'))) {

            $upah = upah::findOrFail($jenisupah);
            $upah->delete();

            return redirect()->route('masterdata.upah.index')->with('success', 'Data berhasil dihapus.');
        }else{
            return redirect()->route('masterdata.upah.index')->with('error', 'Anda tidak memiliki izin untuk menghapus data upah.');
        }
    }
}
