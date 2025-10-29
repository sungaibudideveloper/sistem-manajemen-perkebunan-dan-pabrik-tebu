<?php

namespace App\Http\Controllers\Input;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// Models
use App\Models\RkhPanenHdr;
use App\Models\RkhPanenLst;
use App\Models\RkhPanenResult;
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

        // Build query
        $query = RkhPanenHdr::with(['mandor', 'kontraktors', 'results'])
            ->where('companycode', $companycode);

        // Apply search filter
        if ($search) {
            $query->where('rkhpanenno', 'like', '%' . $search . '%');
        }

        // Apply status filter
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }

        // Apply date filter
        if (empty($allDate)) {
            $dateToFilter = $filterDate ?: Carbon::today()->format('Y-m-d');
            $query->whereDate('rkhdate', $dateToFilter);
        }

        // Order and paginate
        $query->orderBy('rkhdate', 'desc')->orderBy('rkhpanenno', 'desc');
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

        // Load form data
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
            // Validate request
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
        
        // Get RKH Panen header
        $rkhPanen = RkhPanenHdr::with(['mandor', 'kontraktors', 'results'])
            ->where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->first();
        
        if (!$rkhPanen) {
            return redirect()->route('input.rkh-panen.index')
                ->with('error', 'Data RKH Panen tidak ditemukan');
        }

        // Section 1: Rencana (from kontraktors)
        $rencana = $rkhPanen->kontraktors;

        // Section 2: Hasil Kemarin (all results)
        $hasil = $rkhPanen->results;

        // Section 3: Petak Baru (haritebang = 1)
        $petakBaru = $rkhPanen->getPetakBaru();

        // Calculate totals
        $totals = [
            'rencana_netto' => $rkhPanen->getTotalRencanaNetto(),
            'rencana_ha' => $rkhPanen->getTotalRencanaHa(),
            'hasil_hc' => $rkhPanen->getTotalHasilHc(),
            'field_balance' => $rkhPanen->getTotalFieldBalanceTon(),
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
        
        $rkhPanen = RkhPanenHdr::with(['mandor', 'results'])
            ->where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->first();
        
        if (!$rkhPanen) {
            return redirect()->route('input.rkh-panen.index')
                ->with('error', 'Data RKH Panen tidak ditemukan');
        }

        // Get available plots from Section 1 (rkhpanenlst) + join with masterlist
        $availablePlots = $this->getAvailablePlotsForHasil($rkhpanenno, $companycode);

        return view('input.rkh-panen.edit-hasil', [
            'title' => 'Input Hasil Panen',
            'navbar' => 'Panen',
            'nav' => 'RKH Panen',
            'rkhPanen' => $rkhPanen,
            'availablePlots' => $availablePlots,
            'oldInput' => old(),
        ]);
    }

    public function updateHasil(Request $request, $rkhpanenno)
    {
        try {
            // Validate request
            $this->validateHasilRequest($request);

            $companycode = Session::get('companycode');
            
            DB::transaction(function () use ($request, $rkhpanenno, $companycode) {
                $this->updateHasilRecords($request, $rkhpanenno, $companycode);
            });

            return $this->handleUpdateHasilResponse($request, $rkhpanenno, true);

        } catch (\Exception $e) {
            Log::error("Update Hasil Panen Error: " . $e->getMessage());
            return $this->handleUpdateHasilResponse($request, $rkhpanenno, false, $e->getMessage());
        }
    }

    // =====================================
    // SECTION 5: DELETE
    // =====================================

    public function destroy($rkhpanenno)
    {
        $companycode = Session::get('companycode');
        
        try {
            DB::beginTransaction();
            
            // Delete results first
            RkhPanenResult::where('companycode', $companycode)
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

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    private function loadCreateFormData($companycode)
    {
        // Get mandor panen
        $mandorPanen = User::where('companycode', $companycode)
            ->whereIn('idjabatan', [5]) // Adjust based on your mandor panen jabatan
            ->where('isactive', 1)
            ->orderBy('name')
            ->get();

        // Get kontraktors using QB
        $kontraktors = DB::table('kontraktor')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->orderBy('namakontraktor')
            ->get();

        // Get plots with blok info for multiple select
        $plots = DB::table('plot')
            ->select('plot.plot', 'plot.blok', 'plot.luasarea', 'blok.namablok')
            ->join('blok', function($join) use ($companycode) {
                $join->on('plot.blok', '=', 'blok.blok')
                     ->where('blok.companycode', '=', $companycode);
            })
            ->where('plot.companycode', $companycode)
            ->orderBy('plot.blok')
            ->orderBy('plot.plot')
            ->get();

        return [
            'mandorPanen' => $mandorPanen,
            'kontraktors' => $kontraktors,
            'plots' => $plots,
        ];
    }

    private function getAvailablePlotsForHasil($rkhpanenno, $companycode)
    {
        // Get plots from rkhpanenlst (comma separated)
        $rkhLstData = DB::table('rkhpanenlst')
            ->where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->get();

        $availablePlots = [];

        foreach ($rkhLstData as $lst) {
            if (empty($lst->lokasiplot)) continue;

            $plots = array_map('trim', explode(',', $lst->lokasiplot));

            foreach ($plots as $plotCode) {
                // Get masterlist info for this plot
                $masterInfo = DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('plot', $plotCode)
                    ->where('isactive', 1)
                    ->first();

                if ($masterInfo) {
                    // Calculate hari tebang
                    $hariTebang = $this->calculateHariTebang(
                        $masterInfo->kodestatus,
                        $masterInfo->tanggalpanenpc,
                        $masterInfo->tanggalpanenrc1,
                        $masterInfo->tanggalpanenrc2,
                        $masterInfo->tanggalpanenrc3,
                        now()
                    );

                    $availablePlots[$plotCode] = [
                        'plot' => $plotCode,
                        'blok' => $masterInfo->blok,
                        'batchno' => $masterInfo->batchno,
                        'kodestatus' => $masterInfo->kodestatus,
                        'luasplot' => $masterInfo->batcharea,
                        'haritebang' => $hariTebang,
                    ];
                }
            }
        }

        return array_values($availablePlots); // Re-index array
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
            return 1; // First harvest
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
            'kontraktors.*.lokasiplot' => 'required|string', // Comma separated
        ]);
    }

    private function validateHasilRequest($request)
    {
        $request->validate([
            'hasil' => 'required|array|min:1',
            'hasil.*.blok' => 'required|string|max:2',
            'hasil.*.plot' => 'required|string|max:6',
            'hasil.*.batchno' => 'required|string|max:20',
            'hasil.*.luasplot' => 'required|numeric|min:0',
            'hasil.*.kodestatus' => 'required|in:PC,RC1,RC2,RC3',
            'hasil.*.haritebang' => 'required|integer|min:1',
            'hasil.*.stc' => 'required|numeric|min:0',
            'hasil.*.hc' => 'required|numeric|min:0',
            'hasil.*.fbrit' => 'nullable|integer|min:0',
            'hasil.*.ispremium' => 'nullable|boolean',
            'hasil.*.keterangan' => 'nullable|string',
        ]);
    }

    private function createRkhPanenRecord($request)
    {
        $companycode = Session::get('companycode');
        $tanggal = Carbon::parse($request->input('rkhdate'))->format('Y-m-d');

        // Generate unique RKH Panen No
        $rkhpanenno = $this->generateRkhPanenNo($tanggal, $companycode);

        // Insert header (NO TARGET FIELDS)
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

        // Insert kontraktors (COMMA-SEPARATED lokasiplot)
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
                'lokasiplot' => $kontraktor['lokasiplot'], // COMMA-SEPARATED
            ]);
        }

        return $rkhpanenno;
    }

    private function updateHasilRecords($request, $rkhpanenno, $companycode)
    {
        // Delete existing hasil
        RkhPanenResult::where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->delete();

        // Insert new hasil (NORMALIZED - 1 row per plot)
        foreach ($request->hasil as $hasil) {
            // Calculate BC and FBTON
            $bc = $hasil['stc'] - $hasil['hc'];
            $fbton = $bc * 1000;

            RkhPanenResult::create([
                'companycode' => $companycode,
                'rkhpanenno' => $rkhpanenno,
                'blok' => $hasil['blok'],
                'plot' => $hasil['plot'],
                'batchno' => $hasil['batchno'], // ⭐ FROM MASTERLIST
                'luasplot' => $hasil['luasplot'],
                'kodestatus' => $hasil['kodestatus'],
                'haritebang' => $hasil['haritebang'],
                'stc' => $hasil['stc'],
                'hc' => $hasil['hc'],
                'bc' => $bc,
                'fbton' => $fbton,
                'fbrit' => $hasil['fbrit'] ?? null,
                'ispremium' => isset($hasil['ispremium']) ? 1 : 0,
                'keterangan' => $hasil['keterangan'] ?? null,
            ]);

            // ⭐ UPDATE MASTERLIST - last panen date
            $fieldName = 'tanggalpanen' . strtolower($hasil['kodestatus']);
            
            DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', $hasil['plot'])
                ->update([
                    $fieldName => now(),
                    'lastactivity' => "PANEN {$hasil['kodestatus']} - " . now()->format('Y-m-d H:i:s'),
                ]);
        }

        // Update RKH status to COMPLETED
        RkhPanenHdr::where('companycode', $companycode)
            ->where('rkhpanenno', $rkhpanenno)
            ->update([
                'status' => 'COMPLETED',
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
                ->where('rkhpanenno', 'like', "RKHPN{$day}{$month}%" . $year)
                ->lockForUpdate()
                ->orderBy(DB::raw('CAST(SUBSTRING(rkhpanenno, 11, 2) AS UNSIGNED)'), 'desc')
                ->first();

            if ($lastRkh) {
                $lastNumber = (int)substr($lastRkh->rkhpanenno, 10, 2);
                $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '01';
            }

            return "RKHPN{$day}{$month}{$newNumber}{$year}";
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