// ===============================================
// FILE: resources/js/pages/lkh-input.tsx
// ===============================================

import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  ArrowLeft, Users, Save, Loader, MapPin, 
  CheckCircle, Edit3, User, Package, AlertCircle
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
  needs_material?: boolean;
}

interface AssignedWorker {
  tenagakerjaid: string;
  nama: string;
  nik: string;
}

interface PlotInput {
  plot: string;
  luasplan: number; // Read from database
  hasil: number;    // User input
  sisa: number;     // Calculated: luasplan - hasil
  materialused: number;
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

interface LKHInputProps extends SharedProps {
  title: string;
  lkhData: LKHData;
  assignedWorkers: AssignedWorker[];
  plotData: Array<{plot: string, luasarea: number}>; // From rkhlst
  materials?: MaterialInfo[];
  routes: {
    lkh_save_results: string;
    lkh_assign: string;
    mandor_index: string;
    [key: string]: string;
  };
  csrf_token: string;
  flash?: {
    success?: string;
    error?: string;
  };
}

const LKHInputPage: React.FC<LKHInputProps> = ({
  app,
  lkhData,
  assignedWorkers,
  plotData = [],
  materials = [],
  routes,
  csrf_token,
  flash
}) => {
  const [plotInputs, setPlotInputs] = useState<PlotInput[]>([]);
  const [materialUsage, setMaterialUsage] = useState<{[key: string]: number}>({});
  const [isLoading, setIsLoading] = useState(false);
  const [keterangan, setKeterangan] = useState('');

  // Handle flash messages
  useEffect(() => {
    if (flash?.success) {
      alert(flash.success);
    }
    if (flash?.error) {
      alert('Error: ' + flash.error);
    }
  }, [flash]);

  // Initialize plot inputs with data from database
  useEffect(() => {
    const initialPlots: PlotInput[] = plotData.map(plot => ({
      plot: plot.plot,
      luasplan: plot.luasarea, // Read-only from database
      hasil: 0,               // User input
      sisa: plot.luasarea,    // Initially same as luasplan
      materialused: 0
    }));
    setPlotInputs(initialPlots);
  }, [plotData]);

  const updatePlotInput = (index: number, field: 'hasil', value: number) => {
    setPlotInputs(prev => {
      const updated = [...prev];
      const plot = { ...updated[index] };
      
      if (field === 'hasil') {
        plot.hasil = Math.max(0, Math.min(value, plot.luasplan)); // Max = luasplan
        plot.sisa = Math.max(0, plot.luasplan - plot.hasil); // Auto calculate sisa
      }
      
      updated[index] = plot;
      return updated;
    });
  };

  const updateMaterialUsage = (itemcode: string, value: number) => {
    setMaterialUsage(prev => ({
      ...prev,
      [itemcode]: Math.max(0, value)
    }));
  };

  const calculateTotals = () => {
    return plotInputs.reduce((totals, plot) => ({
      totalLuasPlan: totals.totalLuasPlan + plot.luasplan,
      totalHasil: totals.totalHasil + plot.hasil,
      totalSisa: totals.totalSisa + plot.sisa,
    }), {
      totalLuasPlan: 0,
      totalHasil: 0,
      totalSisa: 0,
    });
  };

  const saveResults = async () => {
    // Validation
    const hasValidInput = plotInputs.some(plot => plot.hasil > 0);
    if (!hasValidInput) {
      alert('Input minimal 1 plot dengan hasil yang dikerjakan');
      return;
    }

    // Calculate total material used
    const totalMaterialUsed = Object.values(materialUsage).reduce((sum, qty) => sum + qty, 0);

    setIsLoading(true);
    
    try {
      router.post(routes.lkh_save_results, {
        assigned_workers: assignedWorkers as any,
        plot_inputs: plotInputs.map(plot => ({
          plot: plot.plot,
          luasplot: plot.luasplan,
          hasil: plot.hasil,
          sisa: plot.sisa, 
          materialused: totalMaterialUsed / plotInputs.length // Distribute equally
        })) as any,
        material_usage: materialUsage as any,
        keterangan: keterangan,
        _token: csrf_token
      }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: (page: any) => {
          if (page.props?.flash?.success) {
            // Navigate back to mandor index
            router.get(routes.mandor_index);
          }
        },
        onError: (errors) => {
          console.error('Save results errors:', errors);
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
      console.error('Error saving results:', error);
      alert('Network error: ' + (error instanceof Error ? error.message : 'Unknown error'));
      setIsLoading(false);
    }
  };

  const goBack = () => {
    router.get(routes.lkh_assign);
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
            <span>Kembali</span>
          </button>
          
          <div className="flex items-center gap-4">
            <img src={app.logo_url} alt={`Logo ${app.name}`} className="w-10 h-10 object-contain" />
            <div>
              <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                Input Hasil Pekerjaan
              </h2>
              <p className="text-lg text-neutral-600">{lkhData.lkhno} - {lkhData.activityname}</p>
            </div>
          </div>
        </div>

        {/* Compact LKH Info */}
        <div className="bg-white rounded-xl shadow-sm border border-neutral-200 mb-6">
          <div className="p-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-6">
                <div>
                  <span className="text-sm text-neutral-500">Tanggal:</span>
                  <span className="ml-2 font-medium">{new Date(lkhData.lkhdate).toLocaleDateString('id-ID')}</span>
                </div>
                <div>
                  <span className="text-sm text-neutral-500">Blok:</span>
                  <span className="ml-2 font-medium">{lkhData.blok}</span>
                </div>
                <div>
                  <span className="text-sm text-neutral-500">Tim:</span>
                  <span className="ml-2 font-medium">{assignedWorkers.length} pekerja</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Plot Input Form */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <Edit3 className="w-5 h-5 text-blue-600" />
              Input Hasil per Plot
            </h3>
            <p className="text-sm text-neutral-600 mt-1">
              Input hasil tim untuk setiap plot. Sisa akan otomatis terhitung.
            </p>
          </div>
          
          <div className="p-6">
            <div className="space-y-6">
              {plotInputs.map((plotInput, index) => (
                <div key={plotInput.plot} className="p-4 border border-neutral-200 rounded-xl">
                  <h4 className="font-semibold text-lg mb-4 text-blue-900">
                    Plot {plotInput.plot}
                  </h4>
                  
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Luas Rencana (Ha)
                      </label>
                      <div className="w-full px-3 py-2 border border-neutral-200 rounded-lg bg-neutral-50 text-neutral-600">
                        {plotInput.luasplan.toFixed(2)} Ha
                      </div>
                      <p className="text-xs text-neutral-500 mt-1">Dari rencana kerja</p>
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Hasil Selesai (Ha) *
                      </label>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        max={plotInput.luasplan}
                        value={plotInput.hasil}
                        onChange={(e) => updatePlotInput(index, 'hasil', parseFloat(e.target.value) || 0)}
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                      />
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Sisa (Ha)
                      </label>
                      <div className={`w-full px-3 py-2 border rounded-lg ${
                        plotInput.sisa > 0 
                          ? 'border-yellow-200 bg-yellow-50 text-yellow-800' 
                          : 'border-green-200 bg-green-50 text-green-800'
                      }`}>
                        {plotInput.sisa.toFixed(2)} Ha
                      </div>
                      <p className="text-xs text-neutral-500 mt-1">Otomatis terhitung</p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Material Usage */}
        {materials.length > 0 ? (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <Package className="w-5 h-5 text-orange-600" />
                Penggunaan Material
              </h3>
            </div>
            <div className="p-6">
              <div className="space-y-4">
                {materials.map((material) => (
                  <div key={material.itemcode} className="flex items-center justify-between p-4 border border-neutral-200 rounded-lg">
                    <div>
                      <h4 className="font-medium">{material.itemname}</h4>
                      <p className="text-sm text-neutral-600">
                        Tersedia: {material.qty} {material.unit}
                      </p>
                    </div>
                    <div className="flex items-center gap-3">
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        max={material.qty}
                        value={materialUsage[material.itemcode] || 0}
                        onChange={(e) => updateMaterialUsage(material.itemcode, parseFloat(e.target.value) || 0)}
                        className="w-24 px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                      <span className="text-sm text-neutral-600">{material.unit}</span>
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

        {/* Summary */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <CheckCircle className="w-5 h-5 text-green-600" />
              Ringkasan Total
            </h3>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="text-center p-4 bg-blue-50 rounded-xl">
                <p className="text-sm text-blue-600 font-medium">Luas Rencana</p>
                <p className="text-2xl font-bold text-blue-900">{totals.totalLuasPlan.toFixed(2)} Ha</p>
              </div>
              <div className="text-center p-4 bg-green-50 rounded-xl">
                <p className="text-sm text-green-600 font-medium">Total Hasil</p>
                <p className="text-2xl font-bold text-green-900">{totals.totalHasil.toFixed(2)} Ha</p>
                <p className="text-xs text-green-600 mt-1">
                  {totals.totalLuasPlan > 0 ? Math.round((totals.totalHasil / totals.totalLuasPlan) * 100) : 0}% selesai
                </p>
              </div>
              <div className="text-center p-4 bg-yellow-50 rounded-xl">
                <p className="text-sm text-yellow-600 font-medium">Total Sisa</p>
                <p className="text-2xl font-bold text-yellow-900">{totals.totalSisa.toFixed(2)} Ha</p>
              </div>
            </div>
          </div>
        </div>

        {/* Keterangan */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold">Keterangan (Opsional)</h3>
          </div>
          <div className="p-6">
            <textarea
              value={keterangan}
              onChange={(e) => setKeterangan(e.target.value)}
              className="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows={3}
              placeholder="Catatan tambahan mengenai pekerjaan hari ini..."
            />
          </div>
        </div>

        {/* Save Button - Bottom */}
        <div className="flex justify-center">
          <button
            onClick={saveResults}
            disabled={isLoading}
            className="flex items-center gap-3 px-8 py-4 bg-green-600 text-white rounded-2xl hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-lg font-medium shadow-lg"
          >
            {isLoading ? (
              <>
                <Loader className="w-5 h-5 animate-spin" />
                <span>Menyimpan...</span>
              </>
            ) : (
              <>
                <Save className="w-5 h-5" />
                <span>Simpan Hasil Pekerjaan</span>
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
};

export default LKHInputPage;