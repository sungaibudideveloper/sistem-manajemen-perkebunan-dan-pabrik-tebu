<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Dashboard',
        ]);
        
    }
    public function agronomi(Request $request)
    {
        $kdCompAgronomi = $request->input('kd_comp', []);
        $kdBlokAgronomi = $request->input('kd_blok', []);
        $kdPlotAgronomi = $request->input('kd_plot', []);
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');
        $title = "Dashboard Agronomi";
        $nav = "Agronomi";

        $verticalField = $request->input('vertical', 'per_germinasi');

        $verticalLabels = [
            'per_germinasi' => '% Germinasi',
            'per_gap' => '% GAP',
            'populasi' => 'Populasi',
            'per_gulma' => '% Penutupan Gulma',
            'ph_tanah' => 'pH Tanah',
        ];
        $verticalLabel = $verticalLabels[$verticalField] ?? ucfirst($verticalField);

        $chartData = [];
        $xAxis = [];

        $months = [
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12
        ];

        $monthsLabel = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $startMonthNum = $months[$startMonth] ?? null;
        $endMonthNum = $months[$endMonth] ?? null;

        if (!empty($kdCompAgronomi) || !empty($kdBlokAgronomi) || !empty($kdPlotAgronomi) || ($startMonthNum && $endMonthNum)) {
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
                ->select(
                    DB::raw("MONTH(agro_hdr.tglamat) as bln_amat"),
                    DB::raw("MIN(agro_hdr.tgltanam) as tgltanam"),
                    'agro_hdr.kat',
                    DB::raw("CASE 
                        WHEN '$verticalField' IN ('populasi', 'ph_tanah') 
                        THEN AVG($verticalField) 
                        ELSE AVG($verticalField) * 100 
                    END as total"),
                    'perusahaan.nama as perusahaan_nama',
                    'blok.kd_blok as blok_nama',
                    'plotting.kd_plot as plot_nama'
                )
                ->when($kdCompAgronomi, function ($query) use ($kdCompAgronomi) {
                    return $query->whereIn('agro_hdr.kd_comp', $kdCompAgronomi);
                })
                ->when($kdBlokAgronomi, function ($query) use ($kdBlokAgronomi) {
                    return $query->whereIn('agro_hdr.kd_blok', $kdBlokAgronomi);
                })
                ->when($kdPlotAgronomi, function ($query) use ($kdPlotAgronomi) {
                    return $query->whereIn('agro_hdr.kd_plot', $kdPlotAgronomi);
                })
                ->when($startMonthNum && $endMonthNum, function ($query) use ($startMonthNum, $endMonthNum) {
                    return $query->whereBetween(DB::raw("MONTH(agro_hdr.tglamat)"), [$startMonthNum, $endMonthNum]);
                })
                ->groupBy('bln_amat', 'kat', 'perusahaan.nama', 'blok.kd_blok', 'plotting.kd_plot')
                ->orderBy('kat');

            $chartDataResult = $chartDataQuery->get();
            $chartDataResult->transform(function ($item) {
                $item->umur_tanam = ceil(Carbon::parse($item->tgltanam)->diffInMonths(Carbon::now())) . ' Bulan';
                return $item;
            });

            $xAxis = $chartDataResult->map(function ($item) use ($kdCompAgronomi, $kdBlokAgronomi, $kdPlotAgronomi) {
                if (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                    return $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->perusahaan_nama;
                } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                    return $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->blok_nama;
                } elseif (empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->kat;
                } elseif (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->perusahaan_nama;
                } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->blok_nama;
                } elseif (!empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                    return $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->blok_nama . ' - ' . $item->perusahaan_nama;
                } else {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->kat . ' - ' . $item->blok_nama . ' - ' . $item->perusahaan_nama;
                }
            })->unique()->values();

            $legends = $chartDataResult->pluck('bln_amat')->unique();

            foreach ($legends as $legend) {
                $data = [];

                foreach ($xAxis as $x) {
                    $data[] = round(
                        $chartDataResult->filter(function ($item) use ($legend, $x, $kdCompAgronomi, $kdBlokAgronomi, $kdPlotAgronomi) {

                            if (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                                $umur_tanam = explode(' - ', $x)[0];
                                $kat = explode(' - ', $x)[1];
                                $blok = explode(' - ', $x)[2];
                                return $item->bln_amat == $legend && $item->umur_tanam == $umur_tanam && $item->kat == $kat && $item->blok_nama == $blok;
                            } elseif (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                                $umur_tanam = explode(' - ', $x)[0];
                                $kat = explode(' - ', $x)[1];
                                $perusahaan = explode(' - ', $x)[2];
                                return $item->bln_amat == $legend && $item->umur_tanam == $umur_tanam && $item->kat == $kat && $item->perusahaan_nama == $perusahaan;
                            } elseif (empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                                $plot = explode(' - ', $x)[0];
                                $umur_tanam = explode(' - ', $x)[1];
                                $kat = explode(' - ', $x)[2];
                                return $item->bln_amat == $legend && $item->plot_nama == $plot && $item->umur_tanam == $umur_tanam && $item->kat == $kat;
                            } elseif (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                                $plot = explode(' - ', $x)[0];
                                $umur_tanam = explode(' - ', $x)[1];
                                $kat = explode(' - ', $x)[2];
                                $perusahaan = explode(' - ', $x)[3];
                                return $item->bln_amat == $legend && $item->plot_nama == $plot && $item->umur_tanam == $umur_tanam && $item->kat == $kat && $item->perusahaan_nama == $perusahaan;
                            } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
                                $plot = explode(' - ', $x)[0];
                                $umur_tanam = explode(' - ', $x)[1];
                                $kat = explode(' - ', $x)[2];
                                $blok = explode(' - ', $x)[3];
                                return $item->bln_amat == $legend && $item->plot_nama == $plot && $item->umur_tanam == $umur_tanam && $item->kat == $kat && $item->blok_nama == $blok;
                            } elseif (!empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
                                $umur_tanam = explode(' - ', $x)[0];
                                $kat = explode(' - ', $x)[1];
                                $blok = explode(' - ', $x)[2];
                                $perusahaan = explode(' - ', $x)[3];
                                return $item->bln_amat == $legend && $item->umur_tanam == $umur_tanam && $item->kat == $kat && $item->blok_nama == $blok && $item->perusahaan_nama == $perusahaan;
                            } else {
                                $plot = explode(' - ', $x)[0];
                                $umur_tanam = explode(' - ', $x)[1];
                                $kat = explode(' - ', $x)[2];
                                $blok = explode(' - ', $x)[3];
                                $perusahaan = explode(' - ', $x)[4];
                                return $item->bln_amat == $legend && $item->plot_nama == $plot && $item->umur_tanam == $umur_tanam && $item->kat == $kat && $item->blok_nama == $blok && $item->perusahaan_nama == $perusahaan;
                            }
                        })->avg('total'),
                        2
                    );
                }

                $monthName = Carbon::createFromFormat('m', $legend)->translatedFormat('F');

                $chartData[] = [
                    'label' => $monthName,
                    'data' => $data,
                ];
            }
        }

        $kdCompAgroOpt = DB::table('perusahaan')
            ->join('agro_hdr', 'perusahaan.kd_comp', '=', 'agro_hdr.kd_comp')
            ->select('perusahaan.kd_comp', 'perusahaan.nama')
            ->distinct()
            ->get();
        $kdBlokAgroOpt = DB::table('blok')
            ->join('agro_hdr', 'blok.kd_blok', '=', 'agro_hdr.kd_blok')
            ->select('blok.kd_blok')
            ->distinct()
            ->get();
        $kdPlotAgroOpt = DB::table('plotting')
            ->join('agro_hdr', 'plotting.kd_plot', '=', 'agro_hdr.kd_plot')
            ->select('plotting.kd_plot')
            ->orderByRaw("LEFT(plotting.kd_plot, 1), CAST(SUBSTRING(plotting.kd_plot, 2) AS UNSIGNED)")
            ->distinct()
            ->get();

        if (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
            $horizontalLabel = 'Umur - Kategori - Kebun';
        } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
            $horizontalLabel = 'Umur - Kategori - Blok';
        } elseif (empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
            $horizontalLabel = 'Plot - Umur - Kategori';
        } elseif (!empty($kdCompAgronomi) && empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
            $horizontalLabel = 'Plot - Umur - Kategori - Kebun';
        } elseif (empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && !empty($kdPlotAgronomi)) {
            $horizontalLabel = 'Plot - Umur - Kategori - Blok';
        } elseif (!empty($kdCompAgronomi) && !empty($kdBlokAgronomi) && empty($kdPlotAgronomi)) {
            $horizontalLabel = 'Umur - Kategori - Blok - Kebun';
        } else {
            $horizontalLabel = 'Plot - Umur - Kategori - Blok - Kebun';
        }

        return view('dashboard.agronomi.index', compact(
            'chartData',
            'xAxis',
            'verticalField',
            'verticalLabel',
            'verticalLabels',
            'horizontalLabel',
            'kdCompAgronomi',
            'kdBlokAgronomi',
            'kdPlotAgronomi',
            'kdCompAgroOpt',
            'kdBlokAgroOpt',
            'kdPlotAgroOpt',
            'title',
            'nav',
            'startMonth',
            'endMonth',
            'monthsLabel'
        ));
    }

    public function hpt(Request $request)
    {
        $kdCompHPT = $request->input('kd_comp', []);
        $kdBlokHPT = $request->input('kd_blok', []);
        $kdPlotHPT = $request->input('kd_plot', []);
        $startMonth = $request->input('start_month');
        $endMonth = $request->input('end_month');
        $title = "Dashboard HPT";
        $nav = "HPT";

        $verticalField = $request->input('vertical', 'per_ppt');

        $verticalLabels = [
            'per_ppt' => '% PPT',
            'per_PBT' => '% PBT',
            'dh' => 'Dead Heart',
            'dt' => 'Dead Top',
            'kbp' => 'Kutu Bulu Putih',
            'kbb' => 'Kutu Bulu Babi',
            'kp' => 'Kutu Perisai',
            'cabuk' => 'Cabuk',
            'belalang' => 'Belalang',
            'jum_grayak' => 'Ulat Grayak',
            'serang_smut' => 'SMUT',
        ];
        $verticalLabel = $verticalLabels[$verticalField] ?? ucfirst($verticalField);

        $chartData = [];
        $xAxis = [];

        $months = [
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12
        ];

        $monthsLabel = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $startMonthNum = $months[$startMonth] ?? null;
        $endMonthNum = $months[$endMonth] ?? null;

        if (!empty($kdCompHPT) || !empty($kdBlokHPT) || !empty($kdPlotHPT) || ($startMonthNum && $endMonthNum)) {
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
                    DB::raw("MONTH(hpt_hdr.tglamat) as bln_amat"),
                    DB::raw("MIN(hpt_hdr.tgltanam) as tgltanam"),
                    DB::raw("CASE 
                        WHEN '$verticalField' IN ('per_ppt', 'per_pbt') 
                        THEN AVG($verticalField) * 100
                        ELSE AVG($verticalField) 
                    END as total"),
                    'perusahaan.nama as perusahaan_nama',
                    'blok.kd_blok as blok_nama',
                    'plotting.kd_plot as plot_nama'
                )
                ->when($kdCompHPT, function ($query) use ($kdCompHPT) {
                    return $query->whereIn('hpt_hdr.kd_comp', $kdCompHPT);
                })
                ->when($kdBlokHPT, function ($query) use ($kdBlokHPT) {
                    return $query->whereIn('hpt_hdr.kd_blok', $kdBlokHPT);
                })
                ->when($kdPlotHPT, function ($query) use ($kdPlotHPT) {
                    return $query->whereIn('hpt_hdr.kd_plot', $kdPlotHPT);
                })
                ->when($startMonthNum && $endMonthNum, function ($query) use ($startMonthNum, $endMonthNum) {
                    return $query->whereBetween(DB::raw("MONTH(hpt_hdr.tglamat)"), [$startMonthNum, $endMonthNum]);
                })
                ->groupBy('perusahaan.nama', 'blok.kd_blok', 'plotting.kd_plot', 'bln_amat')
                ->orderBy('plot_nama');


            $chartDataResult = $chartDataQuery->get();
            $chartDataResult->transform(function ($item) {
                $item->umur_tanam = ceil(Carbon::parse($item->tgltanam)->diffInMonths(Carbon::now())) . ' Bulan';
                return $item;
            });

            $xAxis = $chartDataResult->map(function ($item) use ($kdCompHPT, $kdBlokHPT, $kdPlotHPT) {
                if (!empty($kdCompHPT) && empty($kdBlokHPT) && empty($kdPlotHPT)) {
                    return $item->umur_tanam . ' - ' . $item->perusahaan_nama;
                } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                    return $item->umur_tanam . ' - ' . $item->blok_nama;
                } elseif (empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam;
                } elseif (!empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->perusahaan_nama;
                } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->blok_nama;
                } elseif (!empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                    return $item->umur_tanam . ' - ' . $item->blok_nama . ' - ' . $item->perusahaan_nama;
                } else {
                    return $item->plot_nama . ' - ' . $item->umur_tanam . ' - ' . $item->blok_nama . ' - ' . $item->perusahaan_nama;
                }
            })->unique()->values();

            $legends = $chartDataResult->pluck('bln_amat')->unique();

            foreach ($legends as $legend) {
                $data = [];

                foreach ($xAxis as $x) {
                    $data[] = round(
                        $chartDataResult->filter(function ($item) use ($legend, $x, $kdCompHPT, $kdBlokHPT, $kdPlotHPT) {

                            if (empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                                $umur_tanam = explode(' - ', $x)[0];
                                $blok = explode(' - ', $x)[1];
                                return $item->bln_amat == $legend && $item->umur_tanam == $umur_tanam && $item->blok_nama == $blok;
                            } elseif (!empty($kdCompHPT) && empty($kdBlokHPT) && empty($kdPlotHPT)) {
                                $umur_tanam = explode(' - ', $x)[0];
                                $perusahaan = explode(' - ', $x)[1];
                                return $item->bln_amat == $legend && $item->umur_tanam == $umur_tanam && $item->perusahaan_nama == $perusahaan;
                            } elseif (empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                                $plot = explode(' - ', $x)[0];
                                $umur_tanam = explode(' - ', $x)[1];
                                return $item->bln_amat == $legend && $item->plot_nama == $plot && $item->umur_tanam == $umur_tanam;
                            } elseif (!empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                                $plot = explode(' - ', $x)[0];
                                $umur_tanam = explode(' - ', $x)[1];
                                $perusahaan = explode(' - ', $x)[2];
                                return $item->bln_amat == $legend && $item->plot_nama == $plot && $item->umur_tanam == $umur_tanam && $item->perusahaan_nama == $perusahaan;
                            } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && !empty($kdPlotHPT)) {
                                $plot = explode(' - ', $x)[0];
                                $umur_tanam = explode(' - ', $x)[1];
                                $blok = explode(' - ', $x)[2];
                                return $item->bln_amat == $legend && $item->plot_nama == $plot && $item->umur_tanam == $umur_tanam && $item->blok_nama == $blok;
                            } elseif (!empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
                                $umur_tanam = explode(' - ', $x)[0];
                                $blok = explode(' - ', $x)[1];
                                $perusahaan = explode(' - ', $x)[2];
                                return $item->bln_amat == $legend && $item->umur_tanam == $umur_tanam && $item->blok_nama == $blok && $item->perusahaan_nama == $perusahaan;
                            } else {
                                $plot = explode(' - ', $x)[0];
                                $umur_tanam = explode(' - ', $x)[1];
                                $blok = explode(' - ', $x)[2];
                                $perusahaan = explode(' - ', $x)[3];
                                return $item->bln_amat == $legend && $item->plot_nama == $plot && $item->umur_tanam == $umur_tanam && $item->blok_nama == $blok && $item->perusahaan_nama == $perusahaan;
                            }
                        })->avg('total'),
                        2
                    );
                }

                $monthName = Carbon::createFromFormat('m', $legend)->translatedFormat('F');

                $chartData[] = [
                    'label' => $monthName,
                    'data' => $data,
                ];
            }
        }

        $kdCompHPTOpt = DB::table('perusahaan')
            ->join('hpt_hdr', 'perusahaan.kd_comp', '=', 'hpt_hdr.kd_comp')
            ->select('perusahaan.kd_comp', 'perusahaan.nama')
            ->distinct()
            ->get();
        $kdBlokHPTOpt = DB::table('blok')
            ->join('hpt_hdr', 'blok.kd_blok', '=', 'hpt_hdr.kd_blok')
            ->select('blok.kd_blok')
            ->distinct()
            ->get();
        $kdPlotHPTOpt = DB::table('plotting')
            ->join('hpt_hdr', 'plotting.kd_plot', '=', 'hpt_hdr.kd_plot')
            ->select('plotting.kd_plot')
            ->orderByRaw("LEFT(plotting.kd_plot, 1), CAST(SUBSTRING(plotting.kd_plot, 2) AS UNSIGNED)")
            ->distinct()
            ->get();

        if (!empty($kdCompHPT) && empty($kdBlokHPT) && empty($kdPlotHPT)) {
            $horizontalLabel = 'Umur - Kebun';
        } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
            $horizontalLabel = 'Umur - Blok';
        } elseif (empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
            $horizontalLabel = 'Plot - Umur';
        } elseif (!empty($kdCompHPT) && empty($kdBlokHPT) && !empty($kdPlotHPT)) {
            $horizontalLabel = 'Plot - Umur - Kebun';
        } elseif (empty($kdCompHPT) && !empty($kdBlokHPT) && !empty($kdPlotHPT)) {
            $horizontalLabel = 'Plot - Umur - Blok';
        } elseif (!empty($kdCompHPT) && !empty($kdBlokHPT) && empty($kdPlotHPT)) {
            $horizontalLabel = 'Umur - Blok - Kebun';
        } else {
            $horizontalLabel = 'Plot - Umur - Blok - Kebun';
        }

        return view('dashboard.hpt.index', compact(
            'chartData',
            'xAxis',
            'verticalField',
            'verticalLabel',
            'verticalLabels',
            'horizontalLabel',
            'kdCompHPT',
            'kdBlokHPT',
            'kdPlotHPT',
            'kdCompHPTOpt',
            'kdBlokHPTOpt',
            'kdPlotHPTOpt',
            'title',
            'nav',
            'startMonth',
            'endMonth',
            'monthsLabel'
        ));
    }
}
