<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Plot;

class PlottingController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Master',
            'nav' => 'plot',
            'routeName' => route('masterdata.plotting.index'),
        ]);
    }

    public function index(Request $request)
    {
        $title = "Daftar Plot";

        // Handle per page session
        if ($request->isMethod('post') && $request->has('perPage')) {
            $request->validate([
                'perPage' => 'required|integer|min:1|max:100',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);
        
        // Query dengan search dan join ke masterlist untuk check status
        $query = DB::table('plot as p')
            ->leftJoin('masterlist as m', function($join) {
                $join->on('p.plot', '=', 'm.plot')
                     ->on('p.companycode', '=', 'm.companycode');
            })
            ->where('p.companycode', '=', session('companycode'))
            ->select([
                'p.*',
                DB::raw('CASE WHEN m.plot IS NOT NULL THEN 1 ELSE 0 END as is_in_masterlist'),
                'm.isactive as masterlist_isactive'
            ]);

        // Masterlist filter
        if ($request->filled('masterlist_filter')) {
            $masterlistFilter = $request->masterlist_filter;
            if ($masterlistFilter === 'active') {
                $query->whereNotNull('m.plot')->where('m.isactive', 1);
            } elseif ($masterlistFilter === 'inactive') {
                $query->whereNull('m.plot');
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('p.plot', 'like', '%' . $searchTerm . '%')
                    ->orWhere('p.status', 'like', '%' . $searchTerm . '%')
                    ->orWhere('p.blok', 'like', '%' . $searchTerm . '%');
            });
        }

        $plotting = $query->orderByRaw("LEFT(p.plot, 1), CAST(SUBSTRING(p.plot, 2) AS UNSIGNED)")
            ->paginate($perPage);

        // Add row numbers
        foreach ($plotting as $index => $item) {
            $item->no = ($plotting->currentPage() - 1) * $plotting->perPage() + $index + 1;
        }

        // Status options
        $statusOptions = [
            'KTG' => 'Kategang',
            'RPL' => 'Replanting',
            'KBD' => 'Kebun Dewasa'
        ];

        return view('master.plotting.index', compact('plotting', 'perPage', 'title', 'statusOptions'));
    }

    public function handle(Request $request)
    {
        if ($request->has('perPage')) {
            return $this->index($request);
        }

        return $this->store($request);
    }

    protected function requestValidated(): array
    {
        return [
            'plot' => 'required|string|max:5|regex:/^[A-Z][0-9]{3,4}$/',
            'luasarea' => 'required|numeric|min:0|max:999.99|regex:/^\d{1,3}(\.\d{1,2})?$/',
            'jaraktanam' => 'required|integer|min:0|max:999',
            'status' => 'required|in:KTG,RPL,KBD',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->requestValidated());

        // Auto-set blok from first character of plot
        $blok = substr($request->plot, 0, 1);

        // Check if plot already exists
        $exists = DB::table('plot')
            ->where('plot', $request->plot)
            ->where('companycode', session('companycode'))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Plot dengan kode "' . $request->plot . '" sudah ada! Silakan gunakan kode yang berbeda.'
            ], 422);
        }

        // Ensure blok exists
        $blokExists = DB::table('blok')
            ->where('blok', $blok)
            ->where('companycode', session('companycode'))
            ->exists();

        if (!$blokExists) {
            DB::table('blok')->insert([
                'blok' => $blok,
                'companycode' => session('companycode'),
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
            ]);
        }

        DB::transaction(function () use ($request, $blok) {
            DB::table('plot')->insert([
                'plot' => strtoupper($request->plot),
                'blok' => $blok,
                'luasarea' => $request->luasarea,
                'jaraktanam' => $request->jaraktanam,
                'status' => $request->status,
                'companycode' => session('companycode'),
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Plot "' . strtoupper($request->plot) . '" berhasil ditambahkan!',
            'newData' => [
                'no' => 'NEW!',
                'plot' => strtoupper($request->plot),
                'blok' => $blok,
                'luasarea' => number_format($request->luasarea, 2),
                'jaraktanam' => $request->jaraktanam,
                'status' => $request->status,
                'companycode' => session('companycode'),
            ]
        ]);
    }

    public function update(Request $request, $plot, $companycode)
    {
        $request->validate($this->requestValidated());

        // Auto-set blok from first character of plot
        $blok = substr($request->plot, 0, 1);

        // Check if new plot code already exists (except current record)
        $existingPlot = DB::table('plot')
            ->where('plot', strtoupper($request->plot))
            ->where('companycode', $companycode)
            ->where('plot', '!=', $plot)
            ->exists();

        if ($existingPlot) {
            return redirect()->back()
                ->with('error', 'Plot dengan kode "' . strtoupper($request->plot) . '" sudah ada! Silakan gunakan kode yang berbeda.')
                ->withInput();
        }

        // Ensure blok exists
        $blokExists = DB::table('blok')
            ->where('blok', $blok)
            ->where('companycode', $companycode)
            ->exists();

        if (!$blokExists) {
            DB::table('blok')->insert([
                'blok' => $blok,
                'companycode' => $companycode,
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
            ]);
        }

        DB::transaction(function () use ($request, $plot, $companycode, $blok) {
            DB::table('plot')
                ->where('plot', $plot)
                ->where('companycode', $companycode)
                ->update([
                    'plot' => strtoupper($request->plot),
                    'blok' => $blok,
                    'luasarea' => $request->luasarea,
                    'jaraktanam' => $request->jaraktanam,
                    'status' => $request->status,
                    'updatedat' => now(),
                ]);
        });

        return redirect()->route('masterdata.plotting.index')
            ->with('success', 'Plot "' . strtoupper($request->plot) . '" berhasil diperbarui!');
    }

    public function destroy($plot, $companycode)
    {
        $deleted = DB::transaction(function () use ($plot, $companycode) {
            return DB::table('plot')
                ->where('plot', $plot)
                ->where('companycode', $companycode)
                ->delete();
        });

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Plot "' . $plot . '" berhasil dihapus!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Plot tidak ditemukan atau gagal dihapus.',
            ], 404);
        }
    }


    /**
     * Add plot to masterlist
     */
    public function addToMasterlist(Request $request)
    {
        $request->validate([
            'plot' => 'required|string|max:5',
            'companycode' => 'required|string|max:4'
        ]);

        try {
            $plot = $request->plot;
            $companycode = $request->companycode;

            // Verify plot exists
            $plotData = DB::table('plot')
                ->where('plot', $plot)
                ->where('companycode', $companycode)
                ->first();

            if (!$plotData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plot tidak ditemukan'
                ], 404);
            }

            // Check if already in masterlist
            $existsInMasterlist = DB::table('masterlist')
                ->where('plot', $plot)
                ->where('companycode', $companycode)
                ->exists();

            if ($existsInMasterlist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plot sudah ada di masterlist'
                ], 409);
            }

            // Insert to masterlist
            DB::table('masterlist')->insert([
                'companycode' => $companycode,
                'blok' => $plotData->blok,
                'plot' => $plot,
                'batchno' => null, // Biarkan NULL
                'batcharea' => $plotData->luasarea,
                'cyclecount' => 0,
                'isactive' => 1,
                // Other fields remain null
                'batchdate' => null,
                'tanggalulangtahun' => null,
                'kodevarietas' => null,
                'kodestatus' => null,
                'jaraktanam' => null,
                'lastactivity' => null,
                'tanggalpanenpc' => null,
                'tanggalpanenrc1' => null,
                'tanggalpanenrc2' => null,
                'tanggalpanenrc3' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Plot \"{$plot}\" berhasil ditambahkan ke masterlist"
            ]);

        } catch (\Exception $e) {
            \Log::error("Error adding plot to masterlist: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan ke masterlist: ' . $e->getMessage()
            ], 500);
        }
    }
}
