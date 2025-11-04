<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Upah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Timbangan extends Controller
{
     public function insertData(Request $request)
        {
            try {
                // Mengambil seluruh body request sebagai JSON string
                $payload = json_encode($request->all());
                
                // Insert data ke table menggunakan Query Builder
                $result = DB::table('timbangan_payload')->insert([
                    'payload' => $payload,
                    'createddate' => Carbon::now()
                ]);
                
                if ($result) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data berhasil disimpan',
                        'data' => [
                            'payload' => $payload,
                            'createddate' => Carbon::now()->toDateTimeString()
                        ]
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan data'
                    ], 500);
                }
                
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
        }
}