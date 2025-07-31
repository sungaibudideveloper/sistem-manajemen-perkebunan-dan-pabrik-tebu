import React, { useState } from 'react';
import { PageProps } from '@inertiajs/core';
import { router } from '@inertiajs/react';
import {
  FiCamera, FiArrowRight, FiArrowLeft,
  FiUser, FiTrendingUp, FiActivity, FiDatabase
} from 'react-icons/fi';
import {
  HiOutlineCollection, HiOutlineFingerPrint
} from 'react-icons/hi';
import LayoutMandor from '../components/layout-mandor';

// TypeScript Interfaces
interface User {
  id: number;
  name: string;
  email: string;
}

interface AttendanceSummary {
  name: string;
  time: string;
  status: string;
  status_color: string;
  id: number;
  initials: string;
}

interface Stats {
  total_workers: number;
  productivity: string;
  active_areas: number;
  monitoring: string;
}

interface AttendanceStats {
  today_total: number;
  present: number;
  late: number;
  absent: number;
  percentage_present: number;
}

interface Routes {
  logout: string;
  home: string;
  mandor_dashboard: string;
  mandor_field_data: string;
}

interface DashboardProps extends PageProps {
  title: string;
  user: User;
  routes: Routes;
  stats: Stats;
  attendance_summary: AttendanceSummary[];
  attendance_stats: AttendanceStats;
}

// Main Dashboard Component (Tanpa Loading Animation)
const DashboardMandor: React.FC<DashboardProps> = ({ 
  user, 
  routes, 
  stats, 
  attendance_summary, 
  attendance_stats 
}) => {
  const [activeSection, setActiveSection] = useState('dashboard');

  // Dashboard Content
  const DashboardContent = () => {
    return (
      <div className="min-h-screen">
        {/* Hero Section */}
        <section className="relative overflow-hidden bg-gradient-to-b from-neutral-50 to-white px-6 py-16">
          <div className="absolute inset-0 opacity-5">
            <div className="absolute inset-0" 
              style={{
                backgroundImage: `radial-gradient(circle at 50% 50%, #000 1px, transparent 1px)`,
                backgroundSize: '50px 50px'
              }}
            />
          </div>

          <div className="relative max-w-7xl mx-auto">
            <div className="text-center mb-12">
              <h1 className="text-5xl md:text-7xl font-bold tracking-tighter mb-4">
                <span className="text-transparent bg-clip-text bg-gradient-to-r from-neutral-700 to-neutral-900">
                  SB Tebu
                </span>
                <br />
                <span className="text-neutral-400">Sungaibudi Tebu Apps</span>
              </h1>
              <p className="text-xl text-neutral-500 max-w-2xl mx-auto">
                Aplikasi koleksi data lapangan untuk kegiatan perkebunan tebu
              </p>
            </div>

            {/* Feature Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 pt-16 pb-16">
              {/* Attendance Card */}
              <div
                onClick={() => setActiveSection('absensi')}
                className="group cursor-pointer"
              >
                <div className="relative h-64 overflow-hidden rounded-2xl bg-gradient-to-br from-neutral-900 to-neutral-700 hover:-translate-y-2 transition-transform duration-300 border-2 border-neutral-300 hover:border-neutral-400 shadow-lg hover:shadow-xl">
                  <div className="absolute inset-0 bg-gradient-to-br from-transparent to-black/50" />
                  
                  {/* Pattern overlay */}
                  <div className="absolute inset-0 opacity-10">
                    <div className="absolute inset-0" 
                      style={{
                        backgroundImage: `linear-gradient(45deg, transparent 48%, white 49%, white 51%, transparent 52%)`,
                        backgroundSize: '20px 20px'
                      }}
                    />
                  </div>

                  <div className="relative h-full flex flex-col justify-between p-8">
                    <div>
                      <div className="inline-flex p-3 bg-white/10 backdrop-blur rounded-2xl mb-4">
                        <HiOutlineFingerPrint className="w-8 h-8 text-white" />
                      </div>
                      <h3 className="text-2xl font-bold text-white mb-2">
                        Sistem Absensi
                      </h3>
                      <p className="text-neutral-200">
                        Pencatatan kehadiran pekerja dengan biometrik
                      </p>
                    </div>
                    
                    <div className="flex items-center text-white/80 group-hover:text-white transition-colors">
                      <span className="text-sm font-medium">Masuk Sistem</span>
                      <FiArrowRight className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                    </div>
                  </div>
                </div>
              </div>

              {/* Field Data Card - will navigate to separate page */}
              <div
                onClick={() => router.visit(routes.mandor_field_data)}
                className="group cursor-pointer"
              >
                <div className="relative h-64 overflow-hidden rounded-2xl bg-gradient-to-br from-neutral-800 to-neutral-600 hover:-translate-y-2 transition-transform duration-300 border-2 border-neutral-300 hover:border-neutral-400 shadow-lg hover:shadow-xl">
                  <div className="absolute inset-0 bg-gradient-to-br from-transparent to-black/50" />
                  
                  {/* Dots pattern */}
                  <div className="absolute inset-0 opacity-10">
                    <div className="absolute inset-0"
                      style={{
                        backgroundImage: `radial-gradient(circle, white 1px, transparent 1px)`,
                        backgroundSize: '30px 30px'
                      }}
                    />
                  </div>

                  <div className="relative h-full flex flex-col justify-between p-8">
                    <div>
                      <div className="inline-flex p-3 bg-white/10 backdrop-blur rounded-2xl mb-4">
                        <HiOutlineCollection className="w-8 h-8 text-white" />
                      </div>
                      <h3 className="text-2xl font-bold text-white mb-2">
                        Koleksi Data Lapangan
                      </h3>
                      <p className="text-neutral-200">
                        Pengumpulan data real-time dan monitoring progres
                      </p>
                    </div>
                    
                    <div className="flex items-center text-white/80 group-hover:text-white transition-colors">
                      <span className="text-sm font-medium">Mulai Koleksi</span>
                      <FiArrowRight className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Stats Section */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 pt-32">
              {[
                { value: stats.total_workers.toString(), label: 'Pekerja', icon: FiUser },
                { value: stats.productivity, label: 'Produktivitas', icon: FiTrendingUp },
                { value: stats.active_areas.toString(), label: 'Area Aktif', icon: FiDatabase },
                { value: stats.monitoring, label: 'Monitoring', icon: FiActivity },
              ].map((stat, index) => (
                <div
                  key={index}
                  className="bg-white border border-neutral-200 rounded-2xl p-6 text-center hover:scale-105 transition-transform duration-300"
                >
                  <stat.icon className="w-6 h-6 text-neutral-400 mx-auto mb-3" />
                  <p className="text-3xl font-bold text-neutral-800">{stat.value}</p>
                  <p className="text-sm text-neutral-500 mt-1">{stat.label}</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      </div>
    );
  };

  // Attendance Content
  const AbsensiContent = () => (
    <div className="min-h-screen bg-gradient-to-b from-neutral-50 to-white">
      <div className="max-w-7xl mx-auto px-6 py-12">
        {/* Header dengan tombol back */}
        <div className="mb-12">
          <button
            onClick={() => setActiveSection('dashboard')}
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

  return (
    <LayoutMandor
      user={user}
      routes={routes}
      activeSection={activeSection}
      onSectionChange={setActiveSection}
    >
      {activeSection === 'dashboard' && <DashboardContent />}
      {activeSection === 'absensi' && <AbsensiContent />}
    </LayoutMandor>
  );
};

export default DashboardMandor;