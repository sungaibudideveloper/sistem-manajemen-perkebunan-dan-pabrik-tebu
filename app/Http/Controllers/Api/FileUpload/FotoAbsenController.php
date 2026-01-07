<?php

namespace App\Http\Controllers\Api\FileUpload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FotoAbsenController extends Controller
{
    /**
     * Upload foto absen masuk
     * POST /api/fileupload/foto-absen-masuk
     */
    public function uploadMasuk(Request $request)
    {
        return $this->uploadFoto($request, 'masuk');
    }
    
    /**
     * Upload foto absen pulang
     * POST /api/fileupload/foto-absen-pulang
     */
    public function uploadPulang(Request $request)
    {
        return $this->uploadFoto($request, 'pulang');
    }
    
    /**
     * Private method untuk handle upload
     */
    private function uploadFoto(Request $request, string $tipeAbsen)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'foto' => 'required|image|mimes:jpeg,jpg,png|max:5120',
                'absenno' => 'required|string|max:20',
                'tenagakerjaid' => 'required|string|max:11',
                'companycode' => 'required|string|max:4'
            ]);
            
            $file = $request->file('foto');
            $now = now();
            
            // Cek record berdasarkan absenno, tenagakerjaid, companycode
            $absen = DB::table('absenlst')
                ->where('absenno', $validated['absenno'])
                ->where('tenagakerjaid', $validated['tenagakerjaid'])
                ->where('companycode', $validated['companycode'])
                ->first();
            
            if (!$absen) {
                throw new \Exception('Data absen tidak ditemukan');
            }
            
            // Tentukan kolom
            $columnName = $tipeAbsen === 'masuk' ? 'fotoabsenmasuk' : 'fotoabsenpulang';
            
            // Cek apakah sudah upload
            if (!empty($absen->$columnName)) {
                throw new \Exception('Foto absen ' . $tipeAbsen . ' sudah pernah diupload');
            }
            
            // Generate S3 path (sama seperti sebelumnya)
            $tanggal = $absen->absenmasuk ?? $now;
            $year = date('Y', strtotime($tanggal));
            $month = date('m', strtotime($tanggal));
            $day = date('d', strtotime($tanggal));
            
            $filename = sprintf(
                '%s_%s_%s_%s.%s',
                $validated['tenagakerjaid'],
                $tipeAbsen,
                date('Ymd', strtotime($tanggal)),
                Str::random(6),
                $file->getClientOriginalExtension()
            );
            
            $path = sprintf(
                'absensi/%s/%s/%s/%s/%s',
                $validated['companycode'],
                $year, $month, $day,
                $filename
            );
            
            // Upload to S3
            Storage::disk('s3')->put($path, file_get_contents($file));
            $url = Storage::disk('s3')->url($path);
            
            // Update database (tanpa update waktu absen)
            DB::table('absenlst')
                ->where('absenno', $validated['absenno'])
                ->where('tenagakerjaid', $validated['tenagakerjaid'])
                ->where('companycode', $validated['companycode'])
                ->update([
                    $columnName => $path,
                    'updatedat' => $now
                ]);
            
            DB::commit();
            
            return response()->json([
                'status' => 1,
                'description' => 'Foto absen ' . $tipeAbsen . ' berhasil diupload',
                'data' => [
                    'absenno' => $validated['absenno'],
                    'tenagakerjaid' => $validated['tenagakerjaid'],
                    'tipeabsen' => $tipeAbsen,
                    'path' => $path,
                    'url' => $url,
                    'filename' => $filename,
                    'uploaded_at' => $now->toIso8601String()
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'description' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($path) && Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
            
            \Log::error('Error uploading foto absen ' . $tipeAbsen, [
                'error' => $e->getMessage(),
                'request' => $request->except('foto')
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}