<?php

namespace App\Http\Controllers\React;
// app\Http\Controllers\React\MandorController.php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MandorController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('dashboard-mandor', [
            'title' => 'Mandor Dashboard',
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                // Add other user fields as needed
            ],
            'logoutUrl' => route('logout'),
            'appUrl' => config('app.url'),
            'routes' => [
                'logout' => route('logout'),
                'home' => route('home'),
                'mandor_dashboard' => route('mandor.dashboard'),
            ],
            'stats' => [
                'total_workers' => 156,
                'productivity' => '94%',
                'active_areas' => 12,
                'monitoring' => '24/7'
            ],
            'attendance_summary' => [
                [
                    'name' => 'Ahmad Rizki',
                    'time' => '07:30',
                    'status' => 'Tepat Waktu',
                    'status_color' => 'text-green-600',
                    'id' => 1001,
                    'initials' => 'AR'
                ],
                [
                    'name' => 'Budi Santoso',
                    'time' => '07:45',
                    'status' => 'Tepat Waktu',
                    'status_color' => 'text-green-600',
                    'id' => 1002,
                    'initials' => 'BS'
                ],
                [
                    'name' => 'Siti Nurhaliza',
                    'time' => '09:15',
                    'status' => 'Terlambat',
                    'status_color' => 'text-amber-600',
                    'id' => 1003,
                    'initials' => 'SN'
                ],
                [
                    'name' => 'Dedi Prasetyo',
                    'time' => '-',
                    'status' => 'Tidak Hadir',
                    'status_color' => 'text-red-600',
                    'id' => 1004,
                    'initials' => 'DP'
                ]
            ],
            'field_activities' => [
                [
                    'type' => 'Foto',
                    'location' => 'Blok A-12',
                    'time' => '2 jam lalu',
                    'status' => 'Selesai',
                    'icon' => 'camera'
                ],
                [
                    'type' => 'Laporan Keselamatan',
                    'location' => 'Area B',
                    'time' => '3 jam lalu',
                    'status' => 'Selesai',
                    'icon' => 'shield'
                ],
                [
                    'type' => 'Log Harian',
                    'location' => 'Blok C-7',
                    'time' => '5 jam lalu',
                    'status' => 'Dalam Review',
                    'icon' => 'file-text'
                ]
            ],
            'collection_stats' => [
                [
                    'title' => 'Dokumentasi Foto',
                    'desc' => 'Pelacakan progres visual',
                    'stats' => '127 foto hari ini',
                    'icon' => 'camera',
                    'gradient' => 'from-neutral-700 to-neutral-900'
                ],
                [
                    'title' => 'Laporan Harian',
                    'desc' => 'Update komprehensif lapangan',
                    'stats' => '23 laporan tersimpan',
                    'icon' => 'file-text',
                    'gradient' => 'from-neutral-600 to-neutral-800'
                ],
                [
                    'title' => 'Kepatuhan Keselamatan',
                    'desc' => 'Monitoring dan pemeriksaan HSE',
                    'stats' => '98% kepatuhan',
                    'icon' => 'shield',
                    'gradient' => 'from-neutral-500 to-neutral-700'
                ]
            ],
            'attendance_stats' => [
                'today_total' => 45,
                'present' => 42,
                'late' => 2,
                'absent' => 1,
                'percentage_present' => 93.3
            ]
        ]);
    }

    /**
     * Handle attendance check-in
     */
    public function checkIn(Request $request)
    {
        // Implement check-in logic here
        // This could include biometric verification, photo capture, etc.
        
        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil',
            'timestamp' => now()->format('H:i')
        ]);
    }

    /**
     * Handle attendance check-out
     */
    public function checkOut(Request $request)
    {
        // Implement check-out logic here
        
        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil',
            'timestamp' => now()->format('H:i')
        ]);
    }

    /**
     * Get real-time attendance data
     */
    public function getAttendanceData()
    {
        // This method can be called via AJAX to get updated attendance data
        return response()->json([
            'attendance_summary' => [
                // Real data from database
            ],
            'attendance_stats' => [
                // Real stats from database
            ]
        ]);
    }

    /**
     * Field Data page
     */
    public function fieldData()
    {
        return Inertia::render('field-data', [
            'title' => 'Koleksi Data Lapangan',
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'routes' => [
                'logout' => route('logout'),
                'home' => route('home'),
                'mandor_dashboard' => route('mandor.dashboard'),
            ],
            'field_activities' => [
                [
                    'type' => 'Foto',
                    'location' => 'Blok A-12',
                    'time' => '2 jam lalu',
                    'status' => 'Selesai',
                    'icon' => 'camera'
                ],
                [
                    'type' => 'Laporan Keselamatan',
                    'location' => 'Area B',
                    'time' => '3 jam lalu',
                    'status' => 'Selesai',
                    'icon' => 'shield'
                ],
                [
                    'type' => 'Log Harian',
                    'location' => 'Blok C-7',
                    'time' => '5 jam lalu',
                    'status' => 'Dalam Review',
                    'icon' => 'file-text'
                ]
            ],
            'collection_stats' => [
                [
                    'title' => 'Dokumentasi Foto',
                    'desc' => 'Pelacakan progres visual',
                    'stats' => '127 foto hari ini',
                    'icon' => 'camera',
                    'gradient' => 'from-neutral-700 to-neutral-900'
                ],
                [
                    'title' => 'Laporan Harian',
                    'desc' => 'Update komprehensif lapangan',
                    'stats' => '23 laporan tersimpan',
                    'icon' => 'file-text',
                    'gradient' => 'from-neutral-600 to-neutral-800'
                ],
                [
                    'title' => 'Kepatuhan Keselamatan',
                    'desc' => 'Monitoring dan pemeriksaan HSE',
                    'stats' => '98% kepatuhan',
                    'icon' => 'shield',
                    'gradient' => 'from-neutral-500 to-neutral-700'
                ]
            ]
        ]);
    }
}