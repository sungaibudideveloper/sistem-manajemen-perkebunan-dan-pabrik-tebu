// resources/js/pages/lkh-view.tsx - CLEAN VERSION with 3-digit decimals
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

interface MaterialPlotBreakdown {
  plot: string;
  planned_usage: number;
  qtysisa: number;
  qtydigunakan: number;
}

interface MaterialInfo {
  itemcode: string;
  itemname: string;
  unit: string;
  plot_breakdown: MaterialPlotBreakdown[];
  total_planned: number;
  total_sisa: number;
  total_digunakan: number;
}

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
  created_at?: string;
  admin_updated_at?: string;
}

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
  vehicleInfo?: VehicleInfo;
  vehicleBBMData?: VehicleBBMData[];
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
  vehicleBBMData = [],
  routes,
  flash
}) => {
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

  const goBack = () => {
    router.get(routes.mandor_index);
  };

  const goToEdit = () => {
    router.get(routes.lkh_edit);
  };

  // CLEAN: Vehicle summary
  const renderVehicleSummary = () => {
    if (vehicleBBMData.length > 0) {
      return (
        <div className="space-y-3">
          {vehicleBBMData.map((vehicle) => (
            <div key={`${vehicle.nokendaraan}-${vehicle.plots.join('')}`} className="p-3 border border-neutral-200 rounded-lg bg-orange-50">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Truck className="w-4 h-4 text-orange-600" />
                  <div>
                    <h4 className="font-semibold text-sm text-orange-900">{vehicle.nokendaraan}</h4>
                    <p className="text-xs text-neutral-600">{vehicle.jenis} - {vehicle.operator_nama}</p>
                  </div>
                </div>
                
                <div className="text-right text-xs">
                  <div className="font-medium text-orange-700">
                    {vehicle.jammulai.substring(0, 5)} - {vehicle.jamselesai.substring(0, 5)} ({Math.floor(vehicle.work_duration)}h)
                  </div>
                  <div className="text-neutral-600">Plot: {vehicle.plots.join(', ')}</div>
                  {vehicle.is_completed && vehicle.solar && (
                    <div className="text-green-600 font-medium">Solar: {vehicle.solar}L</div>
                  )}
                </div>
              </div>
              
              {vehicle.is_completed && vehicle.hourmeterstart && vehicle.hourmeterend && (
                <div className="mt-2 pt-2 border-t border-orange-200 text-xs text-neutral-600">
                  HM: {vehicle.hourmeterstart} → {vehicle.hourmeterend} (Δ{(vehicle.hourmeterend - vehicle.hourmeterstart).toFixed(1)})
                </div>
              )}
            </div>
          ))}
        </div>
      );
    }

    if (vehicleInfo) {
      const vehicles = vehicleInfo.is_multiple ? vehicleInfo.vehicles : [vehicleInfo];
      return (
        <div className="space-y-2">
          {vehicles.map((vehicle) => (
            <div key={vehicle.nokendaraan} className="p-3 border border-neutral-200 rounded-lg bg-neutral-50">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Truck className="w-4 h-4 text-orange-600" />
                  <span className="font-medium text-sm">{vehicle.nokendaraan} - {vehicle.jenis}</span>
                </div>
                <div className="text-xs text-neutral-600">
                  {vehicle.operator_nama} | Plot: {vehicle.plots.join(', ')}
                </div>
              </div>
            </div>
          ))}
        </div>
      );
    }

    return (
      <div className="text-center py-6 text-neutral-500">
        <Truck className="w-8 h-8 text-neutral-300 mx-auto mb-2" />
        <p className="text-sm">Tidak ada kendaraan</p>
      </div>
    );
  };

  // CLEAN: Material summary with 3-digit decimals
  const renderMaterialSummary = () => {
    if (materials.length === 0) {
      return (
        <div className="flex items-center gap-3 text-neutral-600 py-4">
          <AlertCircle className="w-5 h-5" />
          <span>LKH ini tidak menggunakan material</span>
        </div>
      );
    }

    return (
      <div className="space-y-4">
        {materials.map((material) => (
          <div key={material.itemcode} className="border border-orange-200 rounded-lg overflow-hidden">
            {/* Material header - 3 digits */}
            <div className="bg-orange-50 p-3">
              <div className="flex items-center justify-between">
                <div>
                  <h4 className="font-semibold text-orange-900">{material.itemname}</h4>
                  <p className="text-xs text-orange-700">{material.itemcode}</p>
                </div>
                <div className="text-right text-xs">
                  <div className="text-orange-700">
                    Total: <span className="font-semibold">{(material.total_planned || 0).toFixed(3)}</span> {material.unit}
                  </div>
                  <div className="text-green-600">
                    Terpakai: <span className="font-semibold">{(material.total_digunakan || 0).toFixed(3)}</span> | 
                    Sisa: <span className="font-semibold">{(material.total_sisa || 0).toFixed(3)}</span>
                  </div>
                </div>
              </div>
            </div>
            
            {/* Plot breakdown - 3 digits */}
            <div className="p-3">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                {(material.plot_breakdown || []).map((plotMaterial) => (
                  <div key={plotMaterial.plot} className="p-3 border border-neutral-200 rounded bg-neutral-50">
                    <div className="flex items-center justify-between mb-1">
                      <span className="font-medium text-sm">Plot {plotMaterial.plot}</span>
                      <span className="text-xs text-neutral-600">
                        {(plotMaterial.planned_usage || 0).toFixed(3)} → {(plotMaterial.qtydigunakan || 0).toFixed(3)}
                      </span>
                    </div>
                    
                    <div className="grid grid-cols-3 gap-1 text-xs">
                      <div className="text-center p-1 bg-blue-100 rounded">
                        <div className="text-blue-700 font-medium">{(plotMaterial.planned_usage || 0).toFixed(3)}</div>
                        <div className="text-blue-600">Rencana</div>
                      </div>
                      <div className="text-center p-1 bg-green-100 rounded">
                        <div className="text-green-700 font-medium">{(plotMaterial.qtydigunakan || 0).toFixed(3)}</div>
                        <div className="text-green-600">Terpakai</div>
                      </div>
                      <div className="text-center p-1 bg-yellow-100 rounded">
                        <div className="text-yellow-700 font-medium">{(plotMaterial.qtysisa || 0).toFixed(3)}</div>
                        <div className="text-yellow-600">Sisa</div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        ))}
      </div>
    );
  };

  const totals = calculateTotals();
  const isReadonly = readonly || completed || lkhData.mobile_status === 'COMPLETED';
  const canEdit = !isReadonly && lkhData.mobile_status === 'DRAFT';

  return (
    <div className="min-h-screen bg-gradient-to-b from-neutral-50 to-white">
      <div className="max-w-7xl mx-auto px-6 py-6">
        {/* Header */}
        <div className="mb-6">
          <button
            onClick={goBack}
            className="flex items-center gap-2 text-neutral-600 hover:text-neutral-900 mb-3 transition-colors"
          >
            <ArrowLeft className="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </button>
          
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              {app?.logo_url ? (
                <img 
                  src={app.logo_url} 
                  alt={`Logo ${app?.name || 'App'}`} 
                  className="w-8 h-8 object-contain"
                  onError={(e) => {
                    (e.target as HTMLImageElement).style.display = 'none';
                  }}
                />
              ) : (
                <div className="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                  <span className="text-white font-bold text-sm">{app?.name?.charAt(0) || 'T'}</span>
                </div>
              )}
              
              <div>
                <h2 className="text-2xl font-bold text-neutral-900">
                  {isReadonly ? 'Hasil Selesai' : 'Hasil Pekerjaan'}
                </h2>
                <p className="text-neutral-600">{lkhData.lkhno} - {lkhData.activitycode}</p>
                <p className="text-sm text-neutral-500">{lkhData.activityname}</p>
              </div>
            </div>

            {canEdit && (
              <button
                onClick={goToEdit}
                className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                <Edit3 className="w-4 h-4" />
                <span>Edit</span>
              </button>
            )}
          </div>
        </div>

        {/* Status & Info */}
        <div className="bg-white rounded-lg shadow-sm border border-neutral-200 mb-6">
          <div className="p-4">
            <div className="flex items-center justify-between flex-wrap gap-4">
              <div className="flex items-center gap-4">
                <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                  lkhData.mobile_status === 'DRAFT' 
                    ? 'bg-yellow-100 text-yellow-700' 
                    : lkhData.mobile_status === 'COMPLETED'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-gray-100 text-gray-700'
                }`}>
                  {lkhData.mobile_status === 'DRAFT' ? 'Draft' : 
                   lkhData.mobile_status === 'COMPLETED' ? 'Selesai' : 
                   'Unknown'}
                </span>
                <div className="text-sm text-neutral-600">
                  {new Date(lkhData.lkhdate).toLocaleDateString('id-ID')} • 
                  Plot: {Array.isArray(lkhData.plot) ? lkhData.plot.join(', ') : lkhData.plot} • 
                  Tim: {assignedWorkers.length} orang
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Completion Notice */}
        {lkhData.mobile_status === 'COMPLETED' && (
          <div className="bg-green-50 border border-green-200 rounded-lg p-3 mb-6">
            <div className="flex items-center gap-2">
              <CheckCircle className="w-5 h-5 text-green-600" />
              <span className="font-medium text-green-900">Data sudah disubmit ke sistem</span>
            </div>
          </div>
        )}

        {/* Vehicle Section */}
        {(vehicleBBMData.length > 0 || vehicleInfo) && (
          <div className="bg-white rounded-lg shadow-sm border border-neutral-200 mb-6">
            <div className="border-b bg-neutral-50 p-3">
              <h3 className="font-semibold flex items-center gap-2 text-sm">
                <Truck className="w-4 h-4 text-orange-600" />
                Kendaraan & Waktu Kerja
                {vehicleBBMData.length > 0 && (
                  <span className="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full">
                    {vehicleBBMData.length}
                  </span>
                )}
              </h3>
            </div>
            <div className="p-4">
              {renderVehicleSummary()}
            </div>
          </div>
        )}

        {/* Worker Summary */}
        <div className="bg-white rounded-lg shadow-sm border border-neutral-200 mb-6">
          <div className="border-b bg-neutral-50 p-3">
            <h3 className="font-semibold flex items-center gap-2 text-sm">
              <Users className="w-4 h-4 text-purple-600" />
              Tim Pekerja ({assignedWorkers.length})
            </h3>
          </div>
          
          <div className="p-4">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              {assignedWorkers.map((worker) => (
                <div key={worker.tenagakerjaid} className="p-3 border border-neutral-200 rounded-lg bg-neutral-50">
                  <div className="flex items-center gap-2 mb-2">
                    <User className="w-4 h-4 text-purple-600" />
                    <div>
                      <h4 className="font-semibold text-sm text-purple-900">{worker.nama}</h4>
                      <p className="text-xs text-neutral-500">{worker.nik}</p>
                    </div>
                  </div>
                  
                  <div className="text-xs text-neutral-600">
                    <div className="flex justify-between">
                      <span>Jam Kerja:</span>
                      <span className="font-medium">
                        {worker.jammasuk?.substring(0, 5) || '07:00'}-{worker.jamselesai?.substring(0, 5) || '15:00'}
                      </span>
                    </div>
                    <div className="flex justify-between">
                      <span>Total:</span>
                      <span className="font-medium">{Math.floor(worker.totaljamkerja || 8)}h</span>
                    </div>
                    {(worker.overtimehours || 0) > 0 && (
                      <div className="flex justify-between text-orange-600">
                        <span>Lembur:</span>
                        <span className="font-medium">{worker.overtimehours}h</span>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Plot Results */}
        <div className="bg-white rounded-lg shadow-sm border border-neutral-200 mb-6">
          <div className="border-b bg-neutral-50 p-3">
            <h3 className="font-semibold flex items-center gap-2 text-sm">
              <MapPin className="w-4 h-4 text-blue-600" />
              Hasil per Plot
            </h3>
          </div>
          
          <div className="p-4">
            <div className="space-y-3">
              {plotData.map((plot) => (
                <div key={plot.plot} className="p-3 border border-neutral-200 rounded-lg bg-neutral-50">
                  <h4 className="font-semibold text-sm mb-2 text-blue-900">
                    Plot {plot.plot} - Blok {plot.blok}
                  </h4>
                  
                  <div className="grid grid-cols-3 gap-3">
                    <div className="text-center p-2 bg-white rounded">
                      <p className="text-xs text-neutral-500 mb-1">Rencana</p>
                      <p className="font-bold text-neutral-900">{plot.luasarea.toFixed(2)} Ha</p>
                    </div>
                    
                    <div className="text-center p-2 bg-green-50 rounded">
                      <p className="text-xs text-green-600 mb-1">Selesai</p>
                      <p className="font-bold text-green-900">{plot.luashasil.toFixed(2)} Ha</p>
                    </div>
                    
                    <div className={`text-center p-2 rounded ${
                      plot.luassisa > 0 ? 'bg-yellow-50' : 'bg-green-50'
                    }`}>
                      <p className={`text-xs mb-1 ${
                        plot.luassisa > 0 ? 'text-yellow-600' : 'text-green-600'
                      }`}>Sisa</p>
                      <p className={`font-bold ${
                        plot.luassisa > 0 ? 'text-yellow-900' : 'text-green-900'
                      }`}>{plot.luassisa.toFixed(2)} Ha</p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Material Summary - CLEAN with 3-digit decimals */}
        <div className="bg-white rounded-lg shadow-sm border border-neutral-200 mb-6">
          <div className="border-b bg-neutral-50 p-3">
            <h3 className="font-semibold flex items-center gap-2 text-sm">
              <Package className="w-4 h-4 text-orange-600" />
              Material per Plot
              {materials.length > 0 && (
                <span className="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full">
                  {materials.length}
                </span>
              )}
            </h3>
          </div>
          <div className="p-4">
            {renderMaterialSummary()}
          </div>
        </div>

        {/* Keterangan */}
        {lkhData.keterangan && (
          <div className="bg-white rounded-lg shadow-sm border border-neutral-200 mb-6">
            <div className="border-b bg-neutral-50 p-3">
              <h3 className="font-semibold text-sm">Keterangan</h3>
            </div>
            <div className="p-4">
              <div className="bg-neutral-50 p-3 rounded text-sm text-neutral-700">
                {lkhData.keterangan}
              </div>
            </div>
          </div>
        )}

        {/* Summary Totals */}
        <div className="bg-white rounded-lg shadow-sm border border-neutral-200 mb-6">
          <div className="border-b bg-neutral-50 p-3">
            <h3 className="font-semibold flex items-center gap-2 text-sm">
              <CheckCircle className="w-4 h-4 text-green-600" />
              Ringkasan Total
            </h3>
          </div>
          <div className="p-4">
            <div className="grid grid-cols-3 gap-4">
              <div className="text-center p-3 bg-blue-50 rounded">
                <p className="text-xs text-blue-600 font-medium mb-1">Rencana</p>
                <p className="text-xl font-bold text-blue-900">{totals.totalLuasPlan.toFixed(2)}</p>
                <p className="text-xs text-blue-600">Ha</p>
              </div>
              <div className="text-center p-3 bg-green-50 rounded">
                <p className="text-xs text-green-600 font-medium mb-1">Hasil</p>
                <p className="text-xl font-bold text-green-900">{totals.totalHasil.toFixed(2)}</p>
                <p className="text-xs text-green-600">
                  {totals.totalLuasPlan > 0 ? Math.round((totals.totalHasil / totals.totalLuasPlan) * 100) : 0}% • Ha
                </p>
              </div>
              <div className="text-center p-3 bg-yellow-50 rounded">
                <p className="text-xs text-yellow-600 font-medium mb-1">Sisa</p>
                <p className="text-xl font-bold text-yellow-900">{totals.totalSisa.toFixed(2)}</p>
                <p className="text-xs text-yellow-600">Ha</p>
              </div>
            </div>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex justify-center gap-3">
          <button
            onClick={goBack}
            className="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
          >
            <ArrowLeft className="w-4 h-4" />
            <span>Beranda</span>
          </button>

          {canEdit && (
            <button
              onClick={goToEdit}
              className="flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
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