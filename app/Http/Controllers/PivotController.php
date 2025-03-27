<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PivotController extends Controller
{
    public function pivotTableAgronomi(Request $request)
    {

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $chartDataQuery = DB::table('agro_hdr')
            ->join('agro_lst', function ($join) {
                $join->on('agro_hdr.no_sample', '=', 'agro_lst.no_sample')
                    ->on('agro_hdr.kd_comp', '=', 'agro_lst.kd_comp');
            })
            ->join('perusahaan', 'agro_hdr.kd_comp', '=', 'perusahaan.kd_comp')
            ->join('plotting', function ($join) {
                $join->on('agro_hdr.kd_plot', '=', 'plotting.kd_plot')
                    ->on('agro_hdr.kd_comp', '=', 'plotting.kd_comp');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agro_hdr.kd_blok', '=', 'blok.kd_blok')
                    ->whereColumn('agro_hdr.kd_comp', '=', 'blok.kd_comp');
            })
            ->where('agro_lst.kd_comp', session('dropdown_value'))
            ->where('agro_hdr.kd_comp', session('dropdown_value'))
            ->where('agro_hdr.status', '=', 'Posted')
            ->where('agro_lst.status', '=', 'Posted')
            ->select(
                'perusahaan.nama as Perusahaan',
                'blok.kd_blok as Blok',
                'plotting.kd_plot as Plot',
                DB::raw("MONTH(agro_hdr.tglamat) as Bulan"),
                DB::raw("AVG(per_germinasi) as Germinasi"),
                DB::raw("AVG(per_gap) as GAP"),
                DB::raw("AVG(populasi) as Populasi"),
                DB::raw("AVG(per_gulma) as PenutupanGulma"),
                DB::raw("AVG(ph_tanah) as PHTanah")
            )
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('agro_hdr.tglamat', [$startDate, $endDate]);
            })
            ->groupBy('Bulan', 'Perusahaan', 'Blok', 'Plot')
            ->orderBy('Bulan', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pivot Table');

        $headers = ['Kebun', 'Blok', 'Plot', 'Bulan', 'Average of %Germinasi', 'Average of %GAP', 'Average of Populasi', 'Average of %Penutupan Gulma', 'Average of pH Tanah'];
        $sheet->fromArray($headers, NULL, 'A1');
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ADD8E6'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $columnIndex = 1;
        foreach ($headers as $header) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            $columnIndex++;
        }
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        $previousCompany = null;
        $previousBlok = null;
        $previousPlot = null;
        $startMergeCompanyRow = 2;
        $startMergeBlokRow = 2;
        $startMergePlotRow = 2;
        foreach ($chartDataQuery as $index => $data) {
            $currentCompany = $data->Perusahaan;
            $currentBlok = $data->Blok;
            $currentPlot = $data->Plot;

            if ($previousCompany !== null && $currentCompany === $previousCompany) {
                $sheet->setCellValue("A$row", '');
            } else {
                $sheet->setCellValue("A$row", $currentCompany);
                $startMergeCompanyRow = $row;
            }

            if ($previousBlok !== null && $currentBlok === $previousBlok && $currentCompany === $previousCompany) {
                $sheet->setCellValue("B$row", '');
            } else {
                $sheet->setCellValue("B$row", $currentBlok);
                $startMergeBlokRow = $row;
            }

            if ($previousPlot !== null && $currentPlot === $previousPlot && $currentBlok === $previousBlok && $currentCompany === $previousCompany) {
                $sheet->setCellValue("C$row", '');
            } else {
                $sheet->setCellValue("C$row", $currentPlot);
                $startMergePlotRow = $row;
            }

            $sheet->fromArray([
                '',
                '',
                '',
                Carbon::createFromFormat('m', $data->Bulan)->translatedFormat('F'),
                round($data->Germinasi, 4),
                round($data->GAP, 4),
                round($data->Populasi, 4),
                round($data->PenutupanGulma, 4),
                round($data->PHTanah, 4),
            ], NULL, "A$row");

            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->Perusahaan !== $currentCompany) {
                if ($row > $startMergeCompanyRow) {
                    $sheet->mergeCells("A{$startMergeCompanyRow}:A{$row}");
                    $sheet->getStyle("A{$startMergeCompanyRow}:A{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->Blok !== $currentBlok || $chartDataQuery[$index + 1]->Perusahaan !== $currentCompany) {
                if ($row > $startMergeBlokRow) {
                    $sheet->mergeCells("B{$startMergeBlokRow}:B{$row}");
                    $sheet->getStyle("B{$startMergeBlokRow}:B{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            if (!isset($chartDataQuery[$index + 1]) || $chartDataQuery[$index + 1]->Plot !== $currentPlot || $chartDataQuery[$index + 1]->Blok !== $currentBlok || $chartDataQuery[$index + 1]->Perusahaan !== $currentCompany) {
                if ($row > $startMergePlotRow) {
                    $sheet->mergeCells("C{$startMergePlotRow}:C{$row}");
                    $sheet->getStyle("C{$startMergePlotRow}:C{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
            }

            $previousCompany = $currentCompany;
            $previousBlok = $currentBlok;
            $previousPlot = $currentPlot;

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        if ($startDate && $endDate) {
            $fileName = "AgronomiPivot_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $fileName = "AgronomiPivot.xlsx";
        }
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function pivotTableHPT(Request $request)
    {
        $kdCompAgronomi = $request->input('kd_comp', []);
        $kdBlokAgronomi = $request->input('kd_blok', []);
        $kdPlotAgronomi = $request->input('kd_plot', []);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query untuk mendapatkan data pivot table yang sama dengan fungsi agronomi
        $chartDataQuery = DB::table('hpt_hdr')
            ->join('hpt_lst', function ($join) {
                $join->on('hpt_hdr.no_sample', '=', 'hpt_lst.no_sample')
                    ->on('hpt_hdr.kd_comp', '=', 'hpt_lst.kd_comp');
            })
            ->join('plotting', function ($join) {
                $join->on('hpt_hdr.kd_plot', '=', 'plotting.kd_plot')
                    ->on('hpt_hdr.kd_comp', '=', 'plotting.kd_comp');
            })
            ->join('perusahaan', 'hpt_hdr.kd_comp', '=', 'perusahaan.kd_comp')
            ->leftJoin('blok', function ($join) {
                $join->on('hpt_hdr.kd_blok', '=', 'blok.kd_blok')
                    ->whereColumn('hpt_hdr.kd_comp', '=', 'blok.kd_comp');
            })
            ->select(
                'perusahaan.nama as Perusahaan',
                'blok.kd_blok as Blok',
                'plotting.kd_plot as Plot',
                DB::raw("MONTH(hpt_hdr.tglamat) as Bulan"),
                DB::raw("AVG(per_ppt) as PPT"),
                DB::raw("AVG(per_pbt) as PBT"),
                DB::raw("AVG(dh) as DH"),
                DB::raw("AVG(dt) as DT"),
                DB::raw("AVG(kbp) as KBP"),
                DB::raw("AVG(kbb) as KBB"),
                DB::raw("AVG(kp) as KP"),
                DB::raw("AVG(cabuk) as Cabuk"),
                DB::raw("AVG(belalang) as Belalang"),
                DB::raw("AVG(grayak) as Grayak"),
                DB::raw("AVG(serang_smut) as SMUT"),
            )
            ->when($kdCompAgronomi, function ($query) use ($kdCompAgronomi) {
                return $query->whereIn('hpt_hdr.kd_comp', $kdCompAgronomi);
            })
            ->when($kdBlokAgronomi, function ($query) use ($kdBlokAgronomi) {
                return $query->whereIn('hpt_hdr.kd_blok', $kdBlokAgronomi);
            })
            ->when($kdPlotAgronomi, function ($query) use ($kdPlotAgronomi) {
                return $query->whereIn('hpt_hdr.kd_plot', $kdPlotAgronomi);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('hpt_hdr.tglamat', [$startDate, $endDate]);
            })
            ->groupBy('Bulan', 'Perusahaan', 'Blok', 'Plot')
            ->orderBy('Perusahaan', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pivot Table');

        $headers = [
            'Kebun',
            'Blok',
            'Plot',
            'Bulan',
            'Average of %PPT',
            'Average of %PBT',
            'Average of Dead Heart',
            'Average of Dead Top',
            'Average of Kutu Bulu Putih',
            'Average of Kutu Bulu Babi',
            'Average of Kutu Perisai',
            'Average of Cabuk',
            'Average of Belalang',
            'Average of Ulat Grayak',
            'Average of BTG Terserang SMUT',
        ];
        $sheet->fromArray($headers, NULL, 'A1');
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($chartDataQuery as $data) {
            $sheet->fromArray([
                $data->Perusahaan,
                $data->Blok,
                $data->Plot,
                Carbon::createFromFormat('m', $data->Bulan)->translatedFormat('F'),
                round($data->PPT, 4),
                round($data->PBT, 4),
                round($data->DH, 4),
                round($data->DT, 4),
                round($data->KBP, 4),
                round($data->KBB, 4),
                round($data->KP, 4),
                round($data->Cabuk, 4),
                round($data->Belalang, 4),
                round($data->Grayak, 4),
                round($data->SMUT, 4),
            ], NULL, "A$row");
            $sheet->getStyle('E' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $sheet->getStyle('F' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

            $row++;
        }

        if ($startDate && $endDate) {
            $fileName = "HPT_PivotTable_{$startDate}_sd_{$endDate}.xlsx";
        } else {
            $fileName = "HPT_PivotTable.xlsx";
        }
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
