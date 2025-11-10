<?php

namespace App\Http\Controllers\Pabrik;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Trash;

class TrashController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('perPage', 10);
        $companycode = session('companycode');

        $query = Trash::query();

        // Filter by company
        if ($companycode) {
            $query->where('companycode', $companycode);
        }

        // Search functionality
        if ($search) {
            $query->where('suratjalanno', 'like', "%{$search}%");
        }

        // Order by suratjalanno aja, jangan created_at
        $query->orderBy('suratjalanno', 'desc');

        $data = $query->paginate($perPage);

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
    {
        try {
            DB::beginTransaction();

            // Validation
            $request->validate([
                'companycode' => 'required|string',
                'no_surat_jalan' => 'required|string',
                'jenis' => 'required|in:manual,mesin',
                'berat_bersih' => 'required|string',
                'pucuk' => 'nullable|string',
                'daun_gulma' => 'nullable|string',
                'sogolan' => 'nullable|string',
                'siwilan' => 'nullable|string',
                'tebumati' => 'nullable|string',
                'tanah_etc' => 'nullable|string'
            ], [
                'companycode.required' => 'Company code wajib diisi',
                'no_surat_jalan.required' => 'Nomor surat jalan wajib diisi',
                'jenis.required' => 'Jenis trash wajib dipilih',
                'jenis.in' => 'Jenis trash harus manual atau mesin',
                'berat_bersih.required' => 'Berat bersih wajib diisi'
            ]);

            // Check if combination already exists
            $exists = DB::table('trash')
                ->where('suratjalanno', $request->no_surat_jalan)
                ->where('companycode', $request->companycode)
                ->where('jenis', $request->jenis)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'Data trash untuk nomor surat jalan ini dengan jenis yang sama sudah ada!')
                    ->withInput();
            }

            // Convert comma format to decimal for calculation
            $beratBersih = $this->parseDecimal($request->berat_bersih);
            $pucuk = $this->parseDecimal($request->pucuk ?? '0');
            $daunGulma = $this->parseDecimal($request->daun_gulma ?? '0');
            $sogolan = $this->parseDecimal($request->sogolan ?? '0');
            $siwilan = $this->parseDecimal($request->siwilan ?? '0');
            $tebumati = $this->parseDecimal($request->tebumati ?? '0');
            $tanahEtc = $this->parseDecimal($request->tanah_etc ?? '0');
            $beratKotor = $this->parseDecimal($request->berat_kotor);

            // Calculate percentages based on berat kotor
            $tebumatiPct = $beratKotor > 0 ? round(($tebumati / $beratKotor) * 100, 2) : 0; // 2 desimal
            $daunPct = $beratKotor > 0 ? round(($daunGulma / $beratKotor) * 100, 2) : 0; // 2 desimal
            $pucukPct = $beratKotor > 0 ? round(($pucuk / $beratKotor) * 100, 2) : 0; // 2 desimal
            $sogolanPct = $beratKotor > 0 ? round(($sogolan / $beratKotor) * 100, 2) : 0; // 2 desimal
            $siwlanPct = $beratKotor > 0 ? round(($siwilan / $beratKotor) * 100, 2) : 0; // 2 desimal
            $tanahEtcRounded = round($tanahEtc, 2); // 2 desimal
            // Calculate totals dengan 3 desimal
            $totalTrash = round($tebumatiPct + $daunPct + $pucukPct + $sogolanPct + $siwlanPct + $tanahEtcRounded, 3); // 3 desimal
            $nettoTrash = round($totalTrash - 5, 3); // 3 desimal           
            // dd([
            //     'berat_kotor' => $beratKotor,
            //     'tebumati_raw' => $tebumati,
            //     'tebumati_pct' => $tebumatiPct,
            //     'daun_pct' => $daunPct,
            //     'pucuk_pct' => $pucukPct,
            //     'sogolan_pct' => $sogolanPct,
            //     'siwlan_pct' => $siwlanPct,
            //     'tanah_etc' => $tanahEtc,
            //     'total_trash' => $totalTrash,
            //     'netto_trash' => $nettoTrash
            // ], $request);
            // // Insert trash record using DB
            DB::table('trash')->insert([
                'suratjalanno' => $request->no_surat_jalan,
                'companycode' => $request->companycode,
                'jenis' => $request->jenis,
                'pucuk' => $pucukPct,
                'daun_gulma' => $daunPct,
                'sogolan' => $sogolanPct,
                'siwilan' => $siwlanPct,
                'tebumati' => $tebumatiPct,
                'tanah_etc' => $tanahEtcRounded,
                'total' => $totalTrash,
                'netto_trash' => $nettoTrash,
            ]);

            DB::commit();

            return redirect()->route('pabrik.trash.index')
                ->with('success', 'Data trash berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $suratjalanno, $companycode, $jenis)
    {
        try {
            DB::beginTransaction();

            // Validation
            $request->validate([
                'companycode' => 'required|string',
                'no_surat_jalan' => 'required|string',
                'jenis' => 'required|in:manual,mesin',
                'berat_bersih' => 'required|string',
                'pucuk' => 'nullable|string',
                'daun_gulma' => 'nullable|string',
                'sogolan' => 'nullable|string',
                'siwilan' => 'nullable|string',
                'tebumati' => 'nullable|string',
                'tanah_etc' => 'nullable|string'
            ], [
                'companycode.required' => 'Company code wajib diisi',
                'no_surat_jalan.required' => 'Nomor surat jalan wajib diisi',
                'jenis.required' => 'Jenis trash wajib dipilih',
                'jenis.in' => 'Jenis trash harus manual atau mesin',
                'berat_bersih.required' => 'Berat bersih wajib diisi'
            ]);

            // Check if record exists
            $exists = DB::table('trash')
                ->where('suratjalanno', $suratjalanno)
                ->where('companycode', $companycode)
                ->where('jenis', $jenis)
                ->exists();

            if (!$exists) {
                return redirect()->back()
                    ->with('error', 'Data trash tidak ditemukan!');
            }

            // Convert comma format to decimal for calculation (SAMA SEPERTI STORE)
            $beratBersih = $this->parseDecimal($request->berat_bersih);
            $pucuk = $this->parseDecimal($request->pucuk ?? '0');
            $daunGulma = $this->parseDecimal($request->daun_gulma ?? '0');
            $sogolan = $this->parseDecimal($request->sogolan ?? '0');
            $siwilan = $this->parseDecimal($request->siwilan ?? '0');
            $tebumati = $this->parseDecimal($request->tebumati ?? '0');
            $tanahEtc = $this->parseDecimal($request->tanah_etc ?? '0');
            $beratKotor = $this->parseDecimal($request->berat_kotor);

            // Calculate percentages based on berat kotor (SAMA SEPERTI STORE)
            $tebumatiPct = $beratKotor > 0 ? round(($tebumati / $beratKotor) * 100, 2) : 0;
            $daunPct = $beratKotor > 0 ? round(($daunGulma / $beratKotor) * 100, 2) : 0;
            $pucukPct = $beratKotor > 0 ? round(($pucuk / $beratKotor) * 100, 2) : 0;
            $sogolanPct = $beratKotor > 0 ? round(($sogolan / $beratKotor) * 100, 2) : 0;
            $siwlanPct = $beratKotor > 0 ? round(($siwilan / $beratKotor) * 100, 2) : 0;
            $tanahEtcRounded = round($tanahEtc, 2);

            // Calculate totals dengan 3 desimal (SAMA SEPERTI STORE)
            $totalTrash = round($tebumatiPct + $daunPct + $pucukPct + $sogolanPct + $siwlanPct + $tanahEtcRounded, 3);
            $nettoTrash = round($totalTrash - 5, 3);

            // Update the record
            DB::table('trash')
                ->where('suratjalanno', $suratjalanno)
                ->where('companycode', $companycode)
                ->where('jenis', $jenis)
                ->update([
                    'suratjalanno' => $request->no_surat_jalan,
                    'companycode' => $request->companycode,
                    'jenis' => $request->jenis,
                    'pucuk' => $pucukPct,
                    'daun_gulma' => $daunPct,
                    'sogolan' => $sogolanPct,
                    'siwilan' => $siwlanPct,
                    'tebumati' => $tebumatiPct,
                    'tanah_etc' => $tanahEtcRounded,
                    'total' => $totalTrash,
                    'netto_trash' => $nettoTrash,
                ]);

            DB::commit();

            return redirect()->route('pabrik.trash.index')
                ->with('success', 'Data trash berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Tambah method destroy juga:
    public function destroy($suratjalanno, $companycode, $jenis)
    {
        try {
            DB::beginTransaction();

            $deleted = DB::table('trash')
                ->where('suratjalanno', $suratjalanno)
                ->where('companycode', $companycode)
                ->where('jenis', $jenis)
                ->delete();

            if ($deleted) {
                DB::commit();
                return redirect()->route('pabrik.trash.index')
                    ->with('success', 'Data trash berhasil dihapus!');
            } else {
                return redirect()->back()
                    ->with('error', 'Data trash tidak ditemukan!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Parse decimal from comma format (Indonesian) to dot format
     * Example: "45,680" -> 45.680
     */
    private function parseDecimal($value)
    {
        if (empty($value) || $value === '') {
            return 0;
        }

        // Remove any spaces and convert comma to dot
        $cleaned = str_replace([' ', ','], ['', '.'], $value);

        return (float) $cleaned;
    }
}
