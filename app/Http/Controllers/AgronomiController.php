<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Mapping;
use App\Models\Perusahaan;
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
            'no_sample' => 'required',
            'kd_comp' => 'required',
            'kd_blok' => 'required',
            'kd_plot' => 'required',
            'kd_plotsample' => 'required|exists:mapping,kd_plotsample',
            'varietas' => 'required',
            'kat' => 'required',
            'tgltanam' => 'required',
            'tglamat' => 'required',
            'lists.*.no_urut' => 'required',
            'lists.*.jm_batang' => 'required',
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
        $companyArray = explode(',', Auth::user()->userComp->kd_comp);
        // dd($company);

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);

            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $agronomi = AgronomiHeader::orderBy('created_at', 'desc')->with('lists', 'perusahaan')
            ->where('kd_comp', '=', session('dropdown_value'))
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->paginate($perPage);

        foreach ($agronomi as $item) {
            $item->umur_tanam = Carbon::parse($item->tgltanam)->diffInMonths(Carbon::now());
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
        $mapping = Mapping::where('kd_comp', '=', session('dropdown_value'))->get();
        $method = 'POST';
        $url = route('input.agronomi.handle');
        $buttonSubmit = 'Create';
        return view('input.agronomi.form', compact('buttonSubmit', 'mapping', 'title', 'method', 'url'));
    }

    public function getFieldByMapping(Request $request)
    {
        $kd_plotsample = $request->input('kd_plotsample');
        $mapping = Mapping::where('kd_plotsample', $kd_plotsample)
            ->where('kd_comp', session('dropdown_value'))->first();

        if ($mapping) {
            return response()->json([
                'kd_comp' => $mapping->kd_comp,
                'kd_blok' => $mapping->kd_blok,
                'kd_plot' => $mapping->kd_plot,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function checkData(Request $request)
    {
        $noSample = $request->get('no_sample');
        $kdPlotSample = $request->get('kd_plotsample');

        $data = DB::table('agro_hdr')
            ->where('no_sample', $noSample)
            ->where('kd_plotsample', $kdPlotSample)
            ->where('kd_comp', session('dropdown_value'))
            ->first();

        if ($data) {
            return response()->json([
                'success' => true,
                'kat' => $data->kat,
                'varietas' => $data->varietas,
                'tgltanam' => $data->tgltanam,
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

        $existsInHeader = AgronomiHeader::where('no_sample', $request->no_sample)
            ->where('kd_comp', $request->kd_comp)
            ->where('tgltanam', $request->tgltanam)
            ->exists();

        $existsInLists = AgronomiList::where('no_sample', $request->no_sample)
            ->where('kd_comp', $request->kd_comp)
            ->where('tgltanam', $request->tgltanam)
            ->exists();

        if ($existsInHeader || $existsInLists) {
            return back()->withErrors([
                'duplicate' => 'Data sudah ada di salah satu tabel, silahkan coba dengan data yang berbeda.',
            ])->withInput();
        }

        DB::beginTransaction();

        try {

            $header = AgronomiHeader::create([
                'no_sample' => $validated['no_sample'],
                'kd_comp' => $validated['kd_comp'],
                'kd_blok' => $validated['kd_blok'],
                'kd_plot' => $validated['kd_plot'],
                'kd_plotsample' => $validated['kd_plotsample'],
                'varietas' => $validated['varietas'],
                'kat' => $validated['kat'],
                'tgltanam' => $validated['tgltanam'],
                'tglamat' => $validated['tglamat'],
                'user_input' => Auth::user()->usernm,
            ]);

            foreach ($validated['lists'] as $list) {
                $per_gap = $list['pan_gap'] / 1000;
                $per_germinasi = 1 - $per_gap;
                $per_gulma = $list['ktk_gulma'] ? $list['ktk_gulma'] / 16 : 0;

                $header->lists()->create([
                    'no_sample' => $validated['no_sample'],
                    'kd_comp' => $validated['kd_comp'],
                    'tgltanam' => $validated['tgltanam'],
                    'no_urut' => $list['no_urut'],
                    'jm_batang' => $list['jm_batang'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $per_gap,
                    'per_germinasi' => 1 - $per_gap,
                    'ph_tanah' => $list['ph_tanah'],
                    'populasi' => $list['jm_batang'] / 10,
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
                    'user_input' => Auth::user()->usernm,
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

            return redirect()->route('input.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function show($no_sample, $kd_comp, $tgltanam)
    {

        $agronomi = DB::table('agro_hdr')
            ->where('kd_comp', '=', session('dropdown_value'))
            ->where('no_sample', $no_sample)
            ->where('kd_comp', $kd_comp)
            ->where('tgltanam', $tgltanam)
            ->first();

        if (!$agronomi) {
            abort(404, 'Agronomi header not found');
        }

        $agronomiLists = DB::table('agro_lst')
            ->leftJoin('agro_hdr', function ($join) {
                $join->on('agro_lst.no_sample', '=', 'agro_hdr.no_sample')
                    ->whereColumn('agro_lst.kd_comp', '=', 'agro_hdr.kd_comp')
                    ->whereColumn('agro_lst.tgltanam', '=', 'agro_hdr.tgltanam');
            })
            ->leftJoin('perusahaan', function ($join) {
                $join->on('agro_hdr.kd_comp', '=', 'perusahaan.kd_comp');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agro_hdr.kd_blok', '=', 'blok.kd_blok')
                    ->whereColumn('agro_hdr.kd_comp', '=', 'blok.kd_comp');
            })
            ->leftJoin('plotting', function ($join) {
                $join->on('agro_hdr.kd_plot', '=', 'plotting.kd_plot')
                    ->whereColumn('agro_hdr.kd_comp', '=', 'plotting.kd_comp');
            })
            ->select(
                'agro_lst.*',
                'agro_hdr.varietas',
                'agro_hdr.kat',
                'agro_hdr.tglamat',
                'perusahaan.nama as compName',
                'blok.kd_blok as blokName',
                'plotting.kd_plot as plotName',
                'plotting.luas_area',
                'plotting.jarak_tanam',
            )
            ->where('agro_lst.no_sample', $no_sample)
            ->where('agro_lst.kd_comp', $kd_comp)
            ->where('agro_lst.tgltanam', $tgltanam)
            ->orderBy('agro_lst.created_at', 'desc')
            ->get();

        $now = Carbon::now();

        $agronomiLists = $agronomiLists->map(function ($item) use ($now) {
            $tgl_tanam = Carbon::parse($item->tgltanam);
            $item->umur_tanam = $tgl_tanam->diffInMonths($now);
            return $item;
        });

        foreach ($agronomiLists as $index => $item) {
            $item->no = $index + 1;
        }

        return response()->json($agronomiLists);
    }

    public function edit($no_sample, $kd_comp, $tgltanam)
    {
        $title = 'Edit Data';
        $header = AgronomiHeader::with(['lists' => function ($query) use ($no_sample, $kd_comp, $tgltanam) {
            $query->where('no_sample', $no_sample)
                ->where('kd_comp', $kd_comp)
                ->where('tgltanam', $tgltanam);
        }])
            ->where('no_sample', $no_sample)
            ->where('kd_comp', $kd_comp)
            ->where('tgltanam', $tgltanam)
            ->firstOrFail();
        $list = AgronomiList::where('no_sample', $no_sample)
            ->where('kd_comp', $kd_comp)
            ->where('tgltanam', $tgltanam)
            ->firstOrFail();
        $company = Perusahaan::all();
        $mapping = Mapping::all();
        $method = 'PUT';
        $buttonSubmit = 'Update';
        $url = route('input.agronomi.update', ['no_sample' => $no_sample, 'kd_comp' => $kd_comp, 'tgltanam' => $tgltanam]);
        
        if ($header->status === "Posted") {
            return redirect()->route('input.hpt.index')->with('success1', 'Data telah di posting, tidak dapat mengakses edit.');
        }

        return view('input.agronomi.form', compact('buttonSubmit', 'header', 'list', 'company', 'mapping', 'title', 'method', 'url'));
    }

    public function update(Request $request, $no_sample, $kd_comp, $tgltanam)
    {
        $validated = $request->validate($this->requestValidated());

        DB::beginTransaction();

        try {
            $header = AgronomiHeader::where('no_sample', $no_sample)
                ->where('kd_comp', $kd_comp)
                ->where('tgltanam', $tgltanam)
                ->firstOrFail();

            DB::table('agro_hdr')
                ->where('no_sample', $no_sample)
                ->where('kd_comp', $kd_comp)
                ->where('tgltanam', $tgltanam)
                ->update([
                    'no_sample' => $validated['no_sample'],
                    'kd_comp' => $validated['kd_comp'],
                    'kd_blok' => $validated['kd_blok'],
                    'kd_plot' => $validated['kd_plot'],
                    'kd_plotsample' => $validated['kd_plotsample'],
                    'varietas' => $validated['varietas'],
                    'kat' => $validated['kat'],
                    'tgltanam' => $validated['tgltanam'],
                    'tglamat' => $validated['tglamat'],
                    'updated_at' => now(),
                ]);

            $existingLists = AgronomiList::where('no_sample', $no_sample)
                ->where('kd_comp', $kd_comp)
                ->where('tgltanam', $tgltanam)
                ->get(['no_urut', 'user_input', 'created_at'])
                ->keyBy('no_urut');

            DB::table('agro_lst')
                ->where('no_sample', $no_sample)
                ->where('kd_comp', $kd_comp)
                ->where('tgltanam', $tgltanam)
                ->delete();

            $listData = [];
            foreach ($validated['lists'] as $list) {
                $listData[] = [
                    'no_sample' => $validated['no_sample'],
                    'kd_comp' => $validated['kd_comp'],
                    'tgltanam' => $validated['tgltanam'],
                    'no_urut' => $list['no_urut'],
                    'jm_batang' => $list['jm_batang'],
                    'pan_gap' => $list['pan_gap'],
                    'per_gap' => $list['pan_gap'] / 1000,
                    'per_germinasi' => 1 - ($list['pan_gap'] / 1000),
                    'ph_tanah' => $list['ph_tanah'],
                    'populasi' => $list['jm_batang'] / 10,
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
                    'user_input' => $existingLists[$list['no_urut']]['user_input'] ?? $header->user_input,
                    'created_at' => $existingLists[$list['no_urut']]['created_at'] ?? $header->created_at,
                    'updated_at' => now(),
                ];
            }

            DB::table('agro_lst')->insert($listData);

            DB::commit();

            return redirect()->route('input.agronomi.index')
                ->with('success', 'Data updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('input.agronomi.create')
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($no_sample, $kd_comp, $tgltanam)
    {
        DB::transaction(function () use ($no_sample, $kd_comp, $tgltanam) {
            $header = AgronomiHeader::where('no_sample', $no_sample)
                ->where('kd_comp', $kd_comp)
                ->where('tgltanam', $tgltanam)
                ->firstOrFail();
            $list = AgronomiList::where('no_sample', $no_sample)
                ->where('kd_comp', $kd_comp)
                ->where('tgltanam', $tgltanam);

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

        $query = DB::table('agro_lst')
            ->leftJoin('agro_hdr', function ($join) {
                $join->on('agro_lst.no_sample', '=', 'agro_hdr.no_sample')
                    ->whereColumn('agro_lst.kd_comp', '=', 'agro_hdr.kd_comp')
                    ->whereColumn('agro_lst.tgltanam', '=', 'agro_hdr.tgltanam');
            })
            ->leftJoin('perusahaan', function ($join) {
                $join->on('agro_hdr.kd_comp', '=', 'perusahaan.kd_comp');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agro_hdr.kd_blok', '=', 'blok.kd_blok')
                    ->whereColumn('agro_hdr.kd_comp', '=', 'blok.kd_comp');
            })
            ->leftJoin('plotting', function ($join) {
                $join->on('agro_hdr.kd_plot', '=', 'plotting.kd_plot')
                    ->whereColumn('agro_hdr.kd_comp', '=', 'plotting.kd_comp');
            })
            ->where('agro_lst.kd_comp', session('dropdown_value'))
            ->where('agro_hdr.kd_comp', session('dropdown_value'))
            ->select(
                'agro_lst.*',
                'agro_hdr.varietas',
                'agro_hdr.kat',
                'agro_hdr.tglamat',
                'perusahaan.nama as compName',
                'blok.kd_blok as blokName',
                'plotting.kd_plot as plotName',
                'plotting.luas_area',
                'plotting.jarak_tanam',
            )
            ->orderBy('agro_lst.created_at', 'desc');


        if ($startDate) {
            $query->whereDate('agro_lst.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('agro_lst.created_at', '<=', $endDate);
        }
        if (!empty($comp)) {
            $query->whereIn('agro_lst.kd_comp', $comp);
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

            $tglTanam = Carbon::parse($list->tgltanam);
            $umurTanam = $tglTanam->diffInMonths($now);

            $tglAmat = Carbon::parse($list->tglamat);
            $bulanPengamatan = $tglAmat->format('F');

            $sheet->setCellValue('A' . $row, $list->no_sample);
            $sheet->setCellValue('B' . $row, $list->compName);
            $sheet->setCellValue('C' . $row, $list->blokName);
            $sheet->setCellValue('D' . $row, $list->plotName);
            $sheet->setCellValue('E' . $row, $list->luas_area);
            $sheet->setCellValue('F' . $row, $list->varietas);
            $sheet->setCellValue('G' . $row, $list->kat);
            $sheet->setCellValue('H' . $row, $tglTanam->format('Y-m-d'));
            $sheet->setCellValue('I' . $row, ceil($umurTanam) . ' Bulan');
            $sheet->setCellValue('J' . $row, $list->jarak_tanam);
            $sheet->setCellValue('K' . $row, $list->tglamat);
            $sheet->setCellValue('L' . $row, $bulanPengamatan);
            $sheet->setCellValue('M' . $row, $list->no_urut);
            $sheet->setCellValue('N' . $row, $list->jm_batang);
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
