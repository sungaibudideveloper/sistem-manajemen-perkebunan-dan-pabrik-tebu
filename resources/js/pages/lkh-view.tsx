// resources/js/pages/lkh-view.tsx - READONLY VIEW MODE

import React, { useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  ArrowLeft, Users, Edit3, MapPin, 
  CheckCircle, User, Package, AlertCircle, Clock, Eye
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
}

interface SharedProps {
  app: {
    name: string;
    url: string;
    logo_url: string;
  };
  [key: string]: any;
}

interface LKHViewProps extends SharedProps {
  title: string;
  mode: 'view' | 'edit';
  lkhData: LKHData;
  assignedWorkers: AssignedWorker[];
  plotData: PlotData[];
  materials?: MaterialInfo[];
  routes: {
    lkh_save_results: string;
    lkh_assign: string;
    lkh_view: string;
    lkh_edit: string;
    mandor_index: string;
    [key: string]: string;
  };
  csrf_token: string;
  flash?: {
    success?: string;
    error?: string;
  };
}

const LKHViewPage: React.FC<LKHViewProps> = ({
  app,
  mode,
  lkhData,
  assignedWorkers,
  plotData = [],
  materials = [],
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

  const goBack = () => {
    router.get(routes.mandor_index);
  };

  const goToEdit = () => {
    router.get(routes.lkh_edit);
  };

  const totals = calculateTotals();

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
              <img src={app.logo_url} alt={`Logo ${app.name}`} className="w-10 h-10 object-contain" />
              <div>
                <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                  Hasil Pekerjaan
                </h2>
                <p className="text-lg text-neutral-600">{lkhData.lkhno} - {lkhData.activitycode} - {lkhData.activityname}</p>
              </div>
            </div>

            {/* Edit Button - Only show for DRAFT status */}
            {lkhData.mobile_status === 'DRAFT' && (
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
              <div className="flex items-center gap-6">
                <div>
                  <span className="text-sm text-neutral-500">Status:</span>
                  <span className={`ml-2 px-3 py-1 rounded-full text-sm font-medium ${
                    lkhData.mobile_status === 'DRAFT' 
                      ? 'bg-yellow-100 text-yellow-700' 
                      : 'bg-green-100 text-green-700'
                  }`}>
                    {lkhData.mobile_status === 'DRAFT' ? 'Draft (Belum Submit)' : 'Selesai'}
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

        {/* Worker Summary - Read Only */}
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
                    </div>
                  </div>
                  
                  <div className="grid grid-cols-2 gap-3 text-sm">
                    <div>
                      <span className="text-neutral-500">Jam Kerja:</span>
                      <p className="font-medium">{worker.jammasuk} - {worker.jamselesai}</p>
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

        {/* Plot Results - Read Only */}
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
                    Plot {plot.plot}
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
                    <p className="text-sm text-neutral-600">Kode: {material.itemcode}</p>
                    <p className="text-lg font-bold text-orange-800 mt-2">
                      {material.qty} {material.unit}
                    </p>
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
            className="flex items-center gap-3 px-8 py-4 bg-neutral-600 text-white rounded-2xl hover:bg-neutral-700 transition-colors text-lg font-medium shadow-lg"
          >
            <ArrowLeft className="w-5 h-5" />
            <span>Kembali ke Beranda</span>
          </button>

          {lkhData.mobile_status === 'DRAFT' && (
            <button
              onClick={goToEdit}
              className="flex items-center gap-3 px-8 py-4 bg-orange-600 text-white rounded-2xl hover:bg-orange-700 transition-colors text-lg font-medium shadow-lg"
            >
              <Edit3 className="w-5 h-5" />
              <span>Edit Data</span>
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default LKHViewPage;