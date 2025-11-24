<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class RekapUpahMingguanController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
            'nav' => 'Rekap Upah Mingguan',
        ]);
    }

    public function index(Request $request)
    {
        $title = 'Rekap Upah Mingguan';
        $search = $request->input('search', '');
        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }
        if ($request->has('tenagakerjarum')) {
            session(['tenagakerjarum' => $request->tenagakerjarum]);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $perPage = $request->session()->get('perPage', 10);
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;

        $querys = DB::table('lkhhdr')
            ->join('activity', 'activity.activitycode', '=', 'lkhhdr.activitycode')
            ->leftJoin('lkhdetailplot', 'lkhdetailplot.lkhno', '=', 'lkhhdr.lkhno')
            ->where('lkhhdr.companycode', '=', session('companycode'))
            ->where('lkhhdr.status', '=', 'APPROVED')
            ->where('activity.active', '=', 1)
            ->where('lkhhdr.jenistenagakerja', '=', $tk)
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('lkhhdr.lkhdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('lkhhdr.lkhdate', '<=', $endDate);
            })
            ->groupBy('lkhhdr.*', 'activity.activityname');

        if (!empty($search)) {
            $querys->where(function ($query) use ($search) {
                $query->where('lkhhdr.activitycode', 'like', '%' . $search . '%')
                    ->orWhere('activity.activityname', 'like', '%' . $search . '%');
            });
        }

        $rum = $querys
            ->select(
                'lkhhdr.*',
                'activity.activityname',
                DB::raw("GROUP_CONCAT(DISTINCT lkhdetailplot.plot ORDER BY lkhdetailplot.plot SEPARATOR ', ') as plots")
            )
            ->paginate($perPage);

        foreach ($rum as $index => $item) {
            $item->no = ($rum->currentPage() - 1) * $rum->perPage() + $index + 1;
            if ($item->totalupahall == 0) {
                $item->totalupahall = '-';
            } else {
                $item->totalupahall = Number::currency($item->totalupahall, 'IDR', 'id');
            }
        }

        if ($request->ajax()) {
            return view('report.rum.index', compact('title', 'search', 'perPage', 'rum', 'startDate', 'endDate'));
        }
        return view('report.rum.index', compact('title', 'perPage', 'search', 'rum', 'startDate', 'endDate'));
    }

    public function show($lkhno)
    {
        $companycode = session('companycode');
        $header = DB::table('lkhhdr')
            ->where('lkhno', $lkhno)
            ->where('companycode', $companycode)
            ->first();

        if (!$header) {
            return collect(); // atau bisa return null sesuai kebutuhanmu
        }

        $query = DB::table('lkhhdr as a')
            ->leftJoin('lkhdetailplot as b', function ($join) {
                $join->on('a.lkhno', '=', 'b.lkhno')
                    ->on('a.companycode', '=', 'b.companycode');
            });

        if ($header->jenistenagakerja == 1) {
            $datas = $query
                ->leftJoin('lkhdetailworker as d', function ($join) {
                    $join->on('a.lkhno', '=', 'd.lkhno')
                        ->on('a.companycode', '=', 'd.companycode');
                })
                ->select(
                    'a.*',
                    'b.plot',
                    'b.luasrkh',
                    'b.luashasil',
                    'd.upahharian as upah',
                    'd.totalupah as total',
                    DB::raw("(SELECT nama FROM tenagakerja WHERE tenagakerjaid = d.tenagakerjaid LIMIT 1) AS namatenagakerja"),
                    DB::raw("(SELECT activityname FROM activity WHERE activitycode = a.activitycode LIMIT 1) AS activityname"),
                );

        } elseif ($header->jenistenagakerja == 2) {
            $datas = $query
                ->select(
                    'a.*',
                    'b.*',
                    DB::raw("(SELECT activityname FROM activity WHERE activitycode = a.activitycode LIMIT 1) AS activityname"),
                    DB::raw("(SELECT amount FROM upahborongan WHERE activitycode = a.activitycode AND companycode = a.companycode LIMIT 1) AS upah")
                );


        } else {
            $datas = collect(); // Jika tidak ada jenis tenaga kerja yang cocok
        }

        $data = $datas->where('a.lkhno', $lkhno)
            ->where('a.companycode', $companycode)
            ->where('a.jenistenagakerja', $header->jenistenagakerja)
            ->orderByDesc('a.lkhno')
            ->get();


        foreach ($data as $index => $item) {
            $item->no = $index + 1;
            if ($header->jenistenagakerja == 2) {
                $item->total = $item->upah * $item->luashasil;
            }
            $item->upah = Number::currency($item->upah ?? 0, 'IDR', 'id');
            $item->total = Number::currency($item->total ?? 0, 'IDR', 'id');
        }

        return response()->json($data);
    }

    public function previewReport(Request $request)
    {
        $title = "Preview Rekap Upah Mingguan";
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;
        $companycode = session('companycode');

        $query = DB::table('lkhhdr as a')
            ->leftJoin('lkhdetailplot as b', function ($join) {
                $join->on('a.lkhno', '=', 'b.lkhno')
                    ->on('a.companycode', '=', 'b.companycode');
            })
            ->leftJoin('activity as c', function ($join) {
                $join->on('a.activitycode', '=', 'c.activitycode');
            });

        if ($tk == 1) {
            $datas = $query
                ->leftJoin('lkhdetailworker as d', function ($join) {
                    $join->on('a.lkhno', '=', 'd.lkhno')
                        ->on('a.companycode', '=', 'd.companycode');
                })
                ->select(
                    'a.*',
                    'b.plot',
                    'b.luasrkh as luasan',
                    'b.luashasil as hasil',
                    'c.activityname',
                    'd.upahharian as upah',
                    'd.totalupah as total',
                    DB::raw("(SELECT nama FROM tenagakerja WHERE tenagakerjaid = d.tenagakerjaid LIMIT 1) AS namatenagakerja"),
                );

        } elseif ($tk == 2) {
            $datas = $query
                ->select(
                    'a.*',
                    'b.plot',
                    'b.luasrkh as luasan',
                    'b.luashasil as hasil',
                    'c.activityname',
                    DB::raw("(SELECT amount FROM upahborongan WHERE activitycode = a.activitycode AND companycode = a.companycode LIMIT 1) AS upah")
                );

        } else {
            $datas = collect();
        }

        $data = $datas
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('a.lkhdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('a.lkhdate', '<=', $endDate);
            })
            ->where('a.companycode', '=', $companycode)
            ->where('a.status', '=', 'APPROVED')
            ->where('a.jenistenagakerja', '=', $tk)
            ->where('c.active', '=', 1)
            ->orderByDesc('a.activitycode')
            ->get();

        foreach ($data as $index => $item) {
            if ($tk == 2) {
                $item->total = $item->upah * $item->hasil;
            }
            $item->upah = Number::currency($item->upah ?? 0, 'IDR', 'id');
            $item->total = Number::currency($item->total ?? 0, 'IDR', 'id');
        }
        // $data = YourModel::query()->get(); // Ganti dengan query Anda
        return view('report.rum.print', compact('title', 'startDate', 'endDate', 'data'));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');
        $tenagakerjarum = session('tenagakerjarum');
        $tk = $tenagakerjarum === 'Harian' ? 1 : 2;
        $companycode = session('companycode');

        $query = DB::table('lkhhdr as a')
            ->leftJoin('lkhdetailplot as b', function ($join) {
                $join->on('a.lkhno', '=', 'b.lkhno')
                    ->on('a.companycode', '=', 'b.companycode');
            })
            ->leftJoin('activity as c', function ($join) {
                $join->on('a.activitycode', '=', 'c.activitycode');
            });

        if ($tk == 1) {
            $datas = $query
                ->leftJoin('lkhdetailworker as d', function ($join) {
                    $join->on('a.lkhno', '=', 'd.lkhno')
                        ->on('a.companycode', '=', 'd.companycode');
                })
                ->select(
                    'a.*',
                    'b.plot',
                    'b.luasrkh as luasan',
                    'b.luashasil as hasil',
                    'c.activityname',
                    'd.upahharian as upah',
                    'd.totalupah as total',
                    DB::raw("(SELECT nama FROM tenagakerja WHERE tenagakerjaid = d.tenagakerjaid LIMIT 1) AS namatenagakerja"),
                );
        } elseif ($tk == 2) {
            $datas = $query
                ->select(
                    'a.*',
                    'b.plot',
                    'b.luasrkh as luasan',
                    'b.luashasil as hasil',
                    'c.activityname',
                    DB::raw("(SELECT amount FROM upahborongan WHERE activitycode = a.activitycode AND companycode = a.companycode LIMIT 1) AS upah")
                );
        } else {
            $datas = collect();
        }

        $data = $datas
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('a.lkhdate', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('a.lkhdate', '<=', $endDate);
            })
            ->where('a.companycode', '=', $companycode)
            ->where('a.status', '=', 'APPROVED')
            ->where('a.jenistenagakerja', '=', $tk)
            ->where('c.active', '=', 1)
            ->orderByDesc('a.activitycode')
            ->get();

        // Process data
        foreach ($data as $index => $item) {
            $item->no = $index + 1;
            if ($tk == 2) {
                $item->total = $item->upah * $item->hasil;
            }
        }

        // Group data by activityname
        $groupedData = [];
        foreach ($data as $item) {
            $activityName = $item->activityname;
            if (!isset($groupedData[$activityName])) {
                $groupedData[$activityName] = [];
            }
            $groupedData[$activityName][] = $item;
        }

        // Create temp directories
        $tempDir = storage_path('app/temp');
        $spoutTempDir = storage_path('app/spout-temp');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        if (!file_exists($spoutTempDir)) {
            mkdir($spoutTempDir, 0755, true);
        }

        // Set temporary folder untuk Spout
        putenv('SPOUT_TEMP_FOLDER=' . $spoutTempDir);

        $filename = 'Rekap_Upah_Mingguan_' . date('Y-m-d_His') . '.xlsx';
        $filePath = $tempDir . '/' . $filename;

        $writer = WriterEntityFactory::createXLSXWriter();

        // Set temp folder untuk writer
        $writer->setTempFolder($spoutTempDir);
        $writer->openToFile($filePath);

        // Styles
        $borderBuilder = new BorderBuilder();
        $border = $borderBuilder
            ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN)
            ->setBorderTop(Color::BLACK, Border::WIDTH_THIN)
            ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN)
            ->setBorderRight(Color::BLACK, Border::WIDTH_THIN)
            ->build();

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(14)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();

        $subHeaderStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(12)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();

        $tableHeaderStyle = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(240, 240, 240))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setBorder($border)
            ->build();

        $normalStyle = (new StyleBuilder())
            ->setBorder($border)
            ->build();

        $centerStyle = (new StyleBuilder())
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::CENTER)
            ->build();

        $rightStyle = (new StyleBuilder())
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->build();

        $mergedCellStyle = (new StyleBuilder())
            ->setBorder($border)
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(249, 249, 249))
            ->build();

        $subtotalStyle = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(255, 249, 230))
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->build();

        $totalStyle = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(230, 247, 230))
            ->setBorder($border)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setFontSize(12)
            ->build();

        // Parse dates for header
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        \Carbon\Carbon::setLocale('id');

        if ($start->format('m Y') === $end->format('m Y')) {
            $periode = $start->translatedFormat('d') . ' s.d ' . $end->translatedFormat('d F Y');
        } else {
            $periode = $start->translatedFormat('d F Y') . ' s.d ' . $end->translatedFormat('d F Y');
        }

        // Header rows - Taruh di tengah (kolom D untuk Harian, kolom C untuk Borongan)
        $headerOffset = $tenagakerjarum == 'Harian' ? 4 : 3; // D=3, C=2 (index 0-based)
        $jenistk = $tenagakerjarum == 'Harian' ? 'Harian' : 'Borongan'; // D=3, C=2 (index 0-based)

        // Row 1: Judul utama
        $headerRow1 = array_fill(0, $headerOffset, '');
        // $headerRow1[] = 'Rekap Upah Mingguan';
        $writer->addRow(WriterEntityFactory::createRow([
            ...(array_map(fn($val) => WriterEntityFactory::createCell($val), $headerRow1)),
            WriterEntityFactory::createCell('Rekap Upah Mingguan', $headerStyle)
        ]));

        // Row 2: Tenaga Kerja
        $headerRow2 = array_fill(0, $headerOffset, '');
        $writer->addRow(WriterEntityFactory::createRow([
            ...(array_map(fn($val) => WriterEntityFactory::createCell($val), $headerRow2)),
            WriterEntityFactory::createCell('Tenaga Kerja ' . $jenistk, $subHeaderStyle)
        ]));

        // Row 3: Divisi
        $headerRow3 = array_fill(0, $headerOffset, '');
        $writer->addRow(WriterEntityFactory::createRow([
            ...(array_map(fn($val) => WriterEntityFactory::createCell($val), $headerRow3)),
            WriterEntityFactory::createCell('Divisi ' . $companycode, $subHeaderStyle)
        ]));

        // Row 4: Periode
        $headerRow4 = array_fill(0, $headerOffset, '');
        $writer->addRow(WriterEntityFactory::createRow([
            ...(array_map(fn($val) => WriterEntityFactory::createCell($val), $headerRow4)),
            WriterEntityFactory::createCell('Periode: ' . $periode, $subHeaderStyle)
        ]));


        // Empty row
        $writer->addRow(WriterEntityFactory::createRowFromArray(['']));

        // No. Voucher
        $writer->addRow(WriterEntityFactory::createRowFromArray(['No. Voucher:']));

        // Empty row
        $writer->addRow(WriterEntityFactory::createRowFromArray(['']));

        // Table header
        if ($tenagakerjarum == 'Harian') {
            $headerRow = ['No.', 'Kegiatan', 'Tenaga Kerja', 'Plot', 'Luasan (Ha)', 'Hasil (Ha)', 'Cost/Unit', 'Biaya (Rp)'];
        } else {
            $headerRow = ['No.', 'Kegiatan', 'Plot', 'Luasan (Ha)', 'Hasil (Ha)', 'Cost/Unit', 'Biaya (Rp)'];
        }
        $writer->addRow(WriterEntityFactory::createRowFromArray($headerRow, $tableHeaderStyle));

        // Data rows
        $rowNumber = 1;
        $totalKeseluruhan = 0;

        foreach ($groupedData as $activityName => $items) {
            $subtotal = 0;
            $firstRow = true;

            foreach ($items as $item) {
                if ($tenagakerjarum == 'Harian') {
                    if ($firstRow) {
                        $rowData = [
                            WriterEntityFactory::createCell($rowNumber, $centerStyle),
                            WriterEntityFactory::createCell($item->activityname, $mergedCellStyle),
                            WriterEntityFactory::createCell($item->namatenagakerja ?? '', $normalStyle),
                            WriterEntityFactory::createCell($item->plot, $normalStyle),
                            WriterEntityFactory::createCell(number_format($item->luasan, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->hasil, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->upah ?? 0, 0, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->total ?? 0, 0, ',', '.'), $rightStyle),
                        ];
                    } else {
                        $rowData = [
                            WriterEntityFactory::createCell($rowNumber, $centerStyle),
                            WriterEntityFactory::createCell('', $normalStyle),
                            WriterEntityFactory::createCell($item->namatenagakerja ?? '', $normalStyle),
                            WriterEntityFactory::createCell($item->plot, $normalStyle),
                            WriterEntityFactory::createCell(number_format($item->luasan, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->hasil, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->upah ?? 0, 0, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->total ?? 0, 0, ',', '.'), $rightStyle),
                        ];
                    }
                } else {
                    if ($firstRow) {
                        $rowData = [
                            WriterEntityFactory::createCell($rowNumber, $centerStyle),
                            WriterEntityFactory::createCell($item->activityname, $mergedCellStyle),
                            WriterEntityFactory::createCell($item->plot, $normalStyle),
                            WriterEntityFactory::createCell(number_format($item->luasan, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->hasil, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->upah ?? 0, 0, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->total ?? 0, 0, ',', '.'), $rightStyle),
                        ];
                    } else {
                        $rowData = [
                            WriterEntityFactory::createCell($rowNumber, $centerStyle),
                            WriterEntityFactory::createCell('', $normalStyle),
                            WriterEntityFactory::createCell($item->plot, $normalStyle),
                            WriterEntityFactory::createCell(number_format($item->luasan, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->hasil, 2, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->upah ?? 0, 0, ',', '.'), $rightStyle),
                            WriterEntityFactory::createCell(number_format($item->total ?? 0, 0, ',', '.'), $rightStyle),
                        ];
                    }
                }

                $writer->addRow(WriterEntityFactory::createRow($rowData));

                $subtotal += ($item->total ?? 0);
                $rowNumber++;
                $firstRow = false;
            }

            // Subtotal row
            if ($tenagakerjarum == 'Harian') {
                $subtotalRow = [
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('Subtotal ' . $activityName, $subtotalStyle),
                    WriterEntityFactory::createCell('Rp ' . number_format($subtotal, 2, ',', '.'), $subtotalStyle),
                ];
            } else {
                $subtotalRow = [
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('', $subtotalStyle),
                    WriterEntityFactory::createCell('Subtotal ' . $activityName, $subtotalStyle),
                    WriterEntityFactory::createCell('Rp ' . number_format($subtotal, 2, ',', '.'), $subtotalStyle),
                ];
            }
            $writer->addRow(WriterEntityFactory::createRow($subtotalRow));

            $totalKeseluruhan += $subtotal;
        }

        // Total keseluruhan row
        if ($tenagakerjarum == 'Harian') {
            $totalRow = [
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('TOTAL KESELURUHAN', $totalStyle),
                WriterEntityFactory::createCell('Rp ' . number_format($totalKeseluruhan, 2, ',', '.'), $totalStyle),
            ];
        } else {
            $totalRow = [
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('', $totalStyle),
                WriterEntityFactory::createCell('TOTAL KESELURUHAN', $totalStyle),
                WriterEntityFactory::createCell('Rp ' . number_format($totalKeseluruhan, 2, ',', '.'), $totalStyle),
            ];
        }
        $writer->addRow(WriterEntityFactory::createRow($totalRow));

        // Footer - taruh di kanan
        $writer->addRow(WriterEntityFactory::createRowFromArray(['']));

        $footerOffset = $tenagakerjarum == 'Harian' ? 6 : 5;
        $footerRow = array_fill(0, $footerOffset, '');
        $footerRow[] = 'Dicetak pada: ' . now()->format('d/m/Y H:i');
        $writer->addRow(WriterEntityFactory::createRowFromArray($footerRow));

        $writer->close();

        // CLEANUP LANGSUNG SETELAH CLOSE WRITER
        $tempFiles = glob($spoutTempDir . '/*');
        foreach ($tempFiles as $tempFile) {
            if (is_file($tempFile)) {
                @unlink($tempFile);
            }
        }

        // Download file dan hapus file Excel setelah download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}