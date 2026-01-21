<?php

namespace App\Http\Controllers\Transaction;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class AgronomiController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Input Data',
            'nav' => 'Agronomi',
            'routeName' => route('transaction.agronomi.index'),
        ]);
    }

    protected function requestValidated(): array
    {
        return [
            'nosample' => 'required',
            'companycode' => 'required',
            'blok' => 'required',
            'plot' => 'required',
            // 'idblokplot' => 'required|exists:mapping,idblokplot',
            'varietas' => 'required',
            'kat' => 'required',
            'tanggaltanam' => 'required',
            'tanggalpengamatan' => 'required',
            'lists.*.nourut' => 'required',
            'lists.*.jumlahbatang' => 'required',
            'lists.*.pan_gap' => 'required',
            'lists.*.ph_tanah' => 'required|numeric',
            'lists.*.ktk_gulma' => 'required',
            'lists.*.t_primer' => 'required',
            'lists.*.t_sekunder' => 'required',
            'lists.*.t_tersier' => 'required',
            'lists.*.t_kuarter' => 'required',
            'lists.*.d_primer' => 'required',
            'lists.*.d_sekunder' => 'required',
            'lists.*.d_tersier' => 'required',
            'lists.*.d_kuarter' => 'required',
        ];
    }
    public function index(Request $request)
    {
        $title = "Daftar Agronomi";
        $search = $request->input('search', '');

        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $userid = Auth::user()->userid;
        $companycode = DB::table('usercompany')
            ->where('userid', $userid)
            ->value('companycode');
        $companyArray = $companycode ? explode(',', $companycode) : [];

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $agronomi = DB::table('agrohdr')
            ->join('company', 'agrohdr.companycode', '=', 'company.companycode')
            ->where('agrohdr.companycode', '=', session('companycode'))
            ->where('agrohdr.closingperiode', '=', 'F')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('agrohdr.tanggalpengamatan', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('agrohdr.tanggalpengamatan', '<=', $endDate);
            });

        if (!empty($search)) {
            $agronomi->where(function ($query) use ($search) {
                $query->where('agrohdr.nosample', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.varietas', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.plot', 'like', '%' . $search . '%')
                    ->orWhere('agrohdr.kat', 'like', '%' . $search . '%');
            });

        }
        $agronomi = $agronomi->select(
            'agrohdr.*',
            'company.name as nama_comp'
        )
            ->orderBy('agrohdr.createdat', 'desc')
            ->paginate($perPage);

        foreach ($agronomi as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
        }

        foreach ($agronomi as $index => $item) {
            $item->no = ($agronomi->currentPage() - 1) * $agronomi->perPage() + $index + 1;
        }

        if ($request->ajax()) {
            return view('transaction.agronomi.index', compact('agronomi', 'perPage', 'startDate', 'endDate', 'title', 'search'));
        }

        return view('transaction.agronomi.index', compact('agronomi', 'perPage', 'startDate', 'endDate', 'title', 'search'));
    }

    public function handle(Request $request)
    {
        if ($request->has('filter') || $request->has('perPage')) {
            return $this->index($request);
        }

        return $this->store($request);
    }

    public function create()
    {
        $title = "Create Data";
        // $mapping = DB::table('mapping')
        //     ->where('companycode', '=', session('companycode'))
        //     ->orderByRaw("CAST(idblokplot AS UNSIGNED)")
        //     ->get();
        $method = 'POST';
        $url = route('transaction.agronomi.handle');
        $buttonSubmit = 'Create';
        return view('transaction.agronomi.form', compact('buttonSubmit', 'title', 'method', 'url'));
    }

    public function getBlokbyField(Request $request)
    {
        // $idblokplot = $request->input('idblokplot');
        $plot = $request->input('plot');
        $blok = DB::table('masterlist')->where('plot', $plot)
            ->where('companycode', session('companycode'))
            ->where('isactive', 1)
            ->first();

        if ($blok) {
            return response()->json([
                // 'idblokplot' => $blok->idblokplot,
                'blok' => $blok->blok,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function getVarietasandKategori(Request $request)
    {
        // $idblokplot = $request->input('idblokplot');
        $plot = $request->input('plot');
        $batch = DB::table('batch')->where('plot', $plot)
            ->where('companycode', session('companycode'))
            ->where('isactive', 1)
            ->first();

        if ($batch) {
            return response()->json([
                'varietas' => $batch->kodevarietas,
                'kat' => $batch->lifecyclestatus,
                'tanggaltanam' => $batch->tanggalulangtahun,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());

        $existsInHeader = DB::table('agrohdr')->where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggalpengamatan', $request->tanggalpengamatan)
            ->exists();

        $existsInLists = DB::table('agrolst')->where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggalpengamatan', $request->tanggalpengamatan)
            ->exists();

        if ($existsInHeader || $existsInLists) {
            return back()->with([
                'success1' => 'Data sudah ada di salah satu tabel, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        DB::beginTransaction();

        try {

            DB::table('agrohdr')->insert([
                'nosample' => $validated['nosample'],
                'companycode' => $validated['companycode'],
                'blok' => $validated['blok'],
                'plot' => $validated['plot'],
                // 'idblokplot' => $validated['idblokplot'],
                'varietas' => $validated['varietas'],
                'kat' => $validated['kat'],
                'tanggaltanam' => $validated['tanggaltanam'],
                'tanggalpengamatan' => $validated['tanggalpengamatan'],
                'inputby' => Auth::user()->userid,
                'createdat' => now(),
                'updatedat' => now(),
            ]);

            $totalPerGerminasi = 0;
            $totalPerGulma = 0;
            $count = count($validated['lists']);

            foreach ($validated['lists'] as $list) {
                $per_gap = $list['pan_gap'] / 1000;
                $per_germinasi = 1 - $per_gap;
                $per_gulma = $list['ktk_gulma'] ? $list['ktk_gulma'] / 16 : 0;

                DB::table('agrolst')->insert([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $list['jumlahbatang'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $per_gap,
                    'per_germinasi' => $per_germinasi,
                    'ph_tanah' => $list['ph_tanah'],
                    'populasi' => round($list['jumlahbatang'] / 10),
                    'ktk_gulma' => $list['ktk_gulma'],
                    'per_gulma' => $per_gulma,
                    't_primer' => $list['t_primer'],
                    't_sekunder' => $list['t_sekunder'],
                    't_tersier' => $list['t_tersier'],
                    't_kuarter' => $list['t_kuarter'],
                    'd_primer' => $list['d_primer'],
                    'd_sekunder' => $list['d_sekunder'],
                    'd_tersier' => $list['d_tersier'],
                    'd_kuarter' => $list['d_kuarter'],
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                    'updatedat' => now(),
                ]);

                $totalPerGerminasi += $per_germinasi;
                $totalPerGulma += $per_gulma;
            }

            $avgPerGerminasi = $totalPerGerminasi / $count;
            $avgPerGulma = $totalPerGulma / $count;

            $umurTanam = Carbon::parse($validated['tanggaltanam'])->diffInMonths(Carbon::now());

            if ($avgPerGerminasi < 0.9 && $umurTanam == 1.0 || $avgPerGulma > 0.25) {
                Notification::createForAgronomi([
                    'plot' => $validated['plot'],
                    'companycode' => $validated['companycode'],
                    'condition' => [
                        'germinasi' => $avgPerGerminasi,
                        'gulma' => $avgPerGulma,
                        'umur' => $umurTanam,
                    ]
                ]);
            }

            DB::commit();
            return redirect()->back()
                ->with('success1', 'Data created successfully.');
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->route('transaction.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show($nosample, $companycode, $tanggalpengamatan)
    {

        $agronomi = DB::table('agrohdr')
            ->where('companycode', '=', session('companycode'))
            ->where('nosample', $nosample)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->first();

        if (!$agronomi) {
            abort(404, 'Agronomi header not found');
        }

        $agronomiLists = DB::table('agrolst')
            ->leftJoin('agrohdr', function ($join) {
                $join->on('agrolst.nosample', '=', 'agrohdr.nosample')
                    ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                    ->whereColumn('agrolst.tanggalpengamatan', '=', 'agrohdr.tanggalpengamatan');
            })
            ->leftJoin('company', function ($join) {
                $join->on('agrohdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agrohdr.blok', '=', 'blok.blok')
                    ->whereColumn('agrohdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('agrohdr.plot', '=', 'plot.plot')
                    ->whereColumn('agrohdr.companycode', '=', 'plot.companycode');
            })
            ->select(
                'agrolst.*',
                'agrohdr.varietas',
                'agrohdr.kat',
                'agrohdr.tanggaltanam',
                'company.name as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
                'plot.jaraktanam',
            )
            ->where('agrolst.nosample', $nosample)
            ->where('agrolst.companycode', $companycode)
            ->where('agrolst.tanggalpengamatan', $tanggalpengamatan)
            ->orderBy('agrolst.nourut', 'asc')
            ->get();

        $now = Carbon::now();

        $agronomiLists = $agronomiLists->map(function ($item) use ($now) {
            $tgl_tanam = Carbon::parse($item->tanggaltanam);
            $item->umur_tanam = $tgl_tanam->diffInMonths($now);
            return $item;
        });

        foreach ($agronomiLists as $index => $item) {
            $item->no = $index + 1;
        }

        return response()->json($agronomiLists);
    }

    public function edit($nosample, $companycode, $tanggalpengamatan)
    {
        $title = 'Edit Data';
        $header = DB::table('agrohdr')->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->first();
        $lists = DB::table('agrolst')
            ->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggalpengamatan', $tanggalpengamatan)
            ->get();
        $list = $lists->first();
        $header->lists = $lists;

        $company = DB::table('company')->get();
        $mapping = DB::table('mapping')->get();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('transaction.agronomi.update', ['nosample' => $nosample, 'companycode' => $companycode, 'tanggalpengamatan' => $tanggalpengamatan]);

        if ($header->status === "Posted") {
            return redirect()->route('transaction.agronomi.index')->with('success1', 'Data telah di posting, tidak dapat mengakses edit.');
        }

        return view('transaction.agronomi.form', compact('buttonSubmit', 'header', 'list', 'company', 'mapping', 'title', 'method', 'url'));
    }

    public function update(Request $request, $nosample, $companycode, $tanggalpengamatan)
    {
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {
            DB::table('agrohdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->update([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'blok' => $validated['blok'],
                    'plot' => $validated['plot'],
                    // 'idblokplot' => $validated['idblokplot'],
                    'varietas' => $validated['varietas'],
                    'kat' => $validated['kat'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'updatedat' => now(),
                ]);

            $lists = DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan);

            $saved = DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->first();

            $createdAt = $saved->createdat;
            $userInput = $saved->inputby;

            $lists->delete();

            foreach ($validated['lists'] as $list) {
                $data = [
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'kat' => $validated['kat'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $list['jumlahbatang'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $list['pan_gap'] / 1000,
                    'per_germinasi' => 1 - ($list['pan_gap'] / 1000),
                    'ph_tanah' => $list['ph_tanah'],
                    'populasi' => $list['jumlahbatang'] / 10,
                    'ktk_gulma' => $list['ktk_gulma'],
                    'per_gulma' => $list['ktk_gulma'] ? $list['ktk_gulma'] / 16 : 0,
                    't_primer' => $list['t_primer'],
                    't_sekunder' => $list['t_sekunder'],
                    't_tersier' => $list['t_tersier'],
                    't_kuarter' => $list['t_kuarter'],
                    'd_primer' => $list['d_primer'],
                    'd_sekunder' => $list['d_sekunder'],
                    'd_tersier' => $list['d_tersier'],
                    'd_kuarter' => $list['d_kuarter'],
                    'inputby' => $userInput,
                    'createdat' => $createdAt,
                    'updatedat' => now(),
                ];
                DB::table('agrolst')
                    ->where('nosample', $nosample)
                    ->where('companycode', $companycode)
                    ->where('tanggalpengamatan', $tanggalpengamatan)
                    ->where('nourut', $list['nourut'])
                    ->insert($data);
            }

            DB::commit();

            return redirect()->route('transaction.agronomi.index')
                ->with('success1', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('transaction.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($nosample, $companycode, $tanggalpengamatan)
    {
        DB::transaction(function () use ($nosample, $companycode, $tanggalpengamatan) {
            DB::table('agrohdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();
            DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggalpengamatan', $tanggalpengamatan)
                ->delete();
        });
        return redirect()->route('transaction.agronomi.index')
            ->with('success', 'Data deleted successfully.');
    }

    public function excel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'search' => 'nullable|string'
        ]);

        $query = DB::table('agrolst')
            ->leftJoin('agrohdr', function ($join) {
                $join->on('agrolst.nosample', '=', 'agrohdr.nosample')
                    ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                    ->whereColumn('agrolst.tanggalpengamatan', '=', 'agrohdr.tanggalpengamatan');
            })
            ->leftJoin('company', function ($join) {
                $join->on('agrohdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agrohdr.blok', '=', 'blok.blok')
                    ->whereColumn('agrohdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('batch', function ($join) {
                $join->on('agrohdr.plot', '=', 'batch.plot')
                    ->whereColumn('agrohdr.companycode', '=', 'batch.companycode');
            })
            ->where('agrolst.companycode', session('companycode'))
            ->where('agrohdr.companycode', session('companycode'))
            ->where('agrohdr.status', '=', 'Posted')
            ->where('agrolst.status', '=', 'Posted')
            ->select(
                'agrolst.*',
                'agrohdr.varietas',
                'agrohdr.kat',
                'agrohdr.tanggaltanam',
                'company.name as compName',
                'blok.blok as blokName',
                'batch.plot as plotName',
                'batch.batcharea as luasarea',
                'batch.pkp as jaraktanam',
            )
            ->orderBy('agrohdr.tanggalpengamatan', 'desc');

        if ($startDate) {
            $query->whereDate('agrohdr.tanggalpengamatan', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('agrohdr.tanggalpengamatan', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('agrolst.nosample', 'like', "%{$search}%")
                    ->orWhere('plotting.plot', 'like', "%{$search}%")
                    ->orWhere('agrohdr.varietas', 'like', "%{$search}%")
                    ->orWhere('agrohdr.kat', 'like', "%{$search}%");
            });
        }

        $now = Carbon::now();

        // Tentukan nama file
        if ($startDate && $endDate) {
            $filename = "AgronomiReport_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $filename = "AgronomiReport.xlsx";
        }

        // Buat direktori temp jika belum ada
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir . '/' . $filename;

        // Buat writer dengan Spout dan set temp folder
        $writer = WriterEntityFactory::createXLSXWriter();

        // SET TEMP FOLDER - INI YANG PENTING!
        $writer->setTempFolder($tempDir);

        $writer->openToFile($tempFile);

        // Style untuk header (bold)
        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->build();

        // Buat header row
        $headerCells = [
            WriterEntityFactory::createCell('No. Sample'),
            WriterEntityFactory::createCell('Kebun'),
            WriterEntityFactory::createCell('Blok'),
            WriterEntityFactory::createCell('Plot'),
            WriterEntityFactory::createCell('Luas'),
            WriterEntityFactory::createCell('Varietas'),
            WriterEntityFactory::createCell('Kategori'),
            WriterEntityFactory::createCell('Tanggal Tanam'),
            WriterEntityFactory::createCell('Umur Tanam'),
            WriterEntityFactory::createCell('Jarak Tanam'),
            WriterEntityFactory::createCell('Tanggal Pengamatan'),
            WriterEntityFactory::createCell('Bulan Pengamatan'),
            WriterEntityFactory::createCell('No. Urut'),
            WriterEntityFactory::createCell('Jumlah Batang'),
            WriterEntityFactory::createCell('Panjang GAP'),
            WriterEntityFactory::createCell('%GAP'),
            WriterEntityFactory::createCell('%Germinasi'),
            WriterEntityFactory::createCell('pH Tanah'),
            WriterEntityFactory::createCell('Populasi'),
            WriterEntityFactory::createCell('Kotak Gulma'),
            WriterEntityFactory::createCell('%Penutupan Gulma'),
            WriterEntityFactory::createCell('Tinggi Primer'),
            WriterEntityFactory::createCell('Tinggi Sekunder'),
            WriterEntityFactory::createCell('Tinggi Tersier'),
            WriterEntityFactory::createCell('Tinggi Kuarter'),
            WriterEntityFactory::createCell('Diameter Primer'),
            WriterEntityFactory::createCell('Diameter Sekunder'),
            WriterEntityFactory::createCell('Diameter Tersier'),
            WriterEntityFactory::createCell('Diameter Kuarter'),
        ];

        $headerRow = WriterEntityFactory::createRow($headerCells, $headerStyle);
        $writer->addRow($headerRow);

        // Proses data dalam chunk untuk efisiensi memori
        $query->chunk(1000, function ($agronomiChunk) use ($writer, $now) {
            $rows = [];

            foreach ($agronomiChunk as $list) {
                $tanggaltanam = Carbon::parse($list->tanggaltanam);
                $umurTanam = $tanggaltanam->diffInMonths($now);

                $tanggalpengamatan = Carbon::parse($list->tanggalpengamatan);
                $bulanPengamatan = $tanggalpengamatan->format('F');

                // Format persentase (konversi ke desimal untuk Excel)
                // $perGap = is_numeric($list->per_gap) ? $list->per_gap / 100 : $list->per_gap;
                // $perGerminasi = is_numeric($list->per_germinasi) ? $list->per_germinasi / 100 : $list->per_germinasi;
                // $perGulma = is_numeric($list->per_gulma) ? $list->per_gulma / 100 : $list->per_gulma;

                $decimalStyle = (new StyleBuilder())
                    ->setFormat('0.00')
                    ->build();

                $cells = [
                    WriterEntityFactory::createCell($list->nosample),
                    WriterEntityFactory::createCell($list->compName),
                    WriterEntityFactory::createCell($list->blokName),
                    WriterEntityFactory::createCell($list->plotName),
                    WriterEntityFactory::createCell(round((float) $list->luasarea, 10)),
                    WriterEntityFactory::createCell($list->varietas),
                    WriterEntityFactory::createCell($list->kat),
                    WriterEntityFactory::createCell($tanggaltanam->format('Y-m-d')),
                    WriterEntityFactory::createCell(round($umurTanam) . ' Bulan'),
                    WriterEntityFactory::createCell(round((float) $list->jaraktanam, 10)),
                    WriterEntityFactory::createCell($list->tanggalpengamatan),
                    WriterEntityFactory::createCell($bulanPengamatan),
                    WriterEntityFactory::createCell($list->nourut),
                    WriterEntityFactory::createCell($list->jumlahbatang),
                    WriterEntityFactory::createCell($list->pan_gap),

                    // Gunakan value asli 0.8 dengan style persentase
                    WriterEntityFactory::createCell(round((float) $list->per_gap, 10), $decimalStyle),
                    WriterEntityFactory::createCell(round((float) $list->per_germinasi, 10), $decimalStyle),

                    WriterEntityFactory::createCell(round((float) $list->ph_tanah, 10)),
                    WriterEntityFactory::createCell(round((float) $list->populasi, 10)),
                    WriterEntityFactory::createCell($list->ktk_gulma),

                    // Untuk per_gulma juga gunakan style yang sama
                    WriterEntityFactory::createCell(round((float) $list->per_gulma, 10), $decimalStyle),

                    WriterEntityFactory::createCell($list->t_primer),
                    WriterEntityFactory::createCell($list->t_sekunder),
                    WriterEntityFactory::createCell($list->t_tersier),
                    WriterEntityFactory::createCell($list->t_kuarter),
                    WriterEntityFactory::createCell(round((float) $list->d_primer, 1), (new StyleBuilder())->setFormat('0.0')->build()),
                    WriterEntityFactory::createCell(round((float) $list->d_sekunder, 2), $decimalStyle),
                    WriterEntityFactory::createCell(round((float) $list->d_tersier, 3), (new StyleBuilder())->setFormat('0.000')->build()),
                    WriterEntityFactory::createCell(round((float) $list->d_kuarter, 4), (new StyleBuilder())->setFormat('0.0000')->build()),
                ];

                $rows[] = WriterEntityFactory::createRow($cells);
            }

            // Tulis semua rows dalam chunk sekaligus
            $writer->addRows($rows);

            // Bebaskan memori
            unset($rows);
            gc_collect_cycles();
        });

        $writer->close();

        // Return file sebagai download dan hapus setelah dikirim
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ])->deleteFileAfterSend(true);
    }
}
