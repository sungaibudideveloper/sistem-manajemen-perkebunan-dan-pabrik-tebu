<?php

namespace App\Http\Controllers\Pabrik;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TrashController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('perPage', 10);

        // ? TEMPORARY: Comment company filter untuk testing
        // $companycode = session('companycode');

        // Fake data untuk testing UI dulu
        $data = collect([
            (object)[
                'id' => 1,
                'no_surat_jalan' => 'SJ001/2024',
                'jenis' => 'manual',
                'berat_bersih' => 45.680,
                'berat_kotor' => 49.560,
                'created_at' => now()
            ],
            (object)[
                'id' => 2,
                'no_surat_jalan' => 'SJ002/2024',
                'jenis' => 'mesin',
                'berat_bersih' => 67.250,
                'berat_kotor' => 72.180,
                'created_at' => now()->subHours(2)
            ]
        ]);

        // Convert to paginator untuk testing
        $data = new \Illuminate\Pagination\LengthAwarePaginator(
            $data, // items
            $data->count(), // total
            $perPage, // per page
            1, // current page
            ['path' => request()->url()]
        );

        return view('pabrik.trash.index', [
            'title' => 'Trash Pabrik',
            'navbar' => 'Pabrik',
            'nav' => 'Trash',
            'data' => $data
        ]);
    }

public function checkSuratJalan(Request $request)
{
    try {
        $noSuratJalan = $request->get('no');

        if (empty($noSuratJalan)) {
            return response()->json([
                'exists' => false,
                'message' => 'Nomor surat jalan harus diisi'
            ]);
        }

        // ? Cari berdasarkan nomor surat jalan saja
        $suratJalan = DB::table('suratjalanpos')
            ->where('suratjalanno', $noSuratJalan)
            ->first();

        if ($suratJalan) {
            return response()->json([
                'exists' => true,
                'message' => 'Surat jalan ditemukan dan siap digunakan',
                'data' => [
                    'suratjalanno' => $suratJalan->suratjalanno,
                    'companycode' => $suratJalan->companycode, // Return company code
                    'plot' => $suratJalan->plot,
                    'varietas' => $suratJalan->varietas,
                    'kategori' => $suratJalan->kategori,
                ]
            ]);
        } else {
            return response()->json([
                'exists' => false,
                'message' => 'Nomor surat jalan tidak ditemukan dalam sistem'
            ]);
        }

    } catch (\Exception $e) {
        return response()->json([
            'exists' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

    public function store(Request $request)
    {dd($request);
        return response()->json(['message' => 'Store method - coming soon']);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Update method - coming soon']);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Destroy method - coming soon']);
    }
}
