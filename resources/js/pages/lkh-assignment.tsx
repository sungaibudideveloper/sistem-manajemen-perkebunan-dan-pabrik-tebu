// ===============================================
// FILE: resources/js/pages/lkh-assignment.tsx
// ===============================================

import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  ArrowLeft, Users, Truck, Save, Check, Loader, 
  MapPin, Calendar, ExternalLink, CheckCircle, Info
} from 'lucide-react';

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
  existingAssignments?: string[];
  routes: {
    lkh_save_assignment: string;
    lkh_input: string;
    mandor_index: string;
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
  existingAssignments = [],
  routes,
  csrf_token,
  flash
}) => {
  const [assignedWorkers, setAssignedWorkers] = useState<WorkerAssignment[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isSaved, setIsSaved] = useState(false);

  // Handle flash messages
  useEffect(() => {
    if (flash?.success) {
      setIsSaved(true);
      // Create toast notification instead of alert
      const toast = document.createElement('div');
      toast.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full';
      toast.innerHTML = `
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span>${flash.success}</span>
        </div>
      `;
      document.body.appendChild(toast);
      
      // Animate in
      setTimeout(() => {
        toast.classList.remove('translate-x-full');
      }, 100);
      
      // Remove after 3 seconds
      setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
          document.body.removeChild(toast);
        }, 300);
      }, 3000);
    }
    if (flash?.error) {
      alert('Error: ' + flash.error);
    }
  }, [flash]);

  // Set initial state from existing assignments
  useEffect(() => {
    if (existingAssignments.length > 0) {
      const assigned = availableWorkers.filter(w => 
        existingAssignments.includes(w.tenagakerjaid)
      );
      setAssignedWorkers(assigned);
      setIsSaved(true);
    }
  }, [existingAssignments, availableWorkers]);

  const handleWorkerAssignment = (worker: WorkerAssignment) => {
    const isCurrentlyAssigned = assignedWorkers.some(w => w.tenagakerjaid === worker.tenagakerjaid);
    
    if (isCurrentlyAssigned) {
      setAssignedWorkers(prev => prev.filter(w => w.tenagakerjaid !== worker.tenagakerjaid));
    } else {
      setAssignedWorkers(prev => [...prev, { ...worker, assigned: true }]);
    }
    
    setIsSaved(false);
  };

  const saveAssignments = async () => {
    if (assignedWorkers.length === 0) {
      alert('Pilih minimal 1 pekerja untuk assignment');
      return;
    }

    const confirmMessage = `Akan membuat ${assignedWorkers.length} worker assignments. Lanjutkan?`;
    
    if (!confirm(confirmMessage)) {
      return;
    }

    setIsLoading(true);
    
    try {
      router.post(routes.lkh_save_assignment, {
        assigned_workers: assignedWorkers.map(w => ({
          tenagakerjaid: w.tenagakerjaid,
          nama: w.nama,
          nik: w.nik
        })),
        _token: csrf_token
      }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: (page: any) => {
          if (page.props?.flash?.success) {
            setIsSaved(true);
          }
        },
        onError: (errors) => {
          const errorMessage = errors.message || 
                              Object.values(errors).flat().join(', ') || 
                              'Unknown error occurred';
          alert('Error: ' + errorMessage);
        },
        onFinish: () => {
          setIsLoading(false);
        }
      });
    } catch (error: any) {
      console.error('Error saving assignments:', error);
      alert('Network error: ' + (error instanceof Error ? error.message : 'Unknown error'));
      setIsLoading(false);
    }
  };

  const navigateToInput = () => {
    router.get(routes.lkh_input, {}, {
      preserveState: false,
      preserveScroll: false
    });
  };

  const goBack = () => {
    router.get(routes.mandor_index, {}, {
      preserveState: false,
      preserveScroll: false
    });
  };

  // Filter out workers that are already assigned in database
  const unassignedWorkers = availableWorkers.filter(w => 
    !existingAssignments.includes(w.tenagakerjaid)
  );

  return (
    <div className="min-h-screen bg-gradient-to-b from-neutral-50 to-white">
      <div className="max-w-7xl mx-auto px-6 py-8">
        {/* Header */}
        <div className="mb-8">
          <button
            onClick={goBack}
            className="flex items-center gap-2 text-neutral-600 hover:text-neutral-900 mb-4 transition-colors"
          >
            <ArrowLeft className="w-4 h-4" />
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
          </div>
        </div>

        {/* Status Info */}
        {existingAssignments.length > 0 && (
          <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2 text-blue-700">
                <Info className="w-5 h-5" />
                <span className="font-medium">
                  Assignment sudah ada: {existingAssignments.length} pekerja sudah ditugaskan untuk LKH ini
                </span>
              </div>
              
              {/* Input Hasil Button */}
              <button
                onClick={navigateToInput}
                disabled={assignedWorkers.length === 0}
                className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-green-600"
              >
                <ExternalLink className="w-4 h-4" />
                <span>Input Hasil</span>
              </button>
            </div>
          </div>
        )}

        {/* Input Hasil Button when no existing assignments */}
        {existingAssignments.length === 0 && assignedWorkers.length > 0 && (
          <div className="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2 text-green-700">
                <CheckCircle className="w-5 h-5" />
                <span className="font-medium">
                  {assignedWorkers.length} pekerja telah dipilih untuk assignment
                </span>
              </div>
              
              {/* Input Hasil Button */}
              <button
                onClick={navigateToInput}
                disabled={assignedWorkers.length === 0}
                className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-green-600"
              >
                <ExternalLink className="w-4 h-4" />
                <span>Input Hasil</span>
              </button>
            </div>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          {/* LKH Info Card */}
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <MapPin className="w-5 h-5 text-blue-600" />
                Informasi LKH
              </h3>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-2 gap-6">
                <div>
                  <label className="text-sm font-medium text-neutral-500">Tanggal</label>
                  <p className="text-lg font-semibold">{new Date(lkhData.lkhdate).toLocaleDateString('id-ID')}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-neutral-500">Activity</label>
                  <p className="text-lg font-semibold">{lkhData.activityname}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-neutral-500">Plot</label>
                  <p className="text-lg font-semibold">{Array.isArray(lkhData.plot) ? lkhData.plot.join(', ') : lkhData.plot}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-neutral-500">Target Luas</label>
                  <p className="text-lg font-semibold">{lkhData.totalluasplan} Ha</p>
                </div>
                <div className="col-span-2">
                  <label className="text-sm font-medium text-neutral-500">Jenis Tenaga Kerja</label>
                  <p className="text-lg font-semibold">{lkhData.jenistenagakerja}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Vehicle Info (if applicable) */}
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <Truck className="w-5 h-5 text-orange-600" />
                Informasi Kendaraan
              </h3>
            </div>
            <div className="p-6">
              {vehicleInfo ? (
                <div className="grid grid-cols-2 gap-6">
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
              ) : (
                <div className="text-center py-8">
                  <Truck className="w-12 h-12 text-neutral-300 mx-auto mb-4" />
                  <p className="text-neutral-500">Tidak ada kendaraan untuk aktivitas ini</p>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Worker Assignment */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <div className="flex items-center justify-between">
              <h3 className="font-semibold flex items-center gap-2">
                <Users className="w-5 h-5 text-blue-600" />
                Pilih Pekerja
              </h3>
              <div className="text-sm text-neutral-500">
                <span>Pekerja available: {unassignedWorkers.length}</span>
              </div>
            </div>
          </div>
          <div className="p-6">
            {unassignedWorkers.length > 0 ? (
              <div className="space-y-2">
                {unassignedWorkers.map((worker) => {
                  const isAssigned = assignedWorkers.some(w => w.tenagakerjaid === worker.tenagakerjaid);
                  return (
                    <div
                      key={worker.tenagakerjaid}
                      onClick={() => handleWorkerAssignment(worker)}
                      className={`flex items-center justify-between p-3 border rounded-lg cursor-pointer transition-all ${
                        isAssigned 
                          ? 'border-blue-500 bg-blue-50' 
                          : 'border-neutral-200 hover:border-blue-300'
                      }`}
                    >
                      <span className="font-medium text-neutral-900">{worker.nama}</span>
                      <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center ${
                        isAssigned 
                          ? 'border-blue-500 bg-blue-500' 
                          : 'border-neutral-300'
                      }`}>
                        {isAssigned && <Check className="w-3 h-3 text-white" />}
                      </div>
                    </div>
                  );
                })}
              </div>
            ) : (
              <div className="text-center py-8">
                <Users className="w-12 h-12 text-green-300 mx-auto mb-4" />
                <p className="text-green-600 font-medium">
                  {availableWorkers.length > 0 
                    ? `Semua ${availableWorkers.length} pekerja sudah ditugaskan`
                    : 'Tidak ada pekerja yang tersedia'
                  }
                </p>
                {availableWorkers.length === 0 && (
                  <p className="text-sm text-neutral-400 mt-1">Pastikan ada pekerja yang sudah absen hari ini</p>
                )}
              </div>
            )}
          </div>
        </div>

        {/* Assigned Workers Summary - Always Show */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <div className="flex items-center justify-between">
              <h3 className="font-semibold flex items-center gap-2">
                <CheckCircle className="w-5 h-5 text-green-600" />
                Pekerja yang Ditugaskan
              </h3>
              <span className="text-sm text-neutral-500">
                Total: {assignedWorkers.length} pekerja assigned
              </span>
            </div>
          </div>
          <div className="p-6">
            {assignedWorkers.length > 0 ? (
              <div className="space-y-2">
                {assignedWorkers.map((worker, index) => (
                  <div
                    key={worker.tenagakerjaid}
                    className="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg"
                  >
                    <span className="font-medium text-green-900">
                      {index + 1}. {worker.nama}
                    </span>
                    <span className="text-xs text-green-600">Assigned</span>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8">
                <CheckCircle className="w-12 h-12 text-neutral-300 mx-auto mb-4" />
                <p className="text-neutral-500">Belum ada pekerja yang ditugaskan</p>
              </div>
            )}
          </div>
        </div>

        {/* Action Buttons - Moved to bottom */}
        <div className="flex items-center justify-center gap-6">
          <button
            onClick={saveAssignments}
            disabled={isLoading || assignedWorkers.length === 0}
            className="flex items-center gap-2 px-8 py-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-lg font-medium"
          >
            {isLoading ? (
              <>
                <Loader className="w-5 h-5 animate-spin" />
                <span>Menyimpan...</span>
              </>
            ) : (
              <>
                <Save className="w-5 h-5" />
                <span>Simpan Assignment</span>
              </>
            )}
          </button>
        </div>

      </div>
    </div>
  );
};

export default LKHAssignmentPage;