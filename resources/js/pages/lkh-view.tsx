// resources/js/pages/lkh-view.tsx - UPDATED: With Vehicle Summary Display

import React, { useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  ArrowLeft, Users, Edit3, MapPin, 
  CheckCircle, User, Package, AlertCircle, Truck, Clock
} from 'lucide-react';

interface LKHData {
  lkhno: string;
  activitycode: string;
  activityname: string;
  blok: string;
  plot: string[];
  totalluasplan: number;
  jenistenagakerja: string;
  rkhno: string;
  lkhdate: string;
  mandor_nama: string;
  mobile_status: string;
  needs_material?: boolean;
  keterangan?: string;
  totalhasil?: number;
  totalsisa?: number;
  is_completed?: boolean;
}

interface AssignedWorker {
  tenagakerjaid: string;
  nama: string;
  nik: string;
  jammasuk?: string;
  jamselesai?: string;
  totaljamkerja?: number;
  overtimehours?: number;
}

interface PlotData {
  blok: string;
  plot: string;
  luasarea: number;
  luashasil: number;
  luassisa: number;
}

interface MaterialInfo {
  itemcode: string;
  itemname: string;
  qty: number;
  unit: string;
  total_calculated?: number;
}

// NEW: Vehicle interface for view display
interface VehicleBBMData {
  nokendaraan: string;
  jenis: string;
  operator_nama: string;
  plots: string[];
  jammulai: string;
  jamselesai: string;
  work_duration: number;
  solar?: number;
  hourmeterstart?: number;
  hourmeterend?: number;
  is_completed: boolean;
}

// UPDATED: Support both single and multiple vehicles for view
interface SingleVehicle {
  nokendaraan: string;
  jenis: string;
  hourmeter: number;
  operator_nama: string;
  operator_nik?: string;
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
    plots: string[];
    total_luasarea: number;
  }>;
}

type VehicleInfo = SingleVehicle | MultipleVehicles | null;

interface LKHViewProps {
  title: string;
  mode: 'view' | 'edit' | 'view-readonly';
  readonly?: boolean;
  completed?: boolean;
  lkhData: LKHData;
  assignedWorkers: AssignedWorker[];
  plotData: PlotData[];
  materials?: MaterialInfo[];
  vehicleInfo?: VehicleInfo; // NEW: Vehicle info for display
  vehicleBBMData?: VehicleBBMData[]; // NEW: Actual BBM data from kendaraanbbm table
  routes: {
    lkh_save_results: string;
    lkh_assign: string;
    lkh_view: string;
    lkh_edit: string;
    mandor_index: string;
  };
  csrf_token: string;
  flash?: {
    success?: string;
    error?: string;
  };
  app: {
    name: string;
    url: string;
    logo_url: string;
  };
}

const LKHViewPage: React.FC<LKHViewProps> = ({
  app,
  mode,
  readonly = false,
  completed = false,
  lkhData,
  assignedWorkers,
  plotData = [],
  materials = [],
  vehicleInfo,
  vehicleBBMData = [], // NEW
  routes,
  flash
}) => {
  // Handle flash messages
  useEffect(() => {
    if (flash?.success) {
      alert(flash.success);
    }
    if (flash?.error) {
      alert('Error: ' + flash.error);
    }
  }, [flash]);

  const calculateTotals = () => {
    return plotData.reduce((totals, plot) => ({
      totalLuasPlan: totals.totalLuasPlan + plot.luasarea,
      totalHasil: totals.totalHasil + plot.luashasil,
      totalSisa: totals.totalSisa + plot.luassisa,
    }), {
      totalLuasPlan: 0,
      totalHasil: 0,
      totalSisa: 0,
    });
  };

  // Simple navigation using Laravel-generated URLs
  const goBack = () => {
    console.log('Navigating back to:', routes.mandor_index);
    router.get(routes.mandor_index);
  };

  const goToEdit = () => {
    console.log('Navigating to edit:', routes.lkh_edit);
    router.get(routes.lkh_edit);
  };

  // NEW: Render vehicle summary
  const renderVehicleSummary = () => {
    // If we have actual BBM data, show that
    if (vehicleBBMData.length > 0) {
      return (
        <div className="space-y-4">
          {vehicleBBMData.map((vehicle) => (
            <div key={vehicle.nokendaraan} className="p-4 border border-neutral-200 rounded-xl bg-orange-50">
              <div className="flex items-center gap-3 mb-3">
                <Truck className="w-5 h-5 text-orange-600" />
                <div>
                  <h4 className="font-semibold text-orange-900">{vehicle.nokendaraan}</h4>
                  <p className="text-xs text-neutral-500">{vehicle.jenis} - {vehicle.operator_nama}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                  <span className="text-neutral-500">Waktu Kerja:</span>
                  <p className="font-medium">
                    {vehicle.jammulai.substring(0, 5)} - {vehicle.jamselesai.substring(0, 5)}
                  </p>
                </div>
                <div>
                  <span className="text-neutral-500">Durasi:</span>
                  <p className="font-medium">{Math.floor(vehicle.work_duration)} jam</p>
                </div>
                <div>
                  <span className="text-neutral-500">Plot:</span>
                  <p className="font-medium">{vehicle.plots.join(', ')}</p>
                </div>
                {vehicle.is_completed && (
                  <div>
                    <span className="text-neutral-500">Solar:</span>
                    <p className="font-medium text-green-600">
                      {vehicle.solar ? `${vehicle.solar} L` : 'Belum diinput'}
                    </p>
                  </div>
                )}
              </div>
              
              {/* Hour Meter Info - Only show if completed */}
              {vehicle.is_completed && vehicle.hourmeterstart && vehicle.hourmeterend && (
                <div className="mt-3 pt-3 border-t border-orange-200">
                  <div className="grid grid-cols-3 gap-3 text-sm">
                    <div>
                      <span className="text-neutral-500">HM Awal:</span>
                      <p className="font-medium">{vehicle.hourmeterstart}</p>
                    </div>
                    <div>
                      <span className="text-neutral-500">HM Akhir:</span>
                      <p className="font-medium">{vehicle.hourmeterend}</p>
                    </div>
                    <div>
                      <span className="text-neutral-500">Selisih HM:</span>
                      <p className="font-medium">{(vehicle.hourmeterend - vehicle.hourmeterstart).toFixed(2)}</p>
                    </div>
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      );
    }

    // Fallback: Show vehicle info if available but no BBM data yet
    if (vehicleInfo) {
      if (vehicleInfo.is_multiple) {
        return (
          <div className="space-y-4">
            {vehicleInfo.vehicles.map((vehicle) => (
              <div key={vehicle.nokendaraan} className="p-4 border border-neutral-200 rounded-xl bg-neutral-50">
                <div className="flex items-center gap-3 mb-3">
                  <Truck className="w-5 h-5 text-orange-600" />
                  <div>
                    <h4 className="font-semibold text-orange-900">{vehicle.nokendaraan}</h4>
                    <p className="text-xs text-neutral-500">{vehicle.jenis} - {vehicle.operator_nama}</p>
                  </div>
                </div>
                
                <div className="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <span className="text-neutral-500">Plot:</span>
                    <p className="font-medium">{vehicle.plots.join(', ')}</p>
                  </div>
                  <div>
                    <span className="text-neutral-500">Status:</span>
                    <p className="text-orange-600 font-medium">Belum input waktu kerja</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        );
      } else {
        return (
          <div className="p-4 border border-neutral-200 rounded-xl bg-neutral-50">
            <div className="flex items-center gap-3 mb-3">
              <Truck className="w-5 h-5 text-orange-600" />
              <div>
                <h4 className="font-semibold text-orange-900">{vehicleInfo.nokendaraan}</h4>
                <p className="text-xs text-neutral-500">{vehicleInfo.jenis} - {vehicleInfo.operator_nama}</p>
              </div>
            </div>
            
            <div className="grid grid-cols-2 gap-3 text-sm">
              <div>
                <span className="text-neutral-500">Plot:</span>
                <p className="font-medium">{vehicleInfo.plots.join(', ')}</p>
              </div>
              <div>
                <span className="text-neutral-500">Status:</span>
                <p className="text-orange-600 font-medium">Belum input waktu kerja</p>
              </div>
            </div>
          </div>
        );
      }
    }

    // No vehicle info at all
    return (
      <div className="text-center py-8">
        <Truck className="w-12 h-12 text-neutral-300 mx-auto mb-4" />
        <p className="text-neutral-500">Tidak ada kendaraan untuk aktivitas ini</p>
      </div>
    );
  };

  const totals = calculateTotals();
  
  // Determine if this is a completed/readonly view
  const isReadonly = readonly || completed || lkhData.mobile_status === 'COMPLETED';
  const canEdit = !isReadonly && lkhData.mobile_status === 'DRAFT';

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
            <span>Kembali ke Beranda</span>
          </button>
          
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              {/* Logo with better error handling and path */}
              {app?.logo_url ? (
                <img 
                  src={app.logo_url} 
                  alt={`Logo ${app?.name || 'App'}`} 
                  className="w-10 h-10 object-contain"
                  onError={(e) => {
                    console.log('Logo failed to load, using fallback');
                    const target = e.target as HTMLImageElement;
                    target.style.display = 'none';
                    // Show fallback
                    const fallback = target.nextElementSibling as HTMLElement;
                    if (fallback) fallback.style.display = 'flex';
                  }}
                />
              ) : null}
              {/* Fallback logo - always present but hidden unless needed */}
              <div 
                className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center" 
                style={{ display: app?.logo_url ? 'none' : 'flex' }}
              >
                <span className="text-white font-bold text-lg">{app?.name?.charAt(0) || 'T'}</span>
              </div>
              
              <div>
                <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                  {isReadonly ? 'Hasil Pekerjaan (Selesai)' : 'Hasil Pekerjaan'}
                </h2>
                <p className="text-lg text-neutral-600">{lkhData.lkhno} - {lkhData.activitycode} - {lkhData.activityname}</p>
              </div>
            </div>

            {/* Edit Button - Only show for DRAFT status and not readonly */}
            {canEdit && (
              <button
                onClick={goToEdit}
                className="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
              >
                <Edit3 className="w-5 h-5" />
                <span>Edit Data</span>
              </button>
            )}
          </div>
        </div>

        {/* Status Badge */}
        <div className="bg-white rounded-xl shadow-sm border border-neutral-200 mb-6">
          <div className="p-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-6 flex-wrap">
                <div>
                  <span className="text-sm text-neutral-500">Status:</span>
                  <span className={`ml-2 px-3 py-1 rounded-full text-sm font-medium ${
                    lkhData.mobile_status === 'DRAFT' 
                      ? 'bg-yellow-100 text-yellow-700' 
                      : lkhData.mobile_status === 'COMPLETED'
                      ? 'bg-green-100 text-green-700'
                      : 'bg-gray-100 text-gray-700'
                  }`}>
                    {lkhData.mobile_status === 'DRAFT' ? 'Draft (Belum Submit)' : 
                     lkhData.mobile_status === 'COMPLETED' ? 'Selesai & Submitted' : 
                     'Status Unknown'}
                  </span>
                </div>
                <div>
                  <span className="text-sm text-neutral-500">Tanggal:</span>
                  <span className="ml-2 font-medium">{new Date(lkhData.lkhdate).toLocaleDateString('id-ID')}</span>
                </div>
                <div>
                  <span className="text-sm text-neutral-500">Plot:</span>
                  <span className="ml-2 font-medium">{Array.isArray(lkhData.plot) ? lkhData.plot.join(', ') : lkhData.plot}</span>
                </div>
                <div>
                  <span className="text-sm text-neutral-500">Tim:</span>
                  <span className="ml-2 font-medium">{assignedWorkers.length} pekerja</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Completion Notice for COMPLETED status */}
        {lkhData.mobile_status === 'COMPLETED' && (
          <div className="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
            <div className="flex items-center gap-3">
              <CheckCircle className="w-6 h-6 text-green-600" />
              <div>
                <h4 className="font-semibold text-green-900">Data Sudah Diselesaikan</h4>
                <p className="text-sm text-green-700 mt-1">
                  LKH ini sudah disubmit ke sistem.
                </p>
              </div>
            </div>
          </div>
        )}

        {/* NEW: Vehicle Summary Section */}
        {(vehicleBBMData.length > 0 || vehicleInfo) && (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <Truck className="w-5 h-5 text-orange-600" />
                Kendaraan & Waktu Kerja
                {vehicleBBMData.length > 0 && (
                  <span className="ml-2 text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full">
                    {vehicleBBMData.length} unit
                  </span>
                )}
              </h3>
            </div>
            
            <div className="p-6">
              {renderVehicleSummary()}
            </div>
          </div>
        )}

        {/* Worker Summary */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <Users className="w-5 h-5 text-purple-600" />
              Tim Pekerja ({assignedWorkers.length} orang)
            </h3>
          </div>
          
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {assignedWorkers.map((worker) => (
                <div key={worker.tenagakerjaid} className="p-4 border border-neutral-200 rounded-xl bg-neutral-50">
                  <div className="flex items-center gap-3 mb-3">
                    <User className="w-5 h-5 text-purple-600" />
                    <div>
                      <h4 className="font-semibold text-purple-900">{worker.nama}</h4>
                      <p className="text-xs text-neutral-500">{worker.nik}</p>
                    </div>
                  </div>
                  
                  <div className="grid grid-cols-2 gap-3 text-sm">
                    <div>
                      <span className="text-neutral-500">Jam Kerja:</span>
                      <p className="font-medium">
                        {worker.jammasuk?.substring(0, 5) || '07:00'} - {worker.jamselesai?.substring(0, 5) || '15:00'}
                      </p>
                    </div>
                    <div>
                      <span className="text-neutral-500">Total:</span>
                      <p className="font-medium">{Math.floor(worker.totaljamkerja || 8)} jam</p>
                    </div>
                    {(worker.overtimehours || 0) > 0 && (
                      <div className="col-span-2">
                        <span className="text-neutral-500">Lembur:</span>
                        <p className="font-medium text-orange-600">{worker.overtimehours} jam</p>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Plot Results */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <MapPin className="w-5 h-5 text-blue-600" />
              Hasil per Plot
            </h3>
          </div>
          
          <div className="p-6">
            <div className="space-y-4">
              {plotData.map((plot) => (
                <div key={plot.plot} className="p-4 border border-neutral-200 rounded-xl bg-neutral-50">
                  <h4 className="font-semibold text-lg mb-4 text-blue-900">
                    Plot {plot.plot} - Blok {plot.blok}
                  </h4>
                  
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="text-center p-3 bg-white rounded-lg">
                      <p className="text-sm text-neutral-500 mb-1">Luas Rencana</p>
                      <p className="text-xl font-bold text-neutral-900">{plot.luasarea.toFixed(2)} Ha</p>
                    </div>
                    
                    <div className="text-center p-3 bg-green-50 rounded-lg">
                      <p className="text-sm text-green-600 mb-1">Hasil Selesai</p>
                      <p className="text-xl font-bold text-green-900">{plot.luashasil.toFixed(2)} Ha</p>
                    </div>
                    
                    <div className={`text-center p-3 rounded-lg ${
                      plot.luassisa > 0 
                        ? 'bg-yellow-50' 
                        : 'bg-green-50'
                    }`}>
                      <p className={`text-sm mb-1 ${
                        plot.luassisa > 0 ? 'text-yellow-600' : 'text-green-600'
                      }`}>Sisa</p>
                      <p className={`text-xl font-bold ${
                        plot.luassisa > 0 ? 'text-yellow-900' : 'text-green-900'
                      }`}>{plot.luassisa.toFixed(2)} Ha</p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Material Summary - Read Only */}
        {materials.length > 0 ? (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <Package className="w-5 h-5 text-orange-600" />
                Material Digunakan
              </h3>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {materials.map((material) => (
                  <div key={material.itemcode} className="p-4 border border-neutral-200 rounded-xl bg-neutral-50">
                    <h4 className="font-semibold text-orange-900 mb-2">{material.itemname}</h4>
                    <p className="text-sm text-neutral-600 mb-2">Kode: {material.itemcode}</p>
                    <div className="flex justify-between items-center">
                      <div>
                        <p className="text-sm text-neutral-500">Qty Diterima</p>
                        <p className="text-lg font-bold text-orange-800">
                          {material.qty} {material.unit}
                        </p>
                      </div>
                      {material.total_calculated && (
                        <div className="text-right">
                          <p className="text-sm text-neutral-500">Rencana Pakai</p>
                          <p className="text-sm font-medium text-blue-600">
                            {material.total_calculated.toFixed(2)} {material.unit}
                          </p>
                        </div>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        ) : (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <Package className="w-5 h-5 text-neutral-600" />
                Material
              </h3>
            </div>
            <div className="p-6">
              <div className="flex items-center gap-3 text-neutral-600">
                <AlertCircle className="w-5 h-5" />
                <span>LKH ini tidak menggunakan material</span>
              </div>
            </div>
          </div>
        )}

        {/* Keterangan Section */}
        {lkhData.keterangan && (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold">Keterangan</h3>
            </div>
            <div className="p-6">
              <div className="bg-neutral-50 p-4 rounded-lg">
                <p className="text-neutral-700">{lkhData.keterangan}</p>
              </div>
            </div>
          </div>
        )}

        {/* Summary Totals */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <CheckCircle className="w-5 h-5 text-green-600" />
              Ringkasan Total
            </h3>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="text-center p-6 bg-blue-50 rounded-xl">
                <p className="text-sm text-blue-600 font-medium mb-2">Luas Rencana</p>
                <p className="text-3xl font-bold text-blue-900">{totals.totalLuasPlan.toFixed(2)} Ha</p>
              </div>
              <div className="text-center p-6 bg-green-50 rounded-xl">
                <p className="text-sm text-green-600 font-medium mb-2">Total Hasil</p>
                <p className="text-3xl font-bold text-green-900">{totals.totalHasil.toFixed(2)} Ha</p>
                <p className="text-sm text-green-600 mt-2">
                  {totals.totalLuasPlan > 0 ? Math.round((totals.totalHasil / totals.totalLuasPlan) * 100) : 0}% selesai
                </p>
              </div>
              <div className="text-center p-6 bg-yellow-50 rounded-xl">
                <p className="text-sm text-yellow-600 font-medium mb-2">Total Sisa</p>
                <p className="text-3xl font-bold text-yellow-900">{totals.totalSisa.toFixed(2)} Ha</p>
              </div>
            </div>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex justify-center gap-4">
          <button
            onClick={goBack}
            className="flex items-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium"
          >
            <ArrowLeft className="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </button>

          {canEdit && (
            <button
              onClick={goToEdit}
              className="flex items-center gap-2 px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium"
            >
              <Edit3 className="w-4 h-4" />
              <span>Edit Data</span>
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default LKHViewPage;