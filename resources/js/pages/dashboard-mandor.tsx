import React from 'react';
import {
  FiUser, FiTrendingUp, FiActivity, FiDatabase, FiArrowRight
} from 'react-icons/fi';
import {
  HiOutlineCollection, HiOutlineFingerPrint
} from 'react-icons/hi';

interface Stats {
  total_workers: number;
  productivity: string;
  active_areas: number;
  monitoring: string;
}

interface DashboardMandorProps {
  stats: Stats;
  onSectionChange: (section: string) => void;
}

const DashboardMandor: React.FC<DashboardMandorProps> = ({ 
  stats, 
  onSectionChange 
}) => {
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
              <br />
              <span className="text-neutral-400">SB Tebu Apps</span>
            </h1>
            <p className="text-xl text-neutral-500 max-w-2xl mx-auto">
              Aplikasi Absen dan Koleksi data lapangan kegiatan perkebunan tebu
            </p>
          </div>

          {/* Feature Cards */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8 pt-16 pb-16">
            {/* Attendance Card */}
            <div
              onClick={() => onSectionChange('absensi')}
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
                    <h3 className="text-2xl font-bold text-neutral mb-2">
                      Sistem Absensi
                    </h3>
                    <p className="text-neutral-200">
                      Pencatatan kehadiran pekerja dengan foto
                    </p>
                  </div>
                  
                  <div className="flex items-center text-white/80 group-hover:text-white transition-colors">
                    <span className="text-sm font-medium">Masuk Sistem</span>
                    <FiArrowRight className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                  </div>
                </div>
              </div>
            </div>

            {/* Data Collection Card */}
            <div
              onClick={() => onSectionChange('data-collection')}
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
                    <h3 className="text-2xl font-bold text-neutral mb-2">
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

export default DashboardMandor;