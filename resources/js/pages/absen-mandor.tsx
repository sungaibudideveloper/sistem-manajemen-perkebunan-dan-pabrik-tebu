import React from 'react';
import {
  FiCamera, FiArrowLeft
} from 'react-icons/fi';

interface AttendanceSummary {
  name: string;
  time: string;
  status: string;
  status_color: string;
  id: number;
  initials: string;
}

interface AttendanceStats {
  today_total: number;
  present: number;
  late: number;
  absent: number;
  percentage_present: number;
}

interface AbsenMandorProps {
  attendance_summary: AttendanceSummary[];
  attendance_stats: AttendanceStats;
  onSectionChange: (section: string) => void;
}

const AbsenMandor: React.FC<AbsenMandorProps> = ({ 
  attendance_summary, 
  attendance_stats, 
  onSectionChange 
}) => {
  return (
    <div className="min-h-screen bg-gradient-to-b from-neutral-50 to-white">
      <div className="max-w-7xl mx-auto px-6 py-12">
        {/* Header dengan tombol back */}
        <div className="mb-12">
          <button
            onClick={() => onSectionChange('dashboard')}
            className="flex items-center gap-2 text-neutral-600 hover:text-neutral-900 mb-4 transition-colors"
          >
            <FiArrowLeft className="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </button>
          <h2 className="text-4xl font-bold tracking-tight text-neutral-900 mb-2">
            Sistem Absensi
          </h2>
          <p className="text-lg text-neutral-500">
            Pencatatan kehadiran pekerja dengan teknologi biometrik
          </p>
        </div>

        {/* Attendance Stats */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div className="bg-white border border-neutral-200 rounded-xl p-4 text-center">
            <p className="text-2xl font-bold text-neutral-800">{attendance_stats.today_total}</p>
            <p className="text-sm text-neutral-500">Total Hari Ini</p>
          </div>
          <div className="bg-white border border-green-200 rounded-xl p-4 text-center">
            <p className="text-2xl font-bold text-green-600">{attendance_stats.present}</p>
            <p className="text-sm text-neutral-500">Hadir</p>
          </div>
          <div className="bg-white border border-amber-200 rounded-xl p-4 text-center">
            <p className="text-2xl font-bold text-amber-600">{attendance_stats.late}</p>
            <p className="text-sm text-neutral-500">Terlambat</p>
          </div>
          <div className="bg-white border border-red-200 rounded-xl p-4 text-center">
            <p className="text-2xl font-bold text-red-600">{attendance_stats.absent}</p>
            <p className="text-sm text-neutral-500">Tidak Hadir</p>
          </div>
        </div>

        {/* Action Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
          <div className="h-48 rounded-2xl bg-black text-white cursor-pointer overflow-hidden relative group hover:scale-105 transition-transform duration-300">
            <div className="absolute inset-0 bg-gradient-to-br from-neutral-800 to-black" />
            <div className="absolute inset-0 opacity-20 group-hover:opacity-30 transition-opacity">
              <div className="h-full w-full" 
                style={{
                  backgroundImage: `repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.05) 35px, rgba(255,255,255,.05) 70px)`
                }}
              />
            </div>
            <div className="relative h-full flex flex-col justify-center items-center">
              <FiCamera className="w-16 h-16 mb-4 text-white" />
              <h3 className="text-2xl font-bold mb-2 text-white">Absen Masuk</h3>
              <p className="text-neutral-200">Mulai shift kerja</p>
            </div>
          </div>

          <div className="h-48 rounded-2xl bg-neutral-200 cursor-pointer overflow-hidden relative group hover:scale-105 transition-transform duration-300">
            <div className="absolute inset-0 bg-gradient-to-br from-neutral-100 to-neutral-300" />
            <div className="absolute inset-0 opacity-20 group-hover:opacity-30 transition-opacity">
              <div className="h-full w-full" 
                style={{
                  backgroundImage: `repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(0,0,0,.05) 35px, rgba(0,0,0,.05) 70px)`
                }}
              />
            </div>
            <div className="relative h-full flex flex-col justify-center items-center">
              <FiCamera className="w-16 h-16 mb-4 text-neutral-700" />
              <h3 className="text-2xl font-bold mb-2 text-neutral-800">Absen Keluar</h3>
              <p className="text-neutral-600">Akhiri shift kerja</p>
            </div>
          </div>
        </div>

        {/* Today's Overview */}
        <div className="bg-white rounded-2xl shadow-xl border border-neutral-200">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-6">
            <h3 className="text-xl font-medium">Ringkasan Hari Ini</h3>
          </div>
          <div className="p-0">
            <div className="divide-y">
              {attendance_summary.map((worker, index) => (
                <div
                  key={worker.id}
                  className="flex items-center justify-between p-4 hover:bg-neutral-50 transition-colors"
                >
                  <div className="flex items-center gap-4">
                    <div className="h-10 w-10 bg-neutral-200 rounded-full flex items-center justify-center text-neutral-700 text-sm font-medium">
                      {worker.initials}
                    </div>
                    <div>
                      <p className="font-medium text-neutral-900">{worker.name}</p>
                      <p className="text-sm text-neutral-500">ID: {worker.id}</p>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-medium text-neutral-700">{worker.time}</p>
                    <p className={`text-sm font-medium ${worker.status_color}`}>{worker.status}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AbsenMandor;