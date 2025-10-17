<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MobileController extends Controller
{

    private function mapMobileToWebFields(array $data, string $type, string $transaksi): array
    {
        $mappingHeader = [
            'no_sample' => 'nosample',
            'kd_comp' => 'companycode',
            'kd_blok' => 'blok',
            'kd_plot' => 'plot',
            'kd_plotsample' => 'idblokplot',
            'varietas' => 'varietas',
            'kat' => 'kat',
            'tgltanam' => 'tanggaltanam',
            'tglamat' => 'tanggalpengamatan',
            'user_input' => 'inputby',
            'created_at' => 'createdat',
            'updated_at' => 'updatedat',
        ];

        $mappingAgronomiList = [
            'no_sample' => 'nosample',
            'kd_comp' => 'companycode',
            'tgltanam' => 'tanggaltanam',
            'tglamat' => 'tanggalpengamatan',
            'kat' => 'kat',
            'no_urut' => 'nourut',
            'jm_batang' => 'jumlahbatang',
            'pan_gap' => 'pan_gap',
            'per_gap' => 'per_gap',
            'per_germinasi' => 'per_germinasi',
            'ph_tanah' => 'ph_tanah',
            'populasi' => 'populasi',
            'ktk_gulma' => 'ktk_gulma',
            'per_gulma' => 'per_gulma',
            't_primer' => 't_primer',
            't_sekunder' => 't_sekunder',
            't_tersier' => 't_tersier',
            't_kuarter' => 't_kuarter',
            'd_primer' => 'd_primer',
            'd_sekunder' => 'd_sekunder',
            'd_tersier' => 'd_tersier',
            'd_kuarter' => 'd_kuarter',
            'user_input' => 'inputby',
            'created_at' => 'createdat',
            'updated_at' => 'updatedat',
        ];

        $mappingHptList = [
            'no_sample' => 'nosample',
            'kd_comp' => 'companycode',
            'tgltanam' => 'tanggaltanam',
            'tglamat' => 'tanggalpengamatan',
            'kat' => 'kat',
            'no_urut' => 'nourut',
            'jm_batang' => 'jumlahbatang',
            'ppt' => 'ppt',
            'ppt_aktif' => 'ppt_aktif',
            'pbt' => 'pbt',
            'pbt_aktif' => 'pbt_aktif',
            'skor0' => 'skor0',
            'skor1' => 'skor1',
            'skor2' => 'skor2',
            'skor3' => 'skor3',
            'skor4' => 'skor4',
            'per_ppt' => 'per_ppt',
            'per_ppt_aktif' => 'per_ppt_aktif',
            'per_pbt' => 'per_pbt',
            'per_pbt_aktif' => 'per_pbt_aktif',
            'sum_ni' => 'sum_ni',
            'int_rusak' => 'int_rusak',
            'telur_ppt' => 'telur_ppt',
            'larva_ppt1' => 'larva_ppt1',
            'larva_ppt2' => 'larva_ppt2',
            'larva_ppt3' => 'larva_ppt3',
            'larva_ppt4' => 'larva_ppt4',
            'pupa_ppt' => 'pupa_ppt',
            'ngengat_ppt' => 'ngengat_ppt',
            'kosong_ppt' => 'kosong_ppt',
            'telur_pbt' => 'telur_pbt',
            'larva_pbt1' => 'larva_pbt1',
            'larva_pbt2' => 'larva_pbt2',
            'larva_pbt3' => 'larva_pbt3',
            'larva_pbt4' => 'larva_pbt4',
            'pupa_pbt' => 'pupa_pbt',
            'ngengat_pbt' => 'ngengat_pbt',
            'kosong_pbt' => 'kosong_pbt',
            'dh' => 'dh',
            'dt' => 'dt',
            'kbp' => 'kbp',
            'kbb' => 'kbb',
            'kp' => 'kp',
            'cabuk' => 'cabuk',
            'belalang' => 'belalang',
            'serang_grayak' => 'serang_grayak',
            'jum_grayak' => 'jum_grayak',
            'serang_smut' => 'serang_smut',
            'smut_stadia1' => 'smut_stadia1',
            'smut_stadia2' => 'smut_stadia2',
            'smut_stadia3' => 'smut_stadia3',
            'jum_larva_ppt' => 'jum_larva_ppt',
            'jum_larva_pbt' => 'jum_larva_pbt',
            'user_input' => 'inputby',
            'created_at' => 'createdat',
            'updated_at' => 'updatedat',
        ];

        $map = match ($transaksi) {
            'hpt' => ($type === 'header' ? $mappingHeader : $mappingHptList),
            'agronomi' => ($type === 'header' ? $mappingHeader : $mappingAgronomiList),
        };

        $converted = [];
        foreach ($data as $key => $value) {
            $converted[$map[$key] ?? $key] = $value;
        }

        return $converted;
    }

    public function loginMobile(Request $request)
    {
        $data = $request->validate([
            'usernm' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DB::table('user')->where('userid', $data['usernm'])->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username or password is incorrect'
            ], 404);
        }

        if (!password_verify($data['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username or password is incorrect'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'usernm' => $user->userid,
            'name' => $user->name,
        ]);
    }
    public function getFieldByMapping(Request $request)
    {
        $idblokplot = $request->input('kd_plotsample');
        $companycode = $request->input('kd_comp');
        $mapping = DB::table('mapping')->where('idblokplot', $idblokplot)
            ->where('companycode', $companycode)->first();

        if ($mapping) {
            return response()->json([
                'kd_blok' => $mapping->blok,
                'kd_plot' => $mapping->plot,
            ]);
        }

        return response()->json(['message' => 'Data not found'], 404);
    }

    public function checkDataAgronomi(Request $request)
    {
        $nosample = $request->get('no_sample');
        $idblokplot = $request->get('kd_plotsample');
        $companycode = $request->get('kd_comp');

        $data = DB::table('agrohdr')
            ->where('nosample', $nosample)
            ->where('idblokplot', $idblokplot)
            ->where('companycode', $companycode)
            ->first();

        if ($data) {
            return response()->json([
                'success' => true,
                'kat' => $data->kat,
                'varietas' => $data->varietas,
                'tgltanam' => $data->tanggaltanam,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data not found',
        ]);
    }

    public function checkDataHPT(Request $request)
    {
        $nosample = $request->get('no_sample');
        $idblokplot = $request->get('kd_plotsample');
        $companycode = $request->get('kd_comp');

        $data = DB::table('hpthdr')
            ->where('nosample', $nosample)
            ->where('idblokplot', $idblokplot)
            ->where('companycode', $companycode)
            ->first();

        if ($data) {
            return response()->json([
                'success' => true,
                'kat' => $data->kat,
                'varietas' => $data->varietas,
                'tgltanam' => $data->tanggaltanam,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data not found',
        ]);
    }

    public function getCompaniesMobile(Request $request)
    {
        $userid = $request->query('usernm');

        if (!$userid) {
            return response()->json(['error' => 'Missing username'], 400);
        }

        $result = DB::table('usercompany')
            ->where('userid', $userid)
            ->value('companycode');

        if (!$result) {
            return response()->json(['companies' => []]);
        }

        return response()->json([
            'companies' => $result
        ]);
    }

    public function storeMobileAgronomi(Request $request): JsonResponse
    {
        $input = $request->all();
        $errors = [];
        $allSuccess = true;

        DB::beginTransaction();

        try {
            $headerData = [];
            $listData = [];

            foreach ($input as $type => $dataList) {
                if (!is_array($dataList))
                    continue;

                switch ($type) {
                    case 'header':
                        $headerData = $dataList;
                        break;
                    case 'list':
                        $listData = $dataList;
                        break;
                    default:
                        $errors[] = "Unknown type '$type'. Skipping.";
                        break;
                }
            }

            foreach ($headerData as $item) {
                $validator = Validator::make($item, [
                    'no_sample' => 'required|string',
                    'kd_comp' => 'required|string',
                    'kd_blok' => 'required|string',
                    'kd_plot' => 'required|string',
                    'kd_plotsample' => 'required|string',
                    'varietas' => 'required|string',
                    'kat' => 'required|string',
                    'tgltanam' => 'required|date',
                    'tglamat' => 'required|date',
                    'user_input' => 'required|string',
                    'created_at' => 'required|date',
                    'updated_at' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $allSuccess = false;
                    $errors[] = "Header invalid (no_sample " . ($item['no_sample'] ?? 'N/A') . "): " . implode(", ", $validator->errors()->all());
                    continue;
                }

                $mapped = $this->mapMobileToWebFields($item, 'header', 'agronomi');

                DB::table('agrohdr')->updateOrInsert(
                    [
                        'nosample' => $mapped['nosample'],
                        'companycode' => $mapped['companycode'],
                        'tanggalpengamatan' => $mapped['tanggalpengamatan']
                    ],
                    $mapped
                );
            }

            $totalPerGerminasi = 0;
            $totalPerGulma = 0;
            $count = 0;
            $firstList = null;

            foreach ($listData as $item) {
                $validator = Validator::make($item, [
                    'no_sample' => 'required|string',
                    'kd_comp' => 'required|string',
                    'tgltanam' => 'required|date',
                    'tglamat' => 'required|date',
                    'kat' => 'required|string',
                    'no_urut' => 'required|integer',
                    'jm_batang' => 'required|integer',
                    'pan_gap' => 'required|numeric',
                    'per_gap' => 'required|numeric',
                    'per_germinasi' => 'required|numeric',
                    'ph_tanah' => 'required|numeric',
                    'populasi' => 'required|numeric',
                    'ktk_gulma' => 'required|numeric',
                    'per_gulma' => 'required|numeric',
                    't_primer' => 'required|integer',
                    't_sekunder' => 'required|integer',
                    't_tersier' => 'required|integer',
                    't_kuarter' => 'required|integer',
                    'd_primer' => 'required|numeric',
                    'd_sekunder' => 'required|numeric',
                    'd_tersier' => 'required|numeric',
                    'd_kuarter' => 'required|numeric',
                    'user_input' => 'required|string',
                    'created_at' => 'required|date',
                    'updated_at' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $allSuccess = false;
                    $errors[] = "List invalid (no_sample " . ($item['no_sample'] ?? 'N/A') . "): " . implode(", ", $validator->errors()->all());
                    continue;
                }

                $mapped = $this->mapMobileToWebFields($item, 'list', 'agronomi');

                DB::table('agrolst')->updateOrInsert(
                    [
                        'nosample' => $mapped['nosample'],
                        'companycode' => $mapped['companycode'],
                        'tanggalpengamatan' => $mapped['tanggalpengamatan'],
                        'nourut' => $mapped['nourut']
                    ],
                    $mapped
                );

                $totalPerGerminasi += $item['per_germinasi'];
                $totalPerGulma += $item['per_gulma'];
                $count++;

                if (!$firstList)
                    $firstList = $item;
            }

            $avgPerGerminasi = $count > 0 ? $totalPerGerminasi / $count : 1;
            $avgPerGulma = $count > 0 ? $totalPerGulma / $count : 0;
            $umurTanam = $firstList
                ? Carbon::parse($firstList['tgltanam'])->diffInMonths(Carbon::now())
                : 0;

            if (($avgPerGerminasi < 0.9 && $umurTanam == 1.0) || $avgPerGulma > 0.25) {
                Notification::createForAgronomi([
                    'plot' => $firstList['kd_plot'] ?? '-',
                    'companycode' => $firstList['kd_comp'] ?? '-',
                    'condition' => [
                        'germinasi' => $avgPerGerminasi,
                        'gulma' => $avgPerGulma,
                        'umur' => $umurTanam,
                    ]
                ]);
            }

            if ($allSuccess) {
                DB::commit();
                return response()->json(['status' => 'success']);
            } else {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $errors], 422);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function storeMobileHPT(Request $request): JsonResponse
    {
        $input = $request->all();
        $errors = [];
        $allSuccess = true;

        DB::beginTransaction();

        try {
            $headerData = [];
            $listData = [];

            foreach ($input as $type => $dataList) {
                if (!is_array($dataList))
                    continue;

                switch ($type) {
                    case 'header':
                        $headerData = $dataList;
                        break;
                    case 'list':
                        $listData = $dataList;
                        break;
                    default:
                        $errors[] = "Unknown type '$type'. Skipping.";
                        break;
                }
            }

            foreach ($headerData as $item) {
                $validator = Validator::make($item, [
                    'no_sample' => 'required|string',
                    'kd_comp' => 'required|string',
                    'kd_blok' => 'required|string',
                    'kd_plot' => 'required|string',
                    'kd_plotsample' => 'required|string',
                    'varietas' => 'required|string',
                    'kat' => 'required|string',
                    'tgltanam' => 'required|date',
                    'tglamat' => 'required|date',
                    'user_input' => 'required|string',
                    'created_at' => 'required|date',
                    'updated_at' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $allSuccess = false;
                    $errors[] = "List invalid (no_sample " . ($item['no_sample'] ?? 'N/A') . "): " . implode(", ", $validator->errors()->all());
                    continue;
                }

                $mapped = $this->mapMobileToWebFields($item, 'header', 'hpt');

                DB::table('hpthdr')->updateOrInsert(
                    [
                        'nosample' => $mapped['nosample'],
                        'companycode' => $mapped['companycode'],
                        'tanggalpengamatan' => $mapped['tanggalpengamatan']
                    ],
                    $mapped
                );
            }

            $totalPerPPT = 0;
            $totalPerPBT = 0;
            $count = 0;
            $firstList = null;

            foreach ($listData as $item) {
                $validator = Validator::make($item, [
                    'no_sample' => 'required|string',
                    'kd_comp' => 'required|string',
                    'tgltanam' => 'required|date',
                    'tglamat' => 'required|date',
                    'kat' => 'required|string',
                    'no_urut' => 'required|integer',
                    'jm_batang' => 'required|integer',
                    'ppt' => 'required|integer',
                    'ppt_aktif' => 'required|integer',
                    'pbt' => 'required|integer',
                    'pbt_aktif' => 'required|integer',
                    'skor0' => 'required|integer',
                    'skor1' => 'required|integer',
                    'skor2' => 'required|integer',
                    'skor3' => 'required|integer',
                    'skor4' => 'required|integer',
                    'per_ppt' => 'required|numeric',
                    'per_ppt_aktif' => 'required|numeric',
                    'per_pbt' => 'required|numeric',
                    'per_pbt_aktif' => 'required|numeric',
                    'sum_ni' => 'required|integer',
                    'int_rusak' => 'required|numeric',
                    'telur_ppt' => 'required|integer',
                    'larva_ppt1' => 'required|integer',
                    'larva_ppt2' => 'required|integer',
                    'larva_ppt3' => 'required|integer',
                    'larva_ppt4' => 'required|integer',
                    'pupa_ppt' => 'required|integer',
                    'ngengat_ppt' => 'required|integer',
                    'kosong_ppt' => 'required|integer',
                    'telur_pbt' => 'required|integer',
                    'larva_pbt1' => 'required|integer',
                    'larva_pbt2' => 'required|integer',
                    'larva_pbt3' => 'required|integer',
                    'larva_pbt4' => 'required|integer',
                    'pupa_pbt' => 'required|integer',
                    'ngengat_pbt' => 'required|integer',
                    'kosong_pbt' => 'required|integer',
                    'dh' => 'required|integer',
                    'dt' => 'required|integer',
                    'kbp' => 'required|integer',
                    'kbb' => 'required|integer',
                    'kp' => 'required|integer',
                    'cabuk' => 'required|integer',
                    'belalang' => 'required|integer',
                    'serang_grayak' => 'required|integer',
                    'jum_grayak' => 'required|integer',
                    'serang_smut' => 'required|integer',
                    'smut_stadia1' => 'required|integer',
                    'smut_stadia2' => 'required|integer',
                    'smut_stadia3' => 'required|integer',
                    'jum_larva_ppt' => 'required|integer',
                    'jum_larva_pbt' => 'required|integer',
                    'user_input' => 'required|string',
                    'created_at' => 'required|date',
                    'updated_at' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $allSuccess = false;
                    $errors[] = "List invalid (no_sample " . ($item['no_sample'] ?? 'N/A') . "): " . implode(", ", $validator->errors()->all());
                    continue;
                }

                $mapped = $this->mapMobileToWebFields($item, 'list', 'hpt');

                DB::table('hptlst')->updateOrInsert(
                    [
                        'nosample' => $item['nosample'],
                        'companycode' => $item['companycode'],
                        'tanggalpengamatan' => $item['tanggalpengamatan'],
                        'nourut' => $item['nourut']
                    ],
                    $item
                );
                $totalPerPPT += $item['per_ppt'];
                $totalPerPBT += $item['per_pbt'];
                $count++;

                if (!$firstList)
                    $firstList = $item;
            }

            $avgPPT = $count > 0 ? $totalPerPPT / $count : 0;
            $avgPBT = $count > 0 ? $totalPerPBT / $count : 0;
            $umurTanam = $firstList
                ? Carbon::parse($firstList['tgltanam'])->diffInMonths(Carbon::now())
                : 0;

            if (
                ($avgPBT > 0.03 && $umurTanam >= 1 && $umurTanam <= 3) ||
                ($avgPPT > 0.03 && $umurTanam >= 1 && $umurTanam <= 3) ||
                ($avgPBT > 0.05 && $umurTanam >= 4) ||
                ($avgPPT > 0.05 && $umurTanam >= 4)
            ) {
                Notification::createForHPT([
                    'plot' => $firstList['plot'],
                    'companycode' => $firstList['companycode'],
                    'condition' => [
                        'ppt' => $avgPPT,
                        'pbt' => $avgPBT,
                        'umur' => $umurTanam,
                    ]
                ]);
            }

            if ($allSuccess) {
                DB::commit();
                return response()->json(['status' => 'success']);
            } else {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $errors], 422);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
