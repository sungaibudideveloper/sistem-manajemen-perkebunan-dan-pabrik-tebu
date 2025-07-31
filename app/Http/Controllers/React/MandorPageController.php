<?php

namespace App\Http\Controllers\React;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MandorPageController extends Controller
{
    /**
     * Main SPA entry point
     */
    public function index(Request $request)
    {
        return Inertia::render('index', [
            'title' => 'Mandor Dashboard',
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'csrf_token' => csrf_token(),
            'routes' => [
                'logout' => route('logout'),
                'home' => route('home'),
                'mandor_index' => route('mandor.index'),
            ],
            'initialData' => [
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
                'attendance_stats' => [
                    'today_total' => 45,
                    'present' => 42,
                    'late' => 2,
                    'absent' => 1,
                    'percentage_present' => 93.3
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
            ]
        ]);
    }

    // API endpoints for real-time data updates
    public function checkIn(Request $request)
    {
        // Implement check-in logic here
        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil',
            'timestamp' => now()->format('H:i')
        ]);
    }

    public function checkOut(Request $request)
    {
        // Implement check-out logic here
        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil',
            'timestamp' => now()->format('H:i')
        ]);
    }

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

    public function getFieldActivities()
    {
        // Get field activities data
        return response()->json([
            'field_activities' => [
                // Real data from database
            ]
        ]);
    }
}