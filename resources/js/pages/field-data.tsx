import React, { useState } from 'react';
import { PageProps } from '@inertiajs/core';
import { router } from '@inertiajs/react';
import {
  FiCamera, FiFileText, FiShield, FiArrowRight, FiArrowLeft
} from 'react-icons/fi';
import LayoutMandor from '../components/layout-mandor';

// TypeScript Interfaces
interface User {
  id: number;
  name: string;
  email: string;
}

interface FieldActivity {
  type: string;
  location: string;
  time: string;
  status: string;
  icon: string;
}

interface CollectionStat {
  title: string;
  desc: string;
  stats: string;
  icon: string;
  gradient: string;
}

interface Routes {
  logout: string;
  home: string;
  mandor_dashboard: string;
  mandor_field_data: string;
}

interface FieldDataProps extends PageProps {
  title: string;
  user: User;
  routes: Routes;
  field_activities: FieldActivity[];
  collection_stats: CollectionStat[];
}

const FieldData: React.FC<FieldDataProps> = ({ 
  user, 
  routes, 
  field_activities, 
  collection_stats 
}) => {
  const [activeSection, setActiveSection] = useState('field-data');

  const handleBackToDashboard = () => {
    router.visit(routes.mandor_dashboard);
  };

  const FieldDataContent = () => (
    <div className="min-h-screen bg-gradient-to-b from-neutral-50 to-white">
      <div className="max-w-7xl mx-auto px-6 py-12">
        {/* Header dengan tombol back */}
        <div className="mb-12">
          <button
            onClick={handleBackToDashboard}
            className="flex items-center gap-2 text-neutral-600 hover:text-neutral-900 mb-4 transition-colors"
          >
            <FiArrowLeft className="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </button>
          <h2 className="text-4xl font-bold tracking-tight text-neutral-900 mb-2">
            Koleksi Data Lapangan
          </h2>
          <p className="text-lg text-neutral-500">
            Pengumpulan data real-time dan monitoring progres kegiatan
          </p>
        </div>

        {/* Collection Types */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
          {collection_stats.map((item, index) => (
            <div
              key={index}
              className="group cursor-pointer hover:-translate-y-2 transition-transform duration-300"
            >
              <div className={`h-64 rounded-2xl overflow-hidden relative bg-gradient-to-br ${item.gradient}`}>
                <div className="absolute inset-0 bg-black/20" />
                
                {/* Geometric pattern */}
                <div className="absolute inset-0 opacity-10">
                  <svg width="100%" height="100%">
                    <pattern id={`pattern-${index}`} x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                      <circle cx="20" cy="20" r="2" fill="white" />
                    </pattern>
                    <rect width="100%" height="100%" fill={`url(#pattern-${index})`} />
                  </svg>
                </div>

                <div className="relative h-full flex flex-col justify-between p-6 text-white">
                  <div>
                    <div className="inline-flex p-3 bg-white/10 backdrop-blur rounded-xl mb-4">
                      {item.icon === 'camera' && <FiCamera className="w-6 h-6" />}
                      {item.icon === 'file-text' && <FiFileText className="w-6 h-6" />}
                      {item.icon === 'shield' && <FiShield className="w-6 h-6" />}
                    </div>
                    <h3 className="text-xl font-bold mb-2 text-white">{item.title}</h3>
                    <p className="text-neutral-200 text-sm">{item.desc}</p>
                  </div>
                  
                  <div>
                    <p className="text-2xl font-bold text-white">{item.stats}</p>
                    <div className="flex items-center mt-2 text-neutral-200 group-hover:text-white transition-colors">
                      <span className="text-sm">Akses Modul</span>
                      <FiArrowRight className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
          {/* Photo Documentation */}
          <div className="bg-white rounded-2xl shadow-xl border border-neutral-200 p-8">
            <div className="flex items-center gap-4 mb-6">
              <div className="p-3 bg-neutral-100 rounded-xl">
                <FiCamera className="w-8 h-8 text-neutral-600" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-neutral-900">Dokumentasi Foto</h3>
                <p className="text-neutral-500">Ambil foto progres lapangan</p>
              </div>
            </div>
            
            <div className="space-y-3">
              <button className="w-full bg-black text-white py-3 px-6 rounded-xl hover:bg-neutral-800 transition-colors">
                Buka Kamera
              </button>
              <button className="w-full border border-neutral-300 text-neutral-700 py-3 px-6 rounded-xl hover:bg-neutral-50 transition-colors">
                Lihat Galeri
              </button>
            </div>
          </div>

          {/* Report Forms */}
          <div className="bg-white rounded-2xl shadow-xl border border-neutral-200 p-8">
            <div className="flex items-center gap-4 mb-6">
              <div className="p-3 bg-neutral-100 rounded-xl">
                <FiFileText className="w-8 h-8 text-neutral-600" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-neutral-900">Laporan Harian</h3>
                <p className="text-neutral-500">Buat laporan kegiatan</p>
              </div>
            </div>
            
            <div className="space-y-3">
              <button className="w-full bg-neutral-800 text-white py-3 px-6 rounded-xl hover:bg-neutral-700 transition-colors">
                Form Laporan Baru
              </button>
              <button className="w-full border border-neutral-300 text-neutral-700 py-3 px-6 rounded-xl hover:bg-neutral-50 transition-colors">
                Draft Tersimpan
              </button>
            </div>
          </div>
        </div>

        {/* Recent Activity */}
        <div className="bg-white rounded-2xl shadow-xl border border-neutral-200">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-6">
            <h3 className="text-xl font-medium">Aktivitas Terbaru</h3>
          </div>
          <div className="p-6">
            <div className="space-y-4">
              {field_activities.map((activity, index) => (
                <div
                  key={index}
                  className="flex items-center justify-between p-4 bg-neutral-50 rounded-xl hover:bg-neutral-100 transition-colors"
                >
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                      {activity.icon === 'camera' && <FiCamera className="w-5 h-5 text-neutral-600" />}
                      {activity.icon === 'shield' && <FiShield className="w-5 h-5 text-neutral-600" />}
                      {activity.icon === 'file-text' && <FiFileText className="w-5 h-5 text-neutral-600" />}
                    </div>
                    <div>
                      <p className="font-medium text-neutral-900">{activity.type}</p>
                      <p className="text-sm text-neutral-500">{activity.location}</p>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="text-sm text-neutral-500">{activity.time}</p>
                    <p className="text-sm font-medium text-neutral-700">{activity.status}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* GPS & Location Info */}
        <div className="mt-8 bg-gradient-to-r from-neutral-900 to-neutral-700 rounded-2xl p-8 text-white">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-xl font-bold mb-2">Status Lokasi</h3>
              <p className="text-neutral-200">GPS aktif • Akurasi: 3.2m</p>
              <p className="text-sm text-neutral-300 mt-1">
                Lat: -6.2088 • Lng: 106.8456
              </p>
            </div>
            <div className="text-right">
              <div className="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-2">
                <div className="w-3 h-3 bg-white rounded-full animate-pulse"></div>
              </div>
              <p className="text-sm text-neutral-300">Terhubung</p>
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
      <FieldDataContent />
    </LayoutMandor>
  );
};

export default FieldData;