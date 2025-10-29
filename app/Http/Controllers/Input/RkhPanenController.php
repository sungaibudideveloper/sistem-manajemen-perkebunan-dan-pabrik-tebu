<?php

namespace App\Http\Controllers\Input;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// Models (hanya untuk yang punya single primary key)
use App\Models\RkhPanenHdr;
use App\Models\RkhPanenLst;
use App\Models\User;

class RkhPanenController extends Controller
{
    // =====================================
    // SECTION 1: INDEX & LIST
    // =====================================

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search = $request->input('search');
        $filterDate = $request->input('filter_date');
        $filterStatus = $request->input('filter_status');
        $allDate = $request->input('all_date');
        
        $companycode = Session::get('companycode');

        $query = DB::table('rkhpanenhdr as h')
            ->leftJoin('user as m', 'h.mandorpanenid', '=', 'm.userid')
            ->leftJoin(
                DB::raw('(SELECT rkhpanenno, companycode, 
                        SUM(rencananetto) as total_netto, 
                        SUM(rencanaha) as total_ha,
                        COUNT(*) as kontraktor_count
                        FROM rkhpanenlst 
                        GROUP BY rkhpanenno, companycode) as kontraktor_summary'),
                function($join) {
                    $join->on('h.rkhpanenno', '=', 'kontraktor_summary.rkhpanenno')
                        ->on('h.companycode', '=', 'kontraktor_summary.companycode');
                }
            )
            ->where('h.companycode', $companycode)
            ->select([
                'h.*',
                'm.name as mandor_name',
                'kontraktor_summary.total_netto',
                'kontraktor_summary.total_ha',
                'kontraktor_summary.kontraktor_count'
            ]);

        if ($search) {
            $query->where('h.rkhpanenno', 'like', '%' . $search . '%');
        }

        if ($filterStatus) {
            $query->where('h.status', $filterStatus);
        }

        if (empty($allDate)) {
            $dateToFilter = $filterDate ?: Carbon::today()->format('Y-m-d');
            $query->whereDate('h.rkhdate', $dateToFilter);
        }

        $query->orderBy('h.rkhdate', 'desc')->orderBy('h.rkhpanenno', 'desc');
        $rkhPanenData = $query->paginate($perPage);

        return view('input.rkh-panen.index', [
            'title' => 'RKH Panen',
            'navbar' => 'Panen',
            'nav' => 'RKH Panen',
            'perPage' => $perPage,
            'search' => $search,
            'filterDate' => $filterDate,
            'filterStatus' => $filterStatus,
            'allDate' => $allDate,
            'rkhPanenData' => $rkhPanenData,
        ]);
    }

    // =====================================
    // SECTION 2: CREATE RKH PANEN
    // =====================================

    public function create(Request $request)
    {
        $selectedDate = $request->input('date');
        
        if (!$selectedDate) {
            return redirect()->route('input.rkh-panen.index')
                ->with('error', 'Silakan pilih tanggal terlebih dahulu');
        }

        $targetDate = Carbon::parse($selectedDate);
        $companycode = Session::get('companycode');

        $formData = $this->loadCreateFormData($companycode);

        return view('input.rkh-panen.create', array_merge([
            'title' => 'Buat RKH Panen',
            'navbar' => 'Panen',
            'nav' => 'RKH Panen',
            'selectedDate' => $targetDate->format('Y-m-d'),
            'oldInput' => old(),
        ], $formData));
    }

    public function store(Request $request)
    {
        try {
            $this->validateRkhPanenRequest($request);

            $rkhpanenno = null;
            
            DB::transaction(function () use ($request, &$rkhpanenno) {
                $rkhpanenno = $this->createRkhPanenRecord($request);
            });

            return $this->handleStoreResponse($request, $rkhpanenno, true);

        } catch (\Exception $e) {
            Log::error("Store RKH Panen Error: " . $e->getMessage());
            return $this->handleStoreResponse($request, null, false, $e->getMessage());
        }
    }

    // =====================================
    // SECTION 3: SHOW / REPORT
    // =====================================

    public function show($rkhpanenno)
    {
        $companycode = Session::get('companycode');
        
        $rkhPanen = RkhPanenHdr::where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->first();
        
        if (!$rkhPanen) {
            return redirect()->route('input.rkh-panen.index')
                ->with('error', 'Data RKH Panen tidak ditemukan');
        }

        $rencana = DB::table('rkhpanenlst')
            ->where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->get();

        $hasil = DB::table('rkhpanenresult')
            ->where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->whereNotNull('hc')
            ->get();

        $petakBaru = DB::table('rkhpanenresult')
            ->where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->where('haritebang', 1)
            ->get();

        $totals = [
            'rencana_netto' => DB::table('rkhpanenlst')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->sum('rencananetto'),
            'rencana_ha' => DB::table('rkhpanenlst')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->sum('rencanaha'),
            'hasil_hc' => DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->sum('hc'),
            'hasil_stc' => DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->sum('stc'),
            'hasil_bc' => DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->sum('bc'),
            'field_balance_rit' => DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->sum('fbrit'),
            'field_balance_ton' => DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->sum('fbton'),
        ];

        return view('input.rkh-panen.show', [
            'title' => 'Laporan RKH Panen',
            'navbar' => 'Panen',
            'nav' => 'RKH Panen',
            'rkhPanen' => $rkhPanen,
            'rencana' => $rencana,
            'hasil' => $hasil,
            'petakBaru' => $petakBaru,
            'totals' => $totals,
        ]);
    }

    // =====================================
    // SECTION 4: EDIT HASIL
    // =====================================

    public function editHasil($rkhpanenno)
    {
        $companycode = Session::get('companycode');
        
        $rkhPanen = RkhPanenHdr::with(['mandor'])
            ->where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->first();
        
        if (!$rkhPanen) {
            return redirect()->route('input.rkh-panen.index')
                ->with('error', 'Data RKH Panen tidak ditemukan');
        }

        // Get pre-generated result rows (Query Builder)
        $hasilRows = DB::table('rkhpanenresult')
            ->where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->orderBy('blok')
            ->orderBy('plot')
            ->get();

        return view('input.rkh-panen.edit-hasil', [
            'title' => 'Input Hasil Panen',
            'navbar' => 'Panen',
            'nav' => 'RKH Panen',
            'rkhPanen' => $rkhPanen,
            'hasilRows' => $hasilRows,
            'oldInput' => old(),
        ]);
    }

    public function updateHasil(Request $request, $rkhpanenno)
    {
        try {
            $this->validateHasilRequest($request);

            $companycode = Session::get('companycode');
            
            DB::transaction(function () use ($request, $rkhpanenno, $companycode) {
                $this->updateHasilRecords($request, $rkhpanenno, $companycode);
            });

            return $this->handleUpdateHasilResponse($request, $rkhpanenno, true);

        } catch (\Exception $e) {
            Log::error("Update Hasil Panen Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->handleUpdateHasilResponse($request, $rkhpanenno, false, $e->getMessage());
        }
    }

    // =====================================
    // SECTION 5: DELETE & COMPLETE
    // =====================================

    public function destroy($rkhpanenno)
    {
        $companycode = Session::get('companycode');
        
        try {
            DB::beginTransaction();
            
            // Delete results (Query Builder)
            DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->delete();
            
            // Delete kontraktor list
            RkhPanenLst::where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->delete();
            
            // Delete header
            $deleted = RkhPanenHdr::where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->delete();
            
            if ($deleted) {
                DB::commit();
                return response()->json([
                    'success' => true, 
                    'message' => 'RKH Panen dan semua data terkait berhasil dihapus'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'RKH Panen tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Delete RKH Panen Error: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus RKH Panen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete RKH Panen (ubah status jadi COMPLETED)
     */
    public function complete($rkhpanenno)
    {
        $companycode = Session::get('companycode');
        
        try {
            $rkhPanen = RkhPanenHdr::where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->first();

            if (!$rkhPanen) {
                return response()->json([
                    'success' => false,
                    'message' => 'RKH Panen tidak ditemukan'
                ], 404);
            }

            // Update status jadi COMPLETED
            $rkhPanen->update([
                'status' => 'COMPLETED',
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'RKH Panen berhasil diselesaikan'
            ]);

        } catch (\Exception $e) {
            Log::error("Complete RKH Panen Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan RKH Panen: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    private function loadCreateFormData($companycode)
    {
        $mandorPanen = User::where('companycode', $companycode)
            ->where('idjabatan', 5)
            ->where('isactive', 1)
            ->orderBy('name')
            ->get();

        $kontraktors = DB::table('kontraktor')
            ->select('id as kontraktorid', 'namakontraktor')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->orderBy('namakontraktor')
            ->get();

        $plots = DB::table('masterlist')
            ->select(
                'masterlist.plot',
                'masterlist.blok',
                'masterlist.activebatchno',
                'batch.lifecyclestatus',
                'batch.batcharea'
            )
            ->join('batch', 'masterlist.activebatchno', '=', 'batch.batchno')
            ->where('masterlist.companycode', $companycode)
            ->where('masterlist.isactive', 1)
            ->where('batch.isactive', 1)
            ->whereNotNull('masterlist.activebatchno')
            ->orderBy('masterlist.blok')
            ->orderBy('masterlist.plot')
            ->get()
            ->groupBy('blok');

        return [
            'mandorPanen' => $mandorPanen,
            'kontraktors' => $kontraktors,
            'plots' => $plots,
        ];
    }

    private function calculateHariTebang($kodestatus, $tanggalPC, $tanggalRC1, $tanggalRC2, $tanggalRC3, $today)
    {
        $lastPanenDate = match($kodestatus) {
            'PC' => $tanggalPC,
            'RC1' => $tanggalRC1,
            'RC2' => $tanggalRC2,
            'RC3' => $tanggalRC3,
            default => null,
        };
        
        if (!$lastPanenDate) {
            return 1;
        }
        
        $lastDate = Carbon::parse($lastPanenDate);
        $currentDate = Carbon::parse($today);
        
        return $lastDate->diffInDays($currentDate) + 1;
    }

    private function validateRkhPanenRequest($request)
    {
        $request->validate([
            'rkhdate' => 'required|date',
            'mandorpanenid' => 'required|exists:user,userid',
            'keterangan' => 'nullable|string|max:500',
            
            'kontraktors' => 'required|array|min:1',
            'kontraktors.*.kontraktorid' => 'required',
            'kontraktors.*.jenispanen' => 'required|in:MANUAL,SEMI_MEKANIS,MEKANIS',
            'kontraktors.*.rencananetto' => 'nullable|numeric|min:0',
            'kontraktors.*.rencanaha' => 'nullable|numeric|min:0',
            'kontraktors.*.estimasiyph' => 'required|integer|min:10|max:200',
            'kontraktors.*.tenagatebangjumlah' => 'nullable|integer|min:0',
            'kontraktors.*.tenagamuatjumlah' => 'nullable|integer|min:0',
            'kontraktors.*.armadawl' => 'nullable|integer|min:0',
            'kontraktors.*.armadaumum' => 'nullable|integer|min:0',
            'kontraktors.*.lokasiplot' => 'required|string',
        ]);
    }

    private function validateHasilRequest($request)
    {
        $request->validate([
            'hasil' => 'required|array|min:1',
            'hasil.*.plot' => 'required|string|max:5',
            'hasil.*.hc' => 'required|numeric|min:0.01',
            'hasil.*.fbrit' => 'nullable|integer|min:0',
            'hasil.*.fbton' => 'nullable|numeric|min:0',
            'hasil.*.ispremium' => 'nullable',
            'hasil.*.keterangan' => 'nullable|string|max:200',
        ]);
    }

    private function createRkhPanenRecord($request)
    {
        $companycode = Session::get('companycode');
        $tanggal = Carbon::parse($request->input('rkhdate'))->format('Y-m-d');

        $rkhpanenno = $this->generateRkhPanenNo($tanggal, $companycode);

        // Insert header
        RkhPanenHdr::create([
            'companycode' => $companycode,
            'rkhpanenno' => $rkhpanenno,
            'rkhdate' => $tanggal,
            'mandorpanenid' => $request->input('mandorpanenid'),
            'keterangan' => $request->input('keterangan'),
            'status' => 'DRAFT',
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
        ]);

        // Insert kontraktors
        foreach ($request->kontraktors as $kontraktor) {
            RkhPanenLst::create([
                'companycode' => $companycode,
                'rkhpanenno' => $rkhpanenno,
                'kontraktorid' => $kontraktor['kontraktorid'],
                'jenispanen' => $kontraktor['jenispanen'],
                'rencananetto' => $kontraktor['rencananetto'] ?? null,
                'rencanaha' => $kontraktor['rencanaha'] ?? null,
                'estimasiyph' => $kontraktor['estimasiyph'],
                'tenagatebangjumlah' => $kontraktor['tenagatebangjumlah'] ?? null,
                'tenagamuatjumlah' => $kontraktor['tenagamuatjumlah'] ?? null,
                'armadawl' => $kontraktor['armadawl'] ?? 0,
                'armadaumum' => $kontraktor['armadaumum'] ?? 0,
                'mesinpanen' => isset($kontraktor['mesinpanen']) ? 1 : 0,
                'grabloader' => isset($kontraktor['grabloader']) ? 1 : 0,
                'lokasiplot' => $kontraktor['lokasiplot'],
            ]);

            $this->generateEmptyResultRows(
                $rkhpanenno,
                $companycode,
                $kontraktor['lokasiplot'],
                $tanggal
            );
        }

        return $rkhpanenno;
    }

    private function generateEmptyResultRows($rkhpanenno, $companycode, $lokasiplotStr, $rkhdate)
    {
        $plotArray = array_map('trim', explode(',', $lokasiplotStr));

        foreach ($plotArray as $plotCode) {
            $batchInfo = DB::table('masterlist')
                ->join('batch', 'masterlist.activebatchno', '=', 'batch.batchno')
                ->where('masterlist.companycode', $companycode)
                ->where('masterlist.plot', $plotCode)
                ->where('masterlist.isactive', 1)
                ->select(
                    'masterlist.blok',
                    'batch.batchno',
                    'batch.batcharea',
                    'batch.lifecyclestatus',
                    'batch.tanggalpanenpc',
                    'batch.tanggalpanenrc1',
                    'batch.tanggalpanenrc2',
                    'batch.tanggalpanenrc3'
                )
                ->first();

            if (!$batchInfo) {
                Log::warning("Plot {$plotCode} tidak ditemukan");
                continue;
            }

            $hariTebang = $this->calculateHariTebang(
                $batchInfo->lifecyclestatus,
                $batchInfo->tanggalpanenpc,
                $batchInfo->tanggalpanenrc1,
                $batchInfo->tanggalpanenrc2,
                $batchInfo->tanggalpanenrc3,
                $rkhdate
            );

            // Calculate STC: Luas batch - Total HC yang sudah dipanen untuk lifecycle ini
            $totalHcDipanen = DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('plot', $plotCode)
                ->where('kodestatus', $batchInfo->lifecyclestatus)
                ->whereNotNull('hc')
                ->where('hc', '>', 0)
                ->sum('hc');

            $stcValue = max(0, $batchInfo->batcharea - $totalHcDipanen);

            // Insert dengan Query Builder
            DB::table('rkhpanenresult')->insert([
                'companycode' => $companycode,
                'rkhpanenno' => $rkhpanenno,
                'blok' => $batchInfo->blok,
                'plot' => $plotCode,
                'luasplot' => $batchInfo->batcharea,
                'kodestatus' => $batchInfo->lifecyclestatus,
                'haritebang' => $hariTebang,
                'stc' => $stcValue,
                'hc' => null,
                'bc' => null,
                'fbrit' => 0,
                'fbton' => 0,
                'ispremium' => 0,
                'keterangan' => null,
            ]);
        }
    }

    private function updateHasilRecords($request, $rkhpanenno, $companycode)
    {
        $hasilData = $request->input('hasil', []);
        
        if (empty($hasilData)) {
            throw new \Exception('Data hasil kosong');
        }

        foreach ($hasilData as $hasil) {
            if (!is_array($hasil) || !isset($hasil['plot'])) {
                continue;
            }

            $plotCode = $hasil['plot'];
            
            // Get existing row
            $existingRow = DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->where('plot', $plotCode)
                ->first();

            if (!$existingRow) {
                Log::warning("Plot {$plotCode} tidak ditemukan");
                continue;
            }

            // STC tetap dari database
            $stcOriginal = floatval($existingRow->stc);
            $hc = floatval($hasil['hc'] ?? 0);
            
            // Validasi HC tidak boleh lebih dari STC
            if ($hc > $stcOriginal) {
                $hc = $stcOriginal;
            }
            
            $bc = max(0, $stcOriginal - $hc);
            $fbrit = intval($hasil['fbrit'] ?? 0);
            $fbton = floatval($hasil['fbton'] ?? ($fbrit * 5));
            $ispremium = (isset($hasil['ispremium']) && $hasil['ispremium'] == '1') ? 1 : 0;

            // Update dengan Query Builder
            DB::table('rkhpanenresult')
                ->where('companycode', $companycode)
                ->where('rkhpanenno', $rkhpanenno)
                ->where('plot', $plotCode)
                ->update([
                    'hc' => $hc,
                    'bc' => $bc,
                    'fbrit' => $fbrit,
                    'fbton' => $fbton,
                    'ispremium' => $ispremium,
                    'keterangan' => $hasil['keterangan'] ?? null,
                ]);

            // Update batch jika HC > 0
            if ($hc > 0) {
                $batchno = DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('plot', $plotCode)
                    ->value('activebatchno');

                if ($batchno) {
                    $kodestatus = $existingRow->kodestatus;
                    $fieldName = 'tanggalpanen' . strtolower($kodestatus);
                    
                    DB::table('batch')
                        ->where('batchno', $batchno)
                        ->update([
                            $fieldName => now(),
                            'lastactivity' => "PANEN {$kodestatus} - " . now()->format('Y-m-d H:i:s'),
                        ]);
                }
            }
        }

        // Update timestamp saja, JANGAN ubah status
        RkhPanenHdr::where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->update([
                'updateby' => Auth::user()->userid,
                'updatedat' => now(),
            ]);
    }

    private function generateRkhPanenNo($date, $companycode)
    {
        $carbonDate = Carbon::parse($date);
        $day = $carbonDate->format('d');
        $month = $carbonDate->format('m');
        $year = $carbonDate->format('y');

        return DB::transaction(function () use ($carbonDate, $day, $month, $year, $companycode) {
            $lastRkh = RkhPanenHdr::where('companycode', $companycode)
                ->whereDate('rkhdate', $carbonDate)
                ->where('rkhpanenno', 'like', "RKHP{$day}{$month}%" . $year)
                ->lockForUpdate()
                ->orderBy(DB::raw('CAST(SUBSTRING(rkhpanenno, 11, 2) AS UNSIGNED)'), 'desc')
                ->first();

            if ($lastRkh) {
                $lastNumber = (int)substr($lastRkh->rkhpanenno, 10, 2);
                $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '01';
            }

            return "RKHP{$day}{$month}{$newNumber}{$year}";
        });
    }

    private function handleStoreResponse($request, $rkhpanenno, $success, $errorMessage = null)
    {
        if ($request->ajax() || $request->wantsJson()) {
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "RKH Panen berhasil dibuat: <strong>{$rkhpanenno}</strong>",
                    'rkhpanenno' => $rkhpanenno,
                    'redirect_url' => route('input.rkh-panen.index')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $errorMessage
                ], 500);
            }
        }

        if ($success) {
            return redirect()->route('input.rkh-panen.index')
                ->with('success', 'RKH Panen berhasil dibuat!');
        } else {
            return redirect()->back()
                ->withInput($request->all())
                ->with('error', 'Terjadi kesalahan: ' . $errorMessage);
        }
    }

    private function handleUpdateHasilResponse($request, $rkhpanenno, $success, $errorMessage = null)
    {
        if ($request->ajax() || $request->wantsJson()) {
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "Hasil panen berhasil diinput!",
                    'redirect_url' => route('input.rkh-panen.show', $rkhpanenno)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $errorMessage
                ], 500);
            }
        }

        if ($success) {
            return redirect()->route('input.rkh-panen.show', $rkhpanenno)
                ->with('success', 'Hasil panen berhasil diinput!');
        } else {
            return redirect()->back()
                ->withInput($request->all())
                ->with('error', 'Terjadi kesalahan: ' . $errorMessage);
        }
    }
}