// ===============================================
// FILE 2: resources/js/pages/lkh-input.tsx
// ===============================================

import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  FiArrowLeft, FiUsers, FiSave, FiLoader, FiMapPin, 
  FiCheckCircle, FiEdit3, FiUser
} from 'react-icons/fi';

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
}

interface AssignedWorker {
  tenagakerjaid: string;
  nama: string;
  nik: string;
}

interface PlotInput {
  plot: string;
  luasplot: number;
  hasil: number;
  sisa: number;
  materialused: number;
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
  routes: {
    lkh_save_results: string;
    [key: string]: string;
  };
  csrf_token: string;
  flash?: {
    success?: string;
    error?: string;
  };
  success?: boolean;
}

const LKHInputPage: React.FC<LKHInputProps> = ({
  app,
  title,
  lkhData,
  assignedWorkers,
  routes,
  csrf_token
}) => {
  const [plotInputs, setPlotInputs] = useState<PlotInput[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [keterangan, setKeterangan] = useState('');

  // Initialize plot inputs
  useEffect(() => {
    const initialPlots: PlotInput[] = lkhData.plot.map(plot => ({
      plot,
      luasplot: 0,
      hasil: 0,
      sisa: 0,
      materialused: 0
    }));
    setPlotInputs(initialPlots);
  }, [lkhData.plot]);

  const updatePlotInput = (index: number, field: keyof PlotInput, value: number) => {
    setPlotInputs(prev => {
      const updated = [...prev];
      updated[index] = { ...updated[index], [field]: value };
      return updated;
    });
  };

  const calculateTotals = () => {
    return plotInputs.reduce((totals, plot) => ({
      totalLuasPlot: totals.totalLuasPlot + plot.luasplot,
      totalHasil: totals.totalHasil + plot.hasil,
      totalSisa: totals.totalSisa + plot.sisa,
      totalMaterial: totals.totalMaterial + plot.materialused
    }), {
      totalLuasPlot: 0,
      totalHasil: 0,
      totalSisa: 0,
      totalMaterial: 0
    });
  };

  const saveResults = async () => {
    // Validation
    const hasValidInput = plotInputs.some(plot => plot.hasil > 0 || plot.luasplot > 0);
    if (!hasValidInput) {
      alert('Input minimal 1 plot dengan hasil atau luas yang dikerjakan');
      return;
    }

    setIsLoading(true);
    
    try {
      // Use Inertia router for POST request
      router.post(routes.lkh_save_results, {
        assigned_workers: assignedWorkers as any,
        plot_inputs: plotInputs as any,
        keterangan: keterangan,
        _token: csrf_token
      }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: (page: any) => {
          // Check if response contains success message
          if (page.props?.flash?.success || page.props?.success) {
            alert('Data hasil pekerjaan berhasil disimpan! LKH sudah masuk ke sistem untuk review admin.');
            // Navigate back to mandor index with field collection tab
            router.get('/mandor', {}, {
              preserveState: false,
              preserveScroll: false,
              onSuccess: () => {
                // Set hash after navigation completes
                window.location.hash = '#field-collection';
              }
            });
          }
        },
        onError: (errors) => {
          console.error('Save results errors:', errors);
          alert('Error menyimpan hasil pekerjaan: ' + (errors.message || 'Unknown error'));
        },
        onFinish: () => {
          setIsLoading(false);
        }
      });
      
    } catch (error) {
      console.error('Error saving results:', error);
      alert('Error menyimpan hasil pekerjaan');
      setIsLoading(false);
    }
  };

  const goBack = () => {
    // Navigate back to assignment page
    router.get(`/mandor/lkh/${lkhData.lkhno}/assign`, {}, {
      preserveState: false,
      preserveScroll: false
    });
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
            <FiArrowLeft className="w-4 h-4" />
            <span>Kembali</span>
          </button>
          
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <img src={app.logo_url} alt={`Logo ${app.name}`} className="w-10 h-10 object-contain" />
              <div>
                <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                  Input Hasil Pekerjaan
                </h2>
                <p className="text-lg text-neutral-600">{lkhData.lkhno} - {lkhData.activityname}</p>
              </div>
            </div>
            
            <button
              onClick={saveResults}
              disabled={isLoading}
              className="flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isLoading ? (
                <>
                  <FiLoader className="w-4 h-4 animate-spin" />
                  <span>Menyimpan...</span>
                </>
              ) : (
                <>
                  <FiSave className="w-4 h-4" />
                  <span>Simpan Hasil</span>
                </>
              )}
            </button>
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
                <label className="text-sm font-medium text-neutral-500">Blok</label>
                <p className="text-lg font-semibold">Blok {lkhData.blok}</p>
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

        {/* Assigned Workers */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <FiUsers className="w-5 h-5 text-green-600" />
              Tim Pekerja ({assignedWorkers.length} orang)
            </h3>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {assignedWorkers.map((worker) => (
                <div
                  key={worker.tenagakerjaid}
                  className="p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3"
                >
                  <FiUser className="w-5 h-5 text-green-600" />
                  <div>
                    <h4 className="font-semibold text-green-900">{worker.nama}</h4>
                    <p className="text-sm text-green-700">NIK: {worker.nik}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Plot Input Form */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <FiEdit3 className="w-5 h-5 text-blue-600" />
              Input Hasil per Plot
            </h3>
            <p className="text-sm text-neutral-600 mt-1">
              Input hasil tim untuk setiap plot. Backend akan otomatis distribusi ke setiap pekerja.
            </p>
          </div>
          
          <div className="p-6">
            <div className="space-y-6">
              {plotInputs.map((plotInput, index) => (
                <div key={plotInput.plot} className="p-4 border border-neutral-200 rounded-xl">
                  <h4 className="font-semibold text-lg mb-4 text-blue-900">
                    Plot {plotInput.plot}
                  </h4>
                  
                  <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Luas Plot Dikerjakan (Ha)
                      </label>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        value={plotInput.luasplot}
                        onChange={(e) => updatePlotInput(index, 'luasplot', parseFloat(e.target.value) || 0)}
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                      />
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Hasil Selesai (Ha)
                      </label>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        value={plotInput.hasil}
                        onChange={(e) => updatePlotInput(index, 'hasil', parseFloat(e.target.value) || 0)}
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                      />
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Sisa Belum Selesai (Ha)
                      </label>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        value={plotInput.sisa}
                        onChange={(e) => updatePlotInput(index, 'sisa', parseFloat(e.target.value) || 0)}
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                      />
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Material Digunakan (Kg/L)
                      </label>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        value={plotInput.materialused}
                        onChange={(e) => updatePlotInput(index, 'materialused', parseFloat(e.target.value) || 0)}
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                      />
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Summary Card */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <FiCheckCircle className="w-5 h-5 text-green-600" />
              Ringkasan Total
            </h3>
          </div>
          <div className="p-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="text-center p-4 bg-blue-50 rounded-xl">
                <p className="text-sm text-blue-600 font-medium">Total Luas Dikerjakan</p>
                <p className="text-2xl font-bold text-blue-900">{totals.totalLuasPlot.toFixed(2)} Ha</p>
              </div>
              <div className="text-center p-4 bg-green-50 rounded-xl">
                <p className="text-sm text-green-600 font-medium">Total Hasil Selesai</p>
                <p className="text-2xl font-bold text-green-900">{totals.totalHasil.toFixed(2)} Ha</p>
              </div>
              <div className="text-center p-4 bg-yellow-50 rounded-xl">
                <p className="text-sm text-yellow-600 font-medium">Total Sisa</p>
                <p className="text-2xl font-bold text-yellow-900">{totals.totalSisa.toFixed(2)} Ha</p>
              </div>
              <div className="text-center p-4 bg-purple-50 rounded-xl">
                <p className="text-sm text-purple-600 font-medium">Total Material</p>
                <p className="text-2xl font-bold text-purple-900">{totals.totalMaterial.toFixed(2)} Kg/L</p>
              </div>
            </div>
          </div>
        </div>

        {/* Keterangan */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold">Keterangan (Opsional)</h3>
          </div>
          <div className="p-6">
            <textarea
              value={keterangan}
              onChange={(e) => setKeterangan(e.target.value)}
              className="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows={4}
              placeholder="Catatan tambahan mengenai pekerjaan hari ini..."
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default LKHInputPage;