<?php

namespace App\Http\Controllers\Api\FileUpload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LkhFotoLampiranController extends Controller
{
    /**
     * Upload foto lampiran LKH
     * POST /api/fileupload/lkh-foto-lampiran
     */
    public function upload(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'foto' => 'required|image|mimes:jpeg,jpg,png|max:5120',
                'lkhno' => 'required|string|max:15',
                'companycode' => 'required|string|max:4',
                'blok' => 'nullable|string|max:3',
                'plot' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'accuracy' => 'nullable|numeric'
            ]);
            
            $file = $request->file('foto');
            $now = now();
            
            // Get lkhhdrid from lkhhdr
            $lkhhdr = DB::table('lkhhdr')
                ->where('lkhno', $validated['lkhno'])
                ->where('companycode', $validated['companycode'])
                ->first();
            
            if (!$lkhhdr) {
                throw new \Exception('LKH tidak ditemukan');
            }
            
            $lkhhdrid = $lkhhdr->id;
            
            // Generate S3 path
            $year = $now->format('Y');
            $month = $now->format('m');
            $day = $now->format('d');
            
            $blokPlot = '';
            if (!empty($validated['blok'])) {
                $blokPlot = '_' . $validated['blok'];
                if (!empty($validated['plot'])) {
                    $blokPlot .= '_' . $validated['plot'];
                }
            }
            
            $filename = sprintf(
                '%s%s_%s_%s.%s',
                $validated['lkhno'],
                $blokPlot,
                $now->format('YmdHis'),
                Str::random(6),
                $file->getClientOriginalExtension()
            );
            
            $path = sprintf(
                'lkh-lampiran/%s/%s/%s/%s/%s',
                $validated['companycode'],
                $year, $month, $day,
                $filename
            );
            
            // Upload to S3
            Storage::disk('s3')->put($path, file_get_contents($file));
            $url = Storage::disk('s3')->url($path);
            
            // Insert to database
            $photoId = DB::table('lkhfotolampiran')->insertGetId([
                'lkhno' => $validated['lkhno'],
                'lkhhdrid' => $lkhhdrid,
                'companycode' => $validated['companycode'],
                'blok' => $validated['blok'] ?? null,
                'plot' => $validated['plot'] ?? null,
                'photopath' => $path,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'accuracy' => $validated['accuracy'] ?? null,
                'uploadfrom' => 'mobile',
                'createdat' => $now,
                'updatedat' => $now
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => 1,
                'description' => 'Foto lampiran LKH berhasil diupload',
                'data' => [
                    'id' => $photoId,
                    'lkhno' => $validated['lkhno'],
                    'lkhhdrid' => $lkhhdrid,
                    'path' => $path,
                    'url' => $url,
                    'filename' => $filename,
                    'blok' => $validated['blok'] ?? null,
                    'plot' => $validated['plot'] ?? null,
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
            
            \Log::error('Error uploading LKH foto lampiran', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->except('foto')
            ]);
            
            return response()->json([
                'status' => 0,
                'description' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}