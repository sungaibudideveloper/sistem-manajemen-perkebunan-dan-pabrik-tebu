// resources\js\pages\data-collection-mandor.tsx

import React from 'react';
import {
  FiArrowLeft, FiCamera, FiFileText, FiShield, FiCheck, FiClock
} from 'react-icons/fi';

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

interface ExtendedRoutes {
  logout: string;
  home: string;
  mandor_index: string;
  // API routes untuk absensi
  workers: string;
  attendance_today: string;
  process_checkin: string;
  // Tambahkan routes untuk data collection bila diperlukan nanti
  // upload_photo?: string;
  // create_report?: string;
  // safety_check?: string;
}

interface DataCollectionMandorProps {
  field_activities: FieldActivity[];
  collection_stats: CollectionStat[];
  routes: ExtendedRoutes;
  csrf_token: string;
  onSectionChange: (section: string) => void;
}

const DataCollectionMandor: React.FC<DataCollectionMandorProps> = ({ 
  field_activities, 
  collection_stats,
  routes,
  csrf_token,
  onSectionChange 
}) => {
  const getIconComponent = (iconName: string) => {
    const icons: { [key: string]: React.ComponentType<any> } = {
      camera: FiCamera,
      'file-text': FiFileText,
      shield: FiShield,
    };
    return icons[iconName] || FiFileText;
  };

  const getStatusIcon = (status: string) => {
    if (status === 'Selesai') return <FiCheck className="w-4 h-4 text-green-600" />;
    return <FiClock className="w-4 h-4 text-amber-600" />;
  };

  const getStatusColor = (status: string) => {
    if (status === 'Selesai') return 'text-green-600 bg-green-50 border-green-200';
    return 'text-amber-600 bg-amber-50 border-amber-200';
  };

  // Handler untuk quick actions (siap untuk implementasi API calls)
  const handleTakePhoto = () => {
    // TODO: Implementasi ambil foto
    // Bisa menggunakan routes dan csrf_token untuk API calls
    console.log('Take photo clicked');
  };

  const handleCreateReport = () => {
    // TODO: Implementasi buat laporan
    // Bisa menggunakan routes dan csrf_token untuk API calls
    console.log('Create report clicked');
  };

  const handleSafetyCheck = () => {
    // TODO: Implementasi cek keselamatan
    // Bisa menggunakan routes dan csrf_token untuk API calls
    console.log('Safety check clicked');
  };

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
            Koleksi Data Lapangan
          </h2>
          <p className="text-lg text-neutral-500">
            Pengumpulan data real-time dan monitoring progres kegiatan
          </p>
        </div>

        {/* Collection Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
          {collection_stats.map((stat, index) => {
            const IconComponent = getIconComponent(stat.icon);
            return (
              <div
                key={index}
                className={`relative h-48 overflow-hidden rounded-2xl bg-gradient-to-br ${stat.gradient} text-white cursor-pointer hover:-translate-y-2 transition-transform duration-300 shadow-lg hover:shadow-xl`}
              >
                <div className="absolute inset-0 bg-gradient-to-br from-transparent to-black/30" />
                
                {/* Pattern overlay */}
                <div className="absolute inset-0 opacity-10">
                  <div className="absolute inset-0" 
                    style={{
                      backgroundImage: `radial-gradient(circle, white 1px, transparent 1px)`,
                      backgroundSize: '20px 20px'
                    }}
                  />
                </div>

                <div className="relative h-full flex flex-col justify-between p-6">
                  <div>
                    <div className="inline-flex p-3 bg-white/10 backdrop-blur rounded-xl mb-4">
                      <IconComponent className="w-6 h-6 text-white" />
                    </div>
                    <h3 className="text-xl font-bold text-white mb-2">
                      {stat.title}
                    </h3>
                    <p className="text-neutral-200 text-sm">
                      {stat.desc}
                    </p>
                  </div>
                  
                  <div className="text-white">
                    <p className="text-2xl font-bold">{stat.stats}</p>
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
          <div 
            onClick={handleTakePhoto}
            className="bg-white border border-neutral-200 rounded-2xl p-6 hover:shadow-lg transition-all cursor-pointer group"
          >
            <div className="text-center">
              <div className="inline-flex p-4 bg-neutral-100 rounded-2xl mb-4 group-hover:bg-neutral-200 transition-colors">
                <FiCamera className="w-8 h-8 text-neutral-600" />
              </div>
              <h3 className="text-lg font-bold text-neutral-900 mb-2">Ambil Foto</h3>
              <p className="text-sm text-neutral-500">Dokumentasi visual progres kegiatan</p>
            </div>
          </div>

          <div 
            onClick={handleCreateReport}
            className="bg-white border border-neutral-200 rounded-2xl p-6 hover:shadow-lg transition-all cursor-pointer group"
          >
            <div className="text-center">
              <div className="inline-flex p-4 bg-neutral-100 rounded-2xl mb-4 group-hover:bg-neutral-200 transition-colors">
                <FiFileText className="w-8 h-8 text-neutral-600" />
              </div>
              <h3 className="text-lg font-bold text-neutral-900 mb-2">Buat Laporan</h3>
              <p className="text-sm text-neutral-500">Input data lapangan harian</p>
            </div>
          </div>

          <div 
            onClick={handleSafetyCheck}
            className="bg-white border border-neutral-200 rounded-2xl p-6 hover:shadow-lg transition-all cursor-pointer group"
          >
            <div className="text-center">
              <div className="inline-flex p-4 bg-neutral-100 rounded-2xl mb-4 group-hover:bg-neutral-200 transition-colors">
                <FiShield className="w-8 h-8 text-neutral-600" />
              </div>
              <h3 className="text-lg font-bold text-neutral-900 mb-2">Cek Keselamatan</h3>
              <p className="text-sm text-neutral-500">Audit dan monitoring HSE</p>
            </div>
          </div>
        </div>

        {/* Recent Activities */}
        <div className="bg-white rounded-2xl shadow-xl border border-neutral-200">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-6">
            <h3 className="text-xl font-medium">Aktivitas Terbaru</h3>
          </div>
          <div className="p-0">
            <div className="divide-y">
              {field_activities.map((activity, index) => {
                const IconComponent = getIconComponent(activity.icon);
                return (
                  <div
                    key={index}
                    className="flex items-center justify-between p-6 hover:bg-neutral-50 transition-colors"
                  >
                    <div className="flex items-center gap-4">
                      <div className="h-12 w-12 bg-neutral-100 rounded-xl flex items-center justify-center">
                        <IconComponent className="w-6 h-6 text-neutral-600" />
                      </div>
                      <div>
                        <p className="font-medium text-neutral-900">{activity.type}</p>
                        <p className="text-sm text-neutral-500">{activity.location} • {activity.time}</p>
                      </div>
                    </div>
                    <div className="text-right">
                      <div className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-medium ${getStatusColor(activity.status)}`}>
                        {getStatusIcon(activity.status)}
                        <span>{activity.status}</span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DataCollectionMandor;