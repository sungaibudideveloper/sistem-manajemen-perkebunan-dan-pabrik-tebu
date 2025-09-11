// resources/js/pages/lkh-assignment.tsx - UPDATED: Using Existing LayoutMandor

import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import LayoutMandor from '../components/layout-mandor';
import {
  ArrowLeft, Users, Truck, Save, Check, Loader, 
  MapPin, ExternalLink, CheckCircle, Info
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

interface SingleVehicle {
  nokendaraan: string;
  jenis: string;
  hourmeter: number;
  operator_nama: string;
  operator_nik?: string;
  helper_nama?: string;
  helper_id?: string;
  is_multiple: false;
  plots: string[];
}

interface MultipleVehicles {
  is_multiple: true;
  vehicle_count: number;
  vehicles: Array<{
    nokendaraan: string;
    jenis: string;
    hourmeter: number;
    operator_nama: string;
    operator_nik?: string;
    helper_nama?: string;
    helper_id?: string;
    plots: string[];
    total_luasarea: number;
  }>;
}

type VehicleInfo = SingleVehicle | MultipleVehicles | null;

interface WorkerAssignment {
  tenagakerjaid: string;
  nama: string;
  nik: string;
  assigned: boolean;
}

interface User {
  id: number;
  name: string;
  email: string;
  userid: string;
  companycode: string;
  company_name: string;
}

interface ExtendedRoutes {
  logout: string;
  home: string;
  mandor_index: string;
  workers: string;
  attendance_today: string;
  process_checkin: string;
  lkh_save_assignment: string;
  lkh_input: string;
  lkh_view: string;
  [key: string]: string;
}

interface LKHAssignmentProps {
  title: string;
  lkhData: LKHData;
  vehicleInfo?: VehicleInfo;
  availableWorkers: WorkerAssignment[];
  existingAssignments?: string[];
  routes: ExtendedRoutes;
  csrf_token: string;
  flash?: {
    success?: string;
    error?: string;
  };
  success?: boolean;
  app: {
    name: string;
    url: string;
    logo_url: string;
  };
  user: User;
}

const LKHAssignmentContent: React.FC<Omit<LKHAssignmentProps, 'user' | 'routes'> & { 
  routes: ExtendedRoutes;
  onNavigateToInput: () => void;
  onGoBack: () => void;
}> = ({
  title,
  lkhData,
  vehicleInfo,
  availableWorkers,
  existingAssignments = [],
  routes,
  csrf_token,
  flash,
  app,
  onNavigateToInput,
  onGoBack
}) => {
  const [assignedWorkers, setAssignedWorkers] = useState<WorkerAssignment[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isSaved, setIsSaved] = useState(false);

  // Handle flash messages
  useEffect(() => {
    if (flash?.success) {
      setIsSaved(true);
      // Create toast notification
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
      
      setTimeout(() => toast.classList.remove('translate-x-full'), 100);
      setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => document.body.removeChild(toast), 300);
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

  const unassignedWorkers = availableWorkers.filter(w => 
    !existingAssignments.includes(w.tenagakerjaid)
  );

  const renderVehicleInfo = () => {
    if (!vehicleInfo) {
      return (
        <div className="text-center py-8">
          <Truck className="w-12 h-12 text-neutral-300 mx-auto mb-4" />
          <p className="text-neutral-500">Tidak ada kendaraan untuk aktivitas ini</p>
        </div>
      );
    }

    if (!vehicleInfo.is_multiple) {
      return (
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
          {vehicleInfo.helper_nama && (
            <div>
              <label className="text-sm font-medium text-neutral-500">Helper</label>
              <p className="text-lg font-semibold">{vehicleInfo.helper_nama}</p>
            </div>
          )}
          <div className={vehicleInfo.helper_nama ? "" : "col-span-2"}>
            <label className="text-sm font-medium text-neutral-500">Plot</label>
            <p className="text-lg font-semibold">{vehicleInfo.plots.join(', ')}</p>
          </div>
        </div>
      );
    }

    return (
      <div className="space-y-4">
        <div className="flex items-center justify-between mb-4">
          <p className="text-sm text-neutral-600">
            {vehicleInfo.vehicle_count} kendaraan diperlukan untuk aktivitas ini
          </p>
        </div>
        
        {vehicleInfo.vehicles.map((vehicle, index) => (
          <div key={vehicle.nokendaraan} className="bg-neutral-50 rounded-lg p-4 border">
            <div className="flex items-center justify-between mb-3">
              <h4 className="font-medium text-neutral-900">
                Kendaraan {index + 1}: {vehicle.nokendaraan}
              </h4>
              <span className="text-sm text-neutral-500">
                {vehicle.total_luasarea.toFixed(2)} Ha
              </span>
            </div>
            
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span className="text-neutral-500">Jenis:</span>
                <span className="ml-2 font-medium">{vehicle.jenis}</span>
              </div>
              <div>
                <span className="text-neutral-500">Hour Meter:</span>
                <span className="ml-2 font-medium">{vehicle.hourmeter} Jam</span>
              </div>
              <div>
                <span className="text-neutral-500">Operator:</span>
                <span className="ml-2 font-medium">{vehicle.operator_nama}</span>
              </div>
              {vehicle.helper_nama && (
                <div>
                  <span className="text-neutral-500">Helper:</span>
                  <span className="ml-2 font-medium">{vehicle.helper_nama}</span>
                </div>
              )}
              <div className={vehicle.helper_nama ? "" : "col-span-2"}>
                <span className="text-neutral-500">Plot:</span>
                <span className="ml-2 font-medium">{vehicle.plots.join(', ')}</span>
              </div>
            </div>
          </div>
        ))}
      </div>
    );
  };

  return (
    <div className="max-w-7xl mx-auto p-6">
      {/* Header */}
      <div className="mb-8">
        <button
          onClick={onGoBack}
          className="flex items-center gap-2 text-neutral-600 hover:text-neutral-900 mb-4 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span>Kembali</span>
        </button>
        
        <div>
          <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
            Assignment Pekerja
          </h2>
          <p className="text-lg text-neutral-600">{lkhData.lkhno} - {lkhData.activityname}</p>
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
            
            <button
              onClick={onNavigateToInput}
              disabled={assignedWorkers.length === 0}
              className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-green-600"
            >
              <ExternalLink className="w-4 h-4" />
              <span>Input Hasil</span>
            </button>
          </div>
        </div>
      )}

      {/* Input Hasil Button when no existing assignments but workers assigned */}
      {existingAssignments.length === 0 && assignedWorkers.length > 0 && isSaved && (
        <div className="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2 text-green-700">
              <CheckCircle className="w-5 h-5" />
              <span className="font-medium">
                {assignedWorkers.length} pekerja telah disimpan untuk assignment
              </span>
            </div>
            
            <button
              onClick={onNavigateToInput}
              className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
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

        {/* Vehicle Info Card */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <Truck className="w-5 h-5 text-orange-600" />
              Informasi Kendaraan
              {vehicleInfo?.is_multiple && (
                <span className="ml-2 text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full">
                  {vehicleInfo.vehicle_count} unit
                </span>
              )}
            </h3>
          </div>
          <div className="p-6">
            {renderVehicleInfo()}
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

      {/* Assigned Workers Summary */}
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

      {/* Action Buttons */}
      <div className="flex items-center justify-center gap-4">
        <button
          onClick={saveAssignments}
          disabled={isLoading || assignedWorkers.length === 0}
          className="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
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
  );
};

const LKHAssignmentPage: React.FC<LKHAssignmentProps> = (props) => {
  const [activeSection, setActiveSection] = useState('data-collection');

  const handleNavigateToInput = () => {
    console.log('Navigating to input:', props.routes.lkh_input);
    router.get(props.routes.lkh_input);
  };

  const handleGoBack = () => {
    console.log('Navigating back to:', props.routes.mandor_index);
    router.get(props.routes.mandor_index);
  };

  return (
    <LayoutMandor
      user={props.user}
      routes={props.routes}
      csrf_token={props.csrf_token}
      activeSection={activeSection}
      onSectionChange={setActiveSection}
    >
      <LKHAssignmentContent
        {...props}
        onNavigateToInput={handleNavigateToInput}
        onGoBack={handleGoBack}
      />
    </LayoutMandor>
  );
};

export default LKHAssignmentPage;