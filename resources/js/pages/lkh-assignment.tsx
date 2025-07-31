// ===============================================
// FILE 1: resources/js/pages/lkh-assignment.tsx
// ===============================================

import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  FiArrowLeft, FiUsers, FiTruck, FiSave, FiCheck, FiLoader, 
  FiMapPin, FiCalendar, FiExternalLink, FiCheckCircle
} from 'react-icons/fi';

interface LKHData {
  lkhno: string;
  activitycode: string;
  activityname: string;
  blok: string;
  plot: string[];
  totalluasplan: number;
  jenistenagakerja: string;
  estimated_workers: number;
  rkhno: string;
  lkhdate: string;
  mandor_nama: string;
}

interface VehicleInfo {
  nokendaraan: string;
  jenis: string;
  hourmeter: number;
  operator_nama: string;
  operator_nik?: string;
}

interface WorkerAssignment {
  tenagakerjaid: string;
  nama: string;
  nik: string;
  assigned: boolean;
}

interface SharedProps {
  app: {
    name: string;
    url: string;
    logo_url: string;
  };
  [key: string]: any;
}

interface LKHAssignmentProps extends SharedProps {
  title: string;
  lkhData: LKHData;
  vehicleInfo?: VehicleInfo;
  availableWorkers: WorkerAssignment[];
  routes: {
    lkh_save_assignment: string;
    [key: string]: string;
  };
  csrf_token: string;
  flash?: {
    success?: string;
    error?: string;
  };
  success?: boolean;
}

const LKHAssignmentPage: React.FC<LKHAssignmentProps> = ({
  app,
  title,
  lkhData,
  vehicleInfo,
  availableWorkers,
  routes,
  csrf_token
}) => {
  const [assignedWorkers, setAssignedWorkers] = useState<WorkerAssignment[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isSaved, setIsSaved] = useState(false);

  const handleWorkerAssignment = (worker: WorkerAssignment) => {
    const isCurrentlyAssigned = assignedWorkers.some(w => w.tenagakerjaid === worker.tenagakerjaid);
    
    if (isCurrentlyAssigned) {
      setAssignedWorkers(prev => prev.filter(w => w.tenagakerjaid !== worker.tenagakerjaid));
    } else {
      setAssignedWorkers(prev => [...prev, { ...worker, assigned: true }]);
    }
    
    // Reset saved state when assignments change
    setIsSaved(false);
  };

  const saveAssignments = async () => {
    if (assignedWorkers.length === 0) {
      alert('Pilih minimal 1 pekerja untuk assignment');
      return;
    }

    setIsLoading(true);
    
    try {
      // Use Inertia router for POST request
      router.post(routes.lkh_save_assignment, {
        assigned_workers: assignedWorkers.map(w => ({
          tenagakerjaid: w.tenagakerjaid,
          nama: w.nama,
          nik: w.nik
        })),
        _token: csrf_token
      }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page: any) => {
          // Check if response contains success message
          if (page.props?.flash?.success || page.props?.success) {
            setIsSaved(true);
            alert('Assignment berhasil disimpan!');
          }
        },
        onError: (errors) => {
          console.error('Assignment errors:', errors);
          alert('Error menyimpan assignment: ' + (errors.message || 'Unknown error'));
        },
        onFinish: () => {
          setIsLoading(false);
        }
      });
      
    } catch (error) {
      console.error('Error saving assignments:', error);
      alert('Error menyimpan assignment');
      setIsLoading(false);
    }
  };

  const navigateToInput = () => {
    // Use Inertia router for navigation - construct route properly
    router.get(`/mandor/lkh/${lkhData.lkhno}/input`, {}, {
      preserveState: false,
      preserveScroll: false
    });
  };

  const goBack = () => {
    // Use Inertia router to go back
    router.get('/mandor', {}, {
      preserveState: false,
      preserveScroll: false
    });
  };

  return (
    <div className="min-h-screen bg-gradient-to-b from-neutral-50 to-white">
      <div className="max-w-7xl mx-auto px-6 py-8">
        {/* Header */}
        <div className="mb-8">
          <button
            onClick={goBack}
            className="flex items-center gap-2 text-neutral-600 hover:text-neutral-900 mb-4 transition-colors"
          >
            <FiArrowLeft className="w-4 h-4" />
            <span>Kembali</span>
          </button>
          
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <img src={app.logo_url} alt={`Logo ${app.name}`} className="w-10 h-10 object-contain" />
              <div>
                <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                  Assignment Pekerja
                </h2>
                <p className="text-lg text-neutral-600">{lkhData.lkhno} - {lkhData.activityname}</p>
              </div>
            </div>
            
            <div className="flex items-center gap-3">
              <button
                onClick={saveAssignments}
                disabled={isLoading || assignedWorkers.length === 0}
                className="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isLoading ? (
                  <>
                    <FiLoader className="w-4 h-4 animate-spin" />
                    <span>Menyimpan...</span>
                  </>
                ) : (
                  <>
                    <FiSave className="w-4 h-4" />
                    <span>Simpan Assignment</span>
                  </>
                )}
              </button>

              <button
                onClick={navigateToInput}
                disabled={!isSaved}
                className="flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <FiExternalLink className="w-4 h-4" />
                <span>Input Hasil</span>
              </button>
            </div>
          </div>
        </div>

        {/* LKH Info Card */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <FiMapPin className="w-5 h-5 text-blue-600" />
              Informasi LKH
            </h3>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div>
                <label className="text-sm font-medium text-neutral-500">Tanggal</label>
                <p className="text-lg font-semibold">{new Date(lkhData.lkhdate).toLocaleDateString('id-ID')}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-neutral-500">Blok & Plot</label>
                <p className="text-lg font-semibold">Blok {lkhData.blok} • {lkhData.plot.join(', ')}</p>
              </div>
              <div>
                <label className="text-sm font-medium text-neutral-500">Target Luas</label>
                <p className="text-lg font-semibold">{lkhData.totalluasplan} Ha</p>
              </div>
              <div>
                <label className="text-sm font-medium text-neutral-500">Jenis Tenaga Kerja</label>
                <p className="text-lg font-semibold">{lkhData.jenistenagakerja}</p>
              </div>
            </div>
          </div>
        </div>

        {/* Vehicle Info (if applicable) */}
        {vehicleInfo && (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <FiTruck className="w-5 h-5 text-orange-600" />
                Informasi Kendaraan
              </h3>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                  <label className="text-sm font-medium text-neutral-500">No. Kendaraan</label>
                  <p className="text-lg font-semibold">{vehicleInfo.nokendaraan}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-neutral-500">Jenis</label>
                  <p className="text-lg font-semibold">{vehicleInfo.jenis}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-neutral-500">Hour Meter</label>
                  <p className="text-lg font-semibold">{vehicleInfo.hourmeter} Jam</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-neutral-500">Operator</label>
                  <p className="text-lg font-semibold">{vehicleInfo.operator_nama}</p>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Worker Assignment */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <div className="flex items-center justify-between">
              <h3 className="font-semibold flex items-center gap-2">
                <FiUsers className="w-5 h-5 text-blue-600" />
                Pilih Pekerja ({assignedWorkers.length} terpilih)
              </h3>
              <span className="text-sm text-neutral-500">
                Estimasi: ~{lkhData.estimated_workers} pekerja
              </span>
            </div>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {availableWorkers.map((worker) => {
                const isAssigned = assignedWorkers.some(w => w.tenagakerjaid === worker.tenagakerjaid);
                return (
                  <div
                    key={worker.tenagakerjaid}
                    onClick={() => handleWorkerAssignment(worker)}
                    className={`p-4 border-2 rounded-xl cursor-pointer transition-all ${
                      isAssigned 
                        ? 'border-blue-500 bg-blue-50' 
                        : 'border-neutral-200 hover:border-blue-300'
                    }`}
                  >
                    <div className="flex items-center justify-between">
                      <div>
                        <h4 className="font-semibold">{worker.nama}</h4>
                        <p className="text-sm text-neutral-600">NIK: {worker.nik}</p>
                      </div>
                      <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center ${
                        isAssigned 
                          ? 'border-blue-500 bg-blue-500' 
                          : 'border-neutral-300'
                      }`}>
                        {isAssigned && <FiCheck className="w-3 h-3 text-white" />}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
            
            {availableWorkers.length === 0 && (
              <div className="text-center py-8">
                <FiUsers className="w-12 h-12 text-neutral-300 mx-auto mb-4" />
                <p className="text-neutral-500">Tidak ada pekerja yang tersedia</p>
                <p className="text-sm text-neutral-400 mt-1">Pastikan ada pekerja yang sudah absen hari ini</p>
              </div>
            )}
          </div>
        </div>

        {/* Assigned Workers Summary */}
        {assignedWorkers.length > 0 && (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <FiCheckCircle className="w-5 h-5 text-green-600" />
                Pekerja yang Ditugaskan ({assignedWorkers.length})
              </h3>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {assignedWorkers.map((worker) => (
                  <div
                    key={worker.tenagakerjaid}
                    className="p-3 bg-green-50 border border-green-200 rounded-lg"
                  >
                    <h4 className="font-semibold text-green-900">{worker.nama}</h4>
                    <p className="text-sm text-green-700">NIK: {worker.nik}</p>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default LKHAssignmentPage;