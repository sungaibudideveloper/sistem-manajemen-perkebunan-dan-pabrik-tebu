<?php

namespace App\Http\Controllers\Input;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use App\Models\Mapping;
use App\Models\company;
use App\Models\AgronomiList;
use Illuminate\Http\Request;
use App\Models\AgronomiHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\NotificationController;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AgronomiController extends Controller
{

    public function __construct()
    {
        View::share([
            'navbar' => 'Input Data',
            'nav' => 'Agronomi',
            'routeName' => route('input.agronomi.index'),
        ]);
    }

    protected function requestValidated(): array
    {
        return [
            'nosample' => 'required',
            'companycode' => 'required',
            'blok' => 'required',
            'plot' => 'required',
            'idblokplot' => 'required|exists:mappingblokplot,idblokplot',
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
            'lists.*.d_primer' => 'required|numeric',
            'lists.*.d_sekunder' => 'required|numeric',
            'lists.*.d_tersier' => 'required|numeric',
            'lists.*.d_kuarter' => 'required|numeric',
        ];
    }
    public function index(Request $request)
    {
        $title = "Daftar Agronomi";

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $companyArray = explode(',', Auth::user()->userComp->companycode);
        // dd($company);

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $agronomi = AgronomiHeader::orderBy('createdat', 'desc')->with('lists', 'company')
            ->where('companycode', '=', session('companycode'))
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('createdat', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('createdat', '<=', $endDate);
            })
            ->paginate($perPage);

        foreach ($agronomi as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
        }

        foreach ($agronomi as $index => $item) {
            $item->no = ($agronomi->currentPage() - 1) * $agronomi->perPage() + $index + 1;
        }

        return view('input.agronomi.index', compact('agronomi', 'perPage', 'startDate', 'endDate', 'title'));
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
        $mapping = Mapping::where('companycode', '=', session('companycode'))->get();
        $method = 'POST';
        $url = route('input.agronomi.handle');
        $buttonSubmit = 'Create';
        return view('input.agronomi.form', compact('buttonSubmit', 'mapping', 'title', 'method', 'url'));
    }

    public function getFieldByMapping(Request $request)
    {
        $idblokplot = $request->input('idblokplot');
        $mapping = Mapping::where('idblokplot', $idblokplot)->where('companycode', session('companycode'))->first();

        if ($mapping) {
            return response()->json([
                'companycode' => $mapping->companycode,
                'blok' => $mapping->blok,
                'plot' => $mapping->plot,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function checkData(Request $request)
    {
        $noSample = $request->get('nosample');
        $kdPlotSample = $request->get('idblokplot');

        $data = DB::table('agrohdr')
            ->where('nosample', $noSample)
            ->where('idblokplot', $kdPlotSample)
            ->where('companycode', session('companycode'))
            ->first();

        if ($data) {
            return response()->json([
                'success' => true,
                'kat' => $data->kat,
                'varietas' => $data->varietas,
                'tanggaltanam' => $data->tanggaltanam,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data not found',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->requestValidated());
        $notifController = new NotificationController();
        $existsInHeader = AgronomiHeader::where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggaltanam', $request->tanggaltanam)
            ->exists();

        $existsInLists = AgronomiList::where('nosample', $request->nosample)
            ->where('companycode', $request->companycode)
            ->where('tanggaltanam', $request->tanggaltanam)
            ->exists();

        if ($existsInHeader || $existsInLists) {
            return back()->withErrors([
                'duplicate' => 'Data sudah ada di salah satu tabel, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        DB::beginTransaction();

        try {

            $header = AgronomiHeader::create([
                'nosample' => $validated['nosample'],
                'companycode' => $validated['companycode'],
                'blok' => $validated['blok'],
                'plot' => $validated['plot'],
                'idblokplot' => $validated['idblokplot'],
                'varietas' => $validated['varietas'],
                'kat' => $validated['kat'],
                'tanggaltanam' => $validated['tanggaltanam'],
                'tanggalpengamatan' => $validated['tanggalpengamatan'],
                'inputby' => Auth::user()->userid,
            ]);

            foreach ($validated['lists'] as $list) {
                $per_gap = $list['pan_gap'] / 1000;
                $per_germinasi = 1 - $per_gap;
                $per_gulma = $list['ktk_gulma'] ? $list['ktk_gulma'] / 16 : 0;

                $header->lists()->create([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'nourut' => $list['nourut'],
                    'jumlahbatang' => $list['jumlahbatang'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $per_gap,
                    'per_germinasi' => 1 - $per_gap,
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
                    'inputby' => Auth::user()->userid,
                    'createdat' => now(),
                    'updatedat' => now()
                ]);

                if ($per_germinasi < 0.9 || $per_gulma > 0.25) {
                    $notifController->agronomiNotif();
                }
            }

            DB::commit();
            return redirect()->back()
                ->with('success1', 'Data created successfully.');
        } catch (\Exception $e) {

            DB::rollBack();
            dd($e);
            return redirect()->route('input.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show($nosample, $companycode, $tanggaltanam)
    {

        $agronomi = DB::table('agrohdr')
            ->where('companycode', '=', session('companycode'))
            ->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggaltanam', $tanggaltanam)
            ->first();

        if (!$agronomi) {
            abort(404, 'Agronomi header not found');
        }

        $agronomiLists = DB::table('agrolst')
            ->leftJoin('agrohdr', function ($join) {
                $join->on('agrolst.nosample', '=', 'agrohdr.nosample')
                    ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                    ->whereColumn('agrolst.tanggaltanam', '=', 'agrohdr.tanggaltanam');
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
                'agrohdr.tanggalpengamatan',
                'company.name as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
                'plot.jaraktanam',
            )
            ->where('agrolst.nosample', $nosample)
            ->where('agrolst.companycode', $companycode)
            ->where('agrolst.tanggaltanam', $tanggaltanam)
            ->orderBy('agrolst.createdat', 'desc')
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

    public function edit($nosample, $companycode, $tanggaltanam)
    {
        $title = 'Edit Data';
        $header = AgronomiHeader::with(['lists' => function ($query) use ($nosample, $companycode, $tanggaltanam) {
            $query->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam);
        }])
            ->where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggaltanam', $tanggaltanam)
            ->firstOrFail();
        $list = AgronomiList::where('nosample', $nosample)
            ->where('companycode', $companycode)
            ->where('tanggaltanam', $tanggaltanam)
            ->firstOrFail();
        $company = company::all();
        $mapping = Mapping::all();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('input.agronomi.update', ['nosample' => $nosample, 'companycode' => $companycode, 'tanggaltanam' => $tanggaltanam]);

        if ($header->status === "Posted") {
            return redirect()->route('input.hpt.index')->with('success1', 'Data telah di posting, tidak dapat mengakses edit.');
        }

        return view('input.agronomi.form', compact('buttonSubmit', 'header', 'list', 'company', 'mapping', 'title', 'method', 'url'));
    }

    public function update(Request $request, $nosample, $companycode, $tanggaltanam)
    {
        $validated = $request->validate($this->requestValidated());
        DB::beginTransaction();

        try {
            $header = AgronomiHeader::where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->firstOrFail();

            DB::table('agrohdr')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->update([
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'blok' => $validated['blok'],
                    'plot' => $validated['plot'],
                    'idblokplot' => $validated['idblokplot'],
                    'varietas' => $validated['varietas'],
                    'kat' => $validated['kat'],
                    'tanggaltanam' => $validated['tanggaltanam'],
                    'tanggalpengamatan' => $validated['tanggalpengamatan'],
                    'updatedat' => now(),
                ]);

            $existingLists = AgronomiList::where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->get(['nourut', 'inputby', 'createdat'])
                ->keyBy('nourut');

            DB::table('agrolst')
                ->where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->delete();

            $listData = [];
            foreach ($validated['lists'] as $list) {
                $listData[] = [
                    'nosample' => $validated['nosample'],
                    'companycode' => $validated['companycode'],
                    'tanggaltanam' => $validated['tanggaltanam'],
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
                    'inputby' => $existingLists[$list['nourut']]['inputby'] ?? $header->inputby,
                    'createdat' => $existingLists[$list['nourut']]['createdat'] ?? $header->createdat,
                    'updatedat' => now(),
                ];
            }

            DB::table('agrolst')->insert($listData);

            DB::commit();

            return redirect()->route('input.agronomi.index')
                ->with('success', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('input.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($nosample, $companycode, $tanggaltanam)
    {
        DB::transaction(function () use ($nosample, $companycode, $tanggaltanam) {
            $header = AgronomiHeader::where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam)
                ->firstOrFail();
            $list = AgronomiList::where('nosample', $nosample)
                ->where('companycode', $companycode)
                ->where('tanggaltanam', $tanggaltanam);

            $header->delete();
            $list->delete();
        });
        return redirect()->route('input.agronomi.index')
            ->with('success', 'Data deleted successfully.');
    }

    public function excel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        // dd($comp);

        $query = DB::table('agrolst')
            ->leftJoin('agrohdr', function ($join) {
                $join->on('agrolst.nosample', '=', 'agrohdr.nosample')
                    ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                    ->whereColumn('agrolst.tanggaltanam', '=', 'agrohdr.tanggaltanam');
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
            ->where('agrolst.companycode', session('companycode'))
            ->where('agrohdr.companycode', session('companycode'))
            ->select(
                'agrolst.*',
                'agrohdr.varietas',
                'agrohdr.kat',
                'agrohdr.tanggalpengamatan',
                'company.nama as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
                'plot.jaraktanam',
            )
            ->orderBy('agrolst.createdat', 'desc');


        if ($startDate) {
            $query->whereDate('agrolst.createdat', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('agrolst.createdat', '<=', $endDate);
        }
        if (!empty($comp)) {
            $query->whereIn('agrolst.companycode', $comp);
        }
        $agronomi = $query->get();

        $now = Carbon::now();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No. Sample');
        $sheet->setCellValue('B1', 'Kebun');
        $sheet->setCellValue('C1', 'Blok');
        $sheet->setCellValue('D1', 'Plot');
        $sheet->setCellValue('E1', 'Luas');
        $sheet->setCellValue('F1', 'Varietas');
        $sheet->setCellValue('G1', 'Kategori');
        $sheet->setCellValue('H1', 'Tanggal Tanam');
        $sheet->setCellValue('I1', 'Umur Tanam');
        $sheet->setCellValue('J1', 'Jarak Tanam');
        $sheet->setCellValue('K1', 'Tanggal Pengamatan');
        $sheet->setCellValue('L1', 'Bulan Pengamatan');
        $sheet->setCellValue('M1', 'No. Urut');
        $sheet->setCellValue('N1', 'Jumlah Batang');
        $sheet->setCellValue('O1', 'Panjang GAP');
        $sheet->setCellValue('P1', '%GAP');
        $sheet->setCellValue('Q1', '%Germinasi');
        $sheet->setCellValue('R1', 'pH Tanah');
        $sheet->setCellValue('S1', 'Populasi');
        $sheet->setCellValue('T1', 'Kotak Gulma');
        $sheet->setCellValue('U1', '%Penutupan Gulma');
        $sheet->setCellValue('V1', 'Tinggi Primer');
        $sheet->setCellValue('W1', 'Tinggi Sekunder');
        $sheet->setCellValue('X1', 'Tinggi Tersier');
        $sheet->setCellValue('Y1', 'Tinggi Kuarter');
        $sheet->setCellValue('Z1', 'Diameter Primer');
        $sheet->setCellValue('AA1', 'Diameter Sekunder');
        $sheet->setCellValue('AB1', 'Diameter Tersier');
        $sheet->setCellValue('AC1', 'Diameter Kuarter');

        $sheet->getStyle('A1:AC1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($agronomi as $list) {

            $tanggaltanam = Carbon::parse($list->tanggaltanam);
            $umurTanam = $tanggaltanam->diffInMonths($now);

            $tglAmat = Carbon::parse($list->tanggalpengamatan);
            $bulanPengamatan = $tglAmat->format('F');

            $sheet->setCellValue('A' . $row, $list->nosample);
            $sheet->setCellValue('B' . $row, $list->compName);
            $sheet->setCellValue('C' . $row, $list->blokName);
            $sheet->setCellValue('D' . $row, $list->plotName);
            $sheet->setCellValue('E' . $row, $list->luasarea);
            $sheet->setCellValue('F' . $row, $list->varietas);
            $sheet->setCellValue('G' . $row, $list->kat);
            $sheet->setCellValue('H' . $row, $tanggaltanam->format('Y-m-d'));
            $sheet->setCellValue('I' . $row, ceil($umurTanam) . ' Bulan');
            $sheet->setCellValue('J' . $row, $list->jaraktanam);
            $sheet->setCellValue('K' . $row, $list->tanggalpengamatan);
            $sheet->setCellValue('L' . $row, $bulanPengamatan);
            $sheet->setCellValue('M' . $row, $list->nourut);
            $sheet->setCellValue('N' . $row, $list->jumlahbatang);
            $sheet->setCellValue('O' . $row, $list->pan_gap);
            $sheet->setCellValue('P' . $row, $list->per_gap);
            $sheet->setCellValue('Q' . $row, $list->per_germinasi);
            $sheet->setCellValue('R' . $row, $list->ph_tanah);
            $sheet->setCellValue('S' . $row, $list->populasi);
            $sheet->setCellValue('T' . $row, $list->ktk_gulma);
            $sheet->setCellValue('U' . $row, $list->per_gulma);
            $sheet->setCellValue('V' . $row, $list->t_primer);
            $sheet->setCellValue('W' . $row, $list->t_sekunder);
            $sheet->setCellValue('X' . $row, $list->t_tersier);
            $sheet->setCellValue('Y' . $row, $list->t_kuarter);
            $sheet->setCellValue('Z' . $row, $list->d_primer);
            $sheet->setCellValue('AA' . $row, $list->d_sekunder);
            $sheet->setCellValue('AB' . $row, $list->d_tersier);
            $sheet->setCellValue('AC' . $row, $list->d_kuarter);

            $sheet->getStyle('P' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->getStyle('Q' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->getStyle('U' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        if ($startDate && $endDate) {
            $filename = "AgronomiReport_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $filename = "AgronomiReport.xlsx";
        }
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
