<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Upah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Timbangan extends Controller
{
    public function insertData(Request $request)
    {
        try {
            // Validasi bahwa request berisi array data
            $requestData = $request->all();

            if (empty($requestData) || !is_array($requestData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak valid atau kosong'
                ], 400);
            }

            // Array untuk menyimpan data yang berhasil diinsert
            $insertedData = [];
            $failedData = [];
            $duplicateData = [];

            // Loop untuk setiap item dalam array
            foreach ($requestData as $index => $item) {
                try {
                    // Validasi field yang required (jika ada)
                    if (empty($item['NOM'])) {
                        throw new \Exception("Field NOM tidak boleh kosong untuk record index {$index}");
                    }

                    // Cek duplikat berdasarkan kombinasi divisi dan sjalan
                    $divisi = $item['DIVISI'] ?? '';
                    $sjalan = $item['SJALAN'] ?? '';

                    $existingData = DB::table('timbanganpayload')
                        ->where('companycode', $divisi)
                        ->where('suratjalanno', $sjalan)
                        ->first();

                    if ($existingData) {
                        $duplicateData[] = [
                            'index' => $index,
                            'nom' => $item['NOM'] ?? 'unknown',
                            'companycode' => $divisi,
                            'suratjalanno' => $sjalan,
                            'message' => 'Data sudah ada dengan kombinasi divisi dan sjalan yang sama'
                        ];
                        continue; // Skip insert untuk data duplikat
                    }

                    // Mapping data sesuai dengan kolom tabel timbanganpayload
                    $mappedData = [
                        'payload' => json_encode($item), // Simpan data asli sebagai JSON
                        'nom' => $item['NOM'] ?? '',
                        'companycode' => $divisi,
                        'suratjalanno' => $sjalan,
                        'tgl1' => isset($item['TGL1']) && !empty($item['TGL1'])
                                    ? Carbon::createFromFormat('Y-m-d', $item['TGL1'])
                                    : null,
                        'jam1' => $item['JAM1'] ?? '',
                        'tgl2' => isset($item['TGL2']) && !empty($item['TGL2'])
                                    ? Carbon::createFromFormat('Y-m-d', $item['TGL2'])
                                    : null,
                        'jam2' => $item['JAM2'] ?? '',
                        'nopol' => $item['NOPOL'] ?? '',
                        'jnsk' => $item['JNSK'] ?? '',
                        'supl' => $item['SUPL'] ?? '',
                        'gsupl' => $item['GSUPL'] ?? '',
                        'area' => $item['AREA'] ?? '',
                        'item' => $item['ITEM'] ?? '',
                        'note' => $item['NOTE'] ?? '',
                        'ket1' => $item['KET1'] ?? '',
                        'ket2' => $item['KET2'] ?? '',
                        'ket3' => $item['KET3'] ?? '',
                        'donom' => $item['DONOM'] ?? '',
                        'dotgl' => isset($item['DOTGL']) && $item['DOTGL'] !== '0000-00-00' && !empty($item['DOTGL'])
                                    ? Carbon::createFromFormat('Y-m-d', $item['DOTGL'])
                                    : null,
                        'bruto' => isset($item['BRUTO']) ? (float)$item['BRUTO'] : 0.00,
                        'brkend' => isset($item['BRKEND']) ? (float)$item['BRKEND'] : 0.00,
                        'raf' => isset($item['RAF']) ? (float)$item['RAF'] : 0.00,
                        'traf' => isset($item['TRAF']) ? (float)$item['TRAF'] : 0.00,
                        'netto' => isset($item['NETTO']) ? (float)$item['NETTO'] : 0.00,
                        'usr1' => $item['USR1'] ?? '',
                        'usr2' => $item['USR2'] ?? '',
                        'createddate' => Carbon::now()
                    ];

                    // Insert ke tabel timbanganpayload
                    $result = DB::table('timbanganpayload')->insert($mappedData);

                    if ($result) {
                        $insertedData[] = [
                            'index' => $index,
                            'nom' => $mappedData['nom'],
                            'companycode' => $mappedData['companycode'],
                            'suratjalanno' => $mappedData['suratjalanno'],
                            'status' => 'success'
                        ];
                    } else {
                        $failedData[] = [
                            'index' => $index,
                            'nom' => $item['NOM'] ?? 'unknown',
                            'error' => 'Insert failed'
                        ];
                    }

                } catch (\Exception $e) {
                    $failedData[] = [
                        'index' => $index,
                        'nom' => $item['NOM'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];

                    Log::error("Error inserting timbangan data: " . $e->getMessage(), [
                        'data' => $item,
                        'index' => $index
                    ]);
                }
            }

            // Response berdasarkan hasil
            $response = [
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'summary' => [
                    'total_received' => count($requestData),
                    'total_inserted' => count($insertedData),
                    'total_failed' => count($failedData),
                    'total_duplicate' => count($duplicateData)
                ],
                'inserted_data' => $insertedData
            ];

            // Tambahkan informasi duplikat jika ada
            if (count($duplicateData) > 0) {
                $response['duplicate_data'] = $duplicateData;
            }

            // Tambahkan informasi failed jika ada
            if (count($failedData) > 0) {
                $response['failed_data'] = $failedData;
            }

            // Tentukan status berdasarkan hasil
            if (count($insertedData) > 0) {
                if (count($failedData) > 0 || count($duplicateData) > 0) {
                    // $response['status'] = 'partial_success';
                    // $response['message'] = 'Sebagian data berhasil disimpan';
                    $response['status'] = 'success';
                    $response['message'] = 'Data berhasil disimpan';
                }
                return response()->json($response, 200);
            } else {
                // $response['status'] = 'error';
                // $response['message'] = 'Tidak ada data yang berhasil disimpan';
                $response['status'] = 'success';
                $response['message'] = 'Data berhasil disimpan';
                return response()->json($response, 200);
            }

        } catch (\Exception $e) {
            Log::error("Error in insertData: " . $e->getMessage(), [
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternative method - Insert data individually
     */
    public function insertSingleData(Request $request)
    {
        try {
            $data = $request->all();

            // Validasi data
            if (!isset($data['NOM']) || empty($data['NOM'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Field NOM wajib diisi'
                ], 400);
            }

            // Cek duplikat berdasarkan kombinasi divisi dan sjalan
            $divisi = $data['DIVISI'] ?? '';
            $sjalan = $data['SJALAN'] ?? '';

            $existingData = DB::table('timbanganpayload')
                ->where('companycode', $divisi)
                ->where('suratjalanno', $sjalan)
                ->first();

            if ($existingData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data sudah ada dengan kombinasi divisi dan sjalan yang sama',
                    'existing_data' => [
                        'companycode' => $divisi,
                        'suratjalanno' => $sjalan
                    ]
                ], 409); // 409 Conflict
            }

            $mappedData = [
                'payload' => json_encode($data),
                'nom' => $data['NOM'],
                'companycode' => $divisi,
                'suratjalanno' => $sjalan,
                'tgl1' => isset($data['TGL1']) && !empty($data['TGL1'])
                            ? Carbon::createFromFormat('Y-m-d', $data['TGL1'])
                            : null,
                'jam1' => $data['JAM1'] ?? '',
                'tgl2' => isset($data['TGL2']) && !empty($data['TGL2'])
                            ? Carbon::createFromFormat('Y-m-d', $data['TGL2'])
                            : null,
                'jam2' => $data['JAM2'] ?? '',
                'nopol' => $data['NOPOL'] ?? '',
                'jnsk' => $data['JNSK'] ?? '',
                'supl' => $data['SUPL'] ?? '',
                'gsupl' => $data['GSUPL'] ?? '',
                'area' => $data['AREA'] ?? '',
                'item' => $data['ITEM'] ?? '',
                'note' => $data['NOTE'] ?? '',
                'ket1' => $data['KET1'] ?? '',
                'ket2' => $data['KET2'] ?? '',
                'ket3' => $data['KET3'] ?? '',
                'donom' => $data['DONOM'] ?? '',
                'dotgl' => isset($data['DOTGL']) && $data['DOTGL'] !== '0000-00-00' && !empty($data['DOTGL'])
                            ? Carbon::createFromFormat('Y-m-d', $data['DOTGL'])
                            : null,
                'bruto' => isset($data['BRUTO']) ? (float)$data['BRUTO'] : 0.00,
                'brkend' => isset($data['BRKEND']) ? (float)$data['BRKEND'] : 0.00,
                'raf' => isset($data['RAF']) ? (float)$data['RAF'] : 0.00,
                'traf' => isset($data['TRAF']) ? (float)$data['TRAF'] : 0.00,
                'netto' => isset($data['NETTO']) ? (float)$data['NETTO'] : 0.00,
                'usr1' => $data['USR1'] ?? '',
                'usr2' => $data['USR2'] ?? '',
                'createddate' => Carbon::now()
            ];

            // Insert ke tabel timbanganpayload
            $result = DB::table('timbanganpayload')->insert($mappedData);

            if ($result) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan',
                    'data' => [
                        'nom' => $mappedData['nom'],
                        'companycode' => $mappedData['companycode'],
                        'suratjalanno' => $mappedData['suratjalanno'],
                        'created_at' => $mappedData['createddate']->toDateTimeString()
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error("Error in insertSingleData: " . $e->getMessage(), [
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
