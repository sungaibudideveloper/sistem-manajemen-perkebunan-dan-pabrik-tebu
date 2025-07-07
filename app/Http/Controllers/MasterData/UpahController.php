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
use PHPUnit\Framework\Constraint\IsTrue;

class UpahController extends Controller
{
    public function index(Request $request)
    {
        // Query dengan join untuk mendapatkan kategori_id
        $query = DB::table('upah as a')
            ->leftJoin('kategoriupah as b', 'a.jenisupah', '=', 'b.id')
            ->select('a.*', 'b.id as kategori_id', 'b.jenisupah as jenisupah2');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('a.jenisupah', 'like', '%' . $request->search . '%')
                    ->orWhere('a.tanggalefektif', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = $request->get('perPage', 10);
        // Gunakan alias 'a' karena ada join
        $data = $query->orderBy('a.tanggalefektif', 'DESC')->paginate($perPage);

        $user = Auth::user()->userid;
        $userdata = User::where('userid', $user)->firstOrFail();

        $pilihanupah = DB::table('kategoriupah')->get();

        return view('master.upah.index', [
            'title' => 'Menu',
            'navbar' => 'Aplikasi',
            'nav' => 'Menu',
            'data' => $data,
            'perPage' => $perPage,
            'userdata' => $userdata,
            'pilihanupah' => $pilihanupah,
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->userid && in_array('Create Upah', json_decode(Auth::user()->permissions ?? '[]'))) {

            // Ambil jenisupah dari kategoriupah berdasarkan id
            $kategori = DB::table('kategoriupah')->where('id', $request->kategori_id)->first();

            if (!$kategori) {
                return redirect()->back()->with('error', 'Kategori upah tidak ditemukan.');
            }

            $cek = DB::table('upah')
                ->where('jenisupah', $request->kategori_id)->where('tanggalefektif', $request->tanggalefektif)
                ->where('harga', $request->harga)->where('companycode', Auth::user()->companycode)
                ->exists();

            if ($cek) {
                return redirect()->route('masterdata.upah.index')->with('error', 'Data upah dengan kategori, tanggal efektif, dan harga yang sama sudah ada.');
            }

            $upah = new Upah();
            $upah->jenisupah = $request->kategori_id;
            $upah->harga = $request->harga;
            $upah->tanggalefektif = $request->tanggalefektif;
            $upah->inputby = Auth::user()->userid;
            $upah->createdat = now();
            $upah->companycode = Auth::user()->companycode; // Tambahkan companycode
            $upah->save();

            return redirect()->route('masterdata.upah.index')
                ->with('success', 'Data upah berhasil ditambahkan');
        } else {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Anda tidak memiliki izin untuk menambah data upah.');
        }
    }

    public function update(Request $request, $jenisupah, $harga, $tanggalefektif)
    {
        if (Auth::user()->userid && in_array('Edit Upah', json_decode(Auth::user()->permissions ?? '[]'))) {
            $company = Auth::user()->companycode;

            // Cari upah berdasarkan jenisupah lama
            $validasi = DB::table('upah')->where('jenisupah', $request->kategori_id)->where('harga', $request->harga)
                ->where('tanggalefektif', $request->tanggalefektif)->where('companycode', $company)->exists();

            if ($validasi == True) {
                return redirect()->route('masterdata.upah.index')->with('error', 'Data upah dengan kategori, tanggal efektif, dan harga yang sama sudah ada.');
            }

            // Ambil jenisupah baru dari kategoriupah
            $cekkategori = DB::table('kategoriupah')->where('id', $request->kategori_id)->first();

            if (!$cekkategori) {
                return redirect()->back()->with('error', 'Kategori upah tidak ditemukan.');
            }

            DB::table('upah')
                ->where('jenisupah', $jenisupah)
                ->where('harga', $harga)
                ->where('tanggalefektif', $tanggalefektif)
                ->where('companycode', $company)
                ->update([
                    'jenisupah' => $request->kategori_id,
                    'harga' => $request->harga,
                    'tanggalefektif' => $request->tanggalefektif,

                ]);

            Parent::h_flash('Data Berhasil Diupdate!', 'success');

            return redirect()->route('masterdata.upah.index')
                ->with('success', 'Data upah berhasil diupdate');
        } else {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengedit data upah.');
        }
    }
    public function destroy($jenisupah, $harga, $tanggalefektif)
    {
        if (Auth::user()->userid && in_array('Hapus Upah', json_decode(Auth::user()->permissions ?? '[]'))) {
            $company = Auth::user()->companycode;

            $deleted = Upah::where('jenisupah', $jenisupah)
                ->where('harga', $harga)
                ->where('tanggalefektif', $tanggalefektif)
                ->where('companycode', $company)
                ->delete();

            if ($deleted) {
                return redirect()->route('masterdata.upah.index')
                    ->with('success', 'Data berhasil dihapus.');
            } else {
                return redirect()->route('masterdata.upah.index')
                    ->with('error', 'Data upah tidak ditemukan.');
            }
        } else {
            return redirect()->route('masterdata.upah.index')
                ->with('error', 'Anda tidak memiliki izin untuk menghapus data upah.');
        }
    }
}
