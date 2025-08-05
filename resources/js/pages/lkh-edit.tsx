// resources/js/pages/lkh-edit.tsx - EDIT MODE FOR DRAFT LKH

import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  ArrowLeft, Users, Save, Loader, MapPin, 
  CheckCircle, Edit3, User, Package, AlertCircle, Clock, ChevronDown, ChevronUp, Plus, Minus
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

interface PlotInput {
  blok: string;
  plot: string;
  luasarea: number;  // From lkhdetailplot.luasrkh (read-only)
  luashasil: number; // User input - hasil yang dikerjakan
  luassisa: number;  // Calculated: luasarea - luashasil
}

interface WorkerInput {
  tenagakerjaid: string;
  nama: string;
  nik: string;
  jammasuk: string;
  jamselesai: string;
  totaljamkerja: number;
  overtimehours: number;
  isFullTime: boolean;
}

interface MaterialInput {
  itemcode: string;
  itemname: string;
  qtyditerima: number;
  qtysisa: number;
  qtydigunakan: number;
  unit: string;
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

interface LKHEditProps extends SharedProps {
  title: string;
  mode: 'edit';
  lkhData: LKHData;
  assignedWorkers: AssignedWorker[];
  plotData: Array<{blok: string, plot: string, luasarea: number, luashasil: number, luassisa: number}>;
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

const LKHEditPage: React.FC<LKHEditProps> = ({
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
  const [workerInputs, setWorkerInputs] = useState<WorkerInput[]>([]);
  const [materialInputs, setMaterialInputs] = useState<MaterialInput[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [keterangan, setKeterangan] = useState('');
  const [expandedWorkers, setExpandedWorkers] = useState<Set<string>>(new Set());

  // Handle flash messages
  useEffect(() => {
    if (flash?.success) {
      alert(flash.success);
    }
    if (flash?.error) {
      alert('Error: ' + flash.error);
    }
  }, [flash]);

  // Initialize plot inputs from database (pre-populate with existing data)
  useEffect(() => {
    const initialPlots: PlotInput[] = plotData.map(plot => ({
      blok: plot.blok,
      plot: plot.plot,
      luasarea: plot.luasarea,
      luashasil: plot.luashasil || 0, // Pre-populated from existing data
      luassisa: plot.luassisa || (plot.luasarea - (plot.luashasil || 0))
    }));
    setPlotInputs(initialPlots);
  }, [plotData]);

  // Initialize worker inputs (pre-populate with existing data)
  useEffect(() => {
    const initialWorkers: WorkerInput[] = assignedWorkers.map(worker => {
      // Determine if it's full time based on existing data
      const isFullTime = worker.jammasuk === '07:00' && worker.jamselesai === '15:00';
      
      return {
        tenagakerjaid: worker.tenagakerjaid,
        nama: worker.nama,
        nik: worker.nik,
        jammasuk: worker.jammasuk || '07:00',
        jamselesai: worker.jamselesai || '15:00',
        totaljamkerja: worker.totaljamkerja || 8,
        overtimehours: worker.overtimehours || 0,
        isFullTime: isFullTime
      };
    });
    setWorkerInputs(initialWorkers);
  }, [assignedWorkers]);

  // Initialize material inputs (pre-populate if available)
  useEffect(() => {
    const initialMaterials: MaterialInput[] = materials.map(material => ({
      itemcode: material.itemcode,
      itemname: material.itemname,
      qtyditerima: material.qty,
      qtysisa: 0, // This would need to be fetched from lkhdetailmaterial if exists
      qtydigunakan: material.qty,
      unit: material.unit
    }));
    setMaterialInputs(initialMaterials);
  }, [materials]);

  const updatePlotInput = (index: number, field: 'luashasil', value: number) => {
    setPlotInputs(prev => {
      const updated = [...prev];
      const plot = { ...updated[index] };
      
      if (field === 'luashasil') {
        plot.luashasil = Math.max(0, Math.min(value, plot.luasarea));
        plot.luassisa = Math.max(0, plot.luasarea - plot.luashasil);
      }
      
      updated[index] = plot;
      return updated;
    });
  };

  const updateWorkerInput = (index: number, field: keyof WorkerInput, value: any) => {
    setWorkerInputs(prev => {
      const updated = [...prev];
      const worker = { ...updated[index] };
      
      if (field === 'isFullTime') {
        worker.isFullTime = value;
        if (value) {
          worker.jammasuk = '07:00';
          worker.jamselesai = '15:00';
          worker.totaljamkerja = 8;
          worker.overtimehours = 0;
        }
      } else if (field === 'jammasuk' || field === 'jamselesai') {
        worker[field] = value;
        // Recalculate total jam kerja
        if (worker.jammasuk && worker.jamselesai) {
          const start = new Date(`2025-01-01T${worker.jammasuk}:00`);
          const end = new Date(`2025-01-01T${worker.jamselesai}:00`);
          const diffHours = (end.getTime() - start.getTime()) / (1000 * 60 * 60);
          worker.totaljamkerja = Math.max(0, diffHours);
        }
      } else {
        (worker as any)[field] = value;
      }
      
      updated[index] = worker;
      return updated;
    });
  };

  const adjustWorkerTime = (index: number, field: 'jammasuk' | 'jamselesai', direction: 'up' | 'down') => {
    setWorkerInputs(prev => {
      const updated = [...prev];
      const worker = { ...updated[index] };
      
      const currentTime = worker[field];
      const [hours] = currentTime.split(':').map(Number);
      
      let newHours = hours;
      if (direction === 'up') {
        newHours = Math.min(23, hours + 1);
      } else {
        newHours = Math.max(0, hours - 1);
      }
      
      const newTime = `${newHours.toString().padStart(2, '0')}:00`;
      worker[field] = newTime;
      
      // Recalculate total jam kerja
      if (worker.jammasuk && worker.jamselesai) {
        const start = new Date(`2025-01-01T${worker.jammasuk}:00`);
        const end = new Date(`2025-01-01T${worker.jamselesai}:00`);
        const diffHours = (end.getTime() - start.getTime()) / (1000 * 60 * 60);
        worker.totaljamkerja = Math.max(0, diffHours);
      }
      
      updated[index] = worker;
      return updated;
    });
  };

  const adjustWorkerOvertime = (index: number, direction: 'up' | 'down') => {
    setWorkerInputs(prev => {
      const updated = [...prev];
      const worker = { ...updated[index] };
      
      if (direction === 'up') {
        worker.overtimehours = Math.min(8, worker.overtimehours + 1);
      } else {
        worker.overtimehours = Math.max(0, worker.overtimehours - 1);
      }
      
      updated[index] = worker;
      return updated;
    });
  };

  const updateMaterialInput = (index: number, field: 'qtysisa', value: number) => {
    setMaterialInputs(prev => {
      const updated = [...prev];
      const material = { ...updated[index] };
      
      if (field === 'qtysisa') {
        material.qtysisa = Math.max(0, Math.min(value, material.qtyditerima));
        material.qtydigunakan = material.qtyditerima - material.qtysisa;
      }
      
      updated[index] = material;
      return updated;
    });
  };

  const toggleWorkerExpanded = (tenagakerjaid: string) => {
    const newExpanded = new Set(expandedWorkers);
    if (newExpanded.has(tenagakerjaid)) {
      newExpanded.delete(tenagakerjaid);
    } else {
      newExpanded.add(tenagakerjaid);
    }
    setExpandedWorkers(newExpanded);
  };

  const calculateTotals = () => {
    return plotInputs.reduce((totals, plot) => ({
      totalLuasPlan: totals.totalLuasPlan + plot.luasarea,
      totalHasil: totals.totalHasil + plot.luashasil,
      totalSisa: totals.totalSisa + plot.luassisa,
    }), {
      totalLuasPlan: 0,
      totalHasil: 0,
      totalSisa: 0,
    });
  };

  const saveResults = async () => {
    // Validation
    const hasValidInput = plotInputs.some(plot => plot.luashasil > 0);
    if (!hasValidInput) {
      alert('Input minimal 1 plot dengan hasil yang dikerjakan');
      return;
    }

    setIsLoading(true);
    
    try {
      router.post(routes.lkh_save_results, {
        worker_inputs: workerInputs.map(worker => ({
          tenagakerjaid: worker.tenagakerjaid,
          jammasuk: worker.jammasuk,
          jamselesai: worker.jamselesai,
          overtimehours: worker.overtimehours
        })),
        plot_inputs: plotInputs.map(plot => ({
          plot: plot.plot,
          luashasil: plot.luashasil,
          luassisa: plot.luassisa
        })),
        material_inputs: materialInputs.map(material => ({
          itemcode: material.itemcode,
          qtysisa: material.qtysisa,
          keterangan: null
        })),
        keterangan: keterangan,
        _token: csrf_token
      }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: (page: any) => {
          // Will redirect to view mode via controller
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
    router.get(routes.lkh_view);
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
            <span>Kembali ke View</span>
          </button>
          
          <div className="flex items-center gap-4">
            <img src={app.logo_url} alt={`Logo ${app.name}`} className="w-10 h-10 object-contain" />
            <div>
              <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                Edit Hasil Pekerjaan
              </h2>
              <p className="text-lg text-neutral-600">{lkhData.lkhno} - {lkhData.activitycode} - {lkhData.activityname}</p>
              <p className="text-sm text-orange-600 font-medium">Status: Draft - Dapat diedit</p>
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

        {/* Worker Time Input - Same as input page but pre-populated */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <Clock className="w-5 h-5 text-purple-600" />
              Waktu Kerja Pekerja ({workerInputs.length} orang)
            </h3>
            <p className="text-sm text-neutral-600 mt-1">
              Edit waktu kerja untuk setiap pekerja. Klik pekerja untuk mengatur waktu.
            </p>
          </div>
          
          <div className="p-6">
            <div className="space-y-3">
              {workerInputs.map((worker, index) => (
                <div key={worker.tenagakerjaid} className="border border-neutral-200 rounded-xl overflow-hidden">
                  {/* Worker Header - Always Visible */}
                  <div 
                    className="p-4 bg-neutral-50 cursor-pointer hover:bg-neutral-100 transition-colors"
                    onClick={() => toggleWorkerExpanded(worker.tenagakerjaid)}
                  >
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3">
                        <User className="w-5 h-5 text-purple-600" />
                        <div>
                          <h4 className="font-semibold text-purple-900">{worker.nama}</h4>
                        </div>
                      </div>
                      
                      <div className="flex items-center gap-4">
                        {/* Status Badge */}
                        <div className="text-center">
                          <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                            worker.isFullTime 
                              ? 'bg-green-100 text-green-700' 
                              : 'bg-red-100 text-red-700'
                          }`}>
                            {worker.isFullTime ? 'Full Time' : 'Tidak Full Time'}
                          </span>
                          {!worker.isFullTime && (
                            <p className="text-xs text-neutral-500 mt-1">
                              {worker.jammasuk}-{worker.jamselesai}
                              {worker.overtimehours > 0 && ` +${worker.overtimehours}h`}
                            </p>
                          )}
                        </div>
                        
                        {/* Dropdown Icon */}
                        {expandedWorkers.has(worker.tenagakerjaid) ? (
                          <ChevronUp className="w-5 h-5 text-neutral-500" />
                        ) : (
                          <ChevronDown className="w-5 h-5 text-neutral-500" />
                        )}
                      </div>
                    </div>
                  </div>

                  {/* Worker Details - Expandable */}
                  {expandedWorkers.has(worker.tenagakerjaid) && (
                    <div className="p-4 border-t bg-white">
                      {/* Full Time Toggle */}
                      <div className="mb-4">
                        <label className="flex items-center gap-2 cursor-pointer">
                          <input
                            type="checkbox"
                            checked={worker.isFullTime}
                            onChange={(e) => updateWorkerInput(index, 'isFullTime', e.target.checked)}
                            className="rounded text-purple-600 focus:ring-purple-500"
                          />
                          <span className="text-sm font-medium">Full Time (07:00-15:00)</span>
                        </label>
                      </div>
                      
                      {/* Time Inputs - Same as input page */}
                      <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                          <label className="block text-xs font-medium text-neutral-700 mb-1">
                            Jam Masuk
                          </label>
                          <div className="flex items-center">
                            <button
                              type="button"
                              onClick={() => adjustWorkerTime(index, 'jammasuk', 'down')}
                              disabled={worker.isFullTime}
                              className={`p-1 border rounded-l-lg ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-400 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300 hover:bg-neutral-50'
                              }`}
                            >
                              <Minus className="w-4 h-4" />
                            </button>
                            <input
                              type="text"
                              value={worker.jammasuk}
                              readOnly
                              disabled={worker.isFullTime}
                              className={`w-full px-2 py-2 text-sm text-center border-t border-b focus:outline-none ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-500 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300'
                              }`}
                            />
                            <button
                              type="button"
                              onClick={() => adjustWorkerTime(index, 'jammasuk', 'up')}
                              disabled={worker.isFullTime}
                              className={`p-1 border rounded-r-lg ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-400 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300 hover:bg-neutral-50'
                              }`}
                            >
                              <Plus className="w-4 h-4" />
                            </button>
                          </div>
                        </div>
                        
                        <div>
                          <label className="block text-xs font-medium text-neutral-700 mb-1">
                            Jam Selesai
                          </label>
                          <div className="flex items-center">
                            <button
                              type="button"
                              onClick={() => adjustWorkerTime(index, 'jamselesai', 'down')}
                              disabled={worker.isFullTime}
                              className={`p-1 border rounded-l-lg ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-400 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300 hover:bg-neutral-50'
                              }`}
                            >
                              <Minus className="w-4 h-4" />
                            </button>
                            <input
                              type="text"
                              value={worker.jamselesai}
                              readOnly
                              disabled={worker.isFullTime}
                              className={`w-full px-2 py-2 text-sm text-center border-t border-b focus:outline-none ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-500 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300'
                              }`}
                            />
                            <button
                              type="button"
                              onClick={() => adjustWorkerTime(index, 'jamselesai', 'up')}
                              disabled={worker.isFullTime}
                              className={`p-1 border rounded-r-lg ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-400 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300 hover:bg-neutral-50'
                              }`}
                            >
                              <Plus className="w-4 h-4" />
                            </button>
                          </div>
                        </div>
                        
                        <div>
                          <label className="block text-xs font-medium text-neutral-700 mb-1">
                            Total Jam
                          </label>
                          <div className={`w-full px-2 py-2 text-sm text-center border rounded-lg ${
                            worker.isFullTime
                              ? 'bg-neutral-100 text-neutral-500'
                              : 'bg-neutral-50 text-neutral-600 border-neutral-200'
                          }`}>
                            {Math.floor(worker.totaljamkerja)} jam
                          </div>
                        </div>
                        
                        <div>
                          <label className="block text-xs font-medium text-neutral-700 mb-1">
                            Lembur (jam)
                          </label>
                          <div className="flex items-center">
                            <button
                              type="button"
                              onClick={() => adjustWorkerOvertime(index, 'down')}
                              disabled={worker.isFullTime}
                              className={`p-1 border rounded-l-lg ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-400 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300 hover:bg-neutral-50'
                              }`}
                            >
                              <Minus className="w-4 h-4" />
                            </button>
                            <input
                              type="text"
                              value={worker.overtimehours}
                              readOnly
                              disabled={worker.isFullTime}
                              className={`w-full px-2 py-2 text-sm text-center border-t border-b focus:outline-none ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-500 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300'
                              }`}
                            />
                            <button
                              type="button"
                              onClick={() => adjustWorkerOvertime(index, 'up')}
                              disabled={worker.isFullTime}
                              className={`p-1 border rounded-r-lg ${
                                worker.isFullTime 
                                  ? 'bg-neutral-100 text-neutral-400 cursor-not-allowed' 
                                  : 'bg-white border-neutral-300 hover:bg-neutral-50'
                              }`}
                            >
                              <Plus className="w-4 h-4" />
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Plot Input Form - Same as input page but pre-populated */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <Edit3 className="w-5 h-5 text-blue-600" />
              Edit Hasil per Plot
            </h3>
            <p className="text-sm text-neutral-600 mt-1">
              Edit hasil tim untuk setiap plot. Sisa akan otomatis terhitung.
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
                        {plotInput.luasarea.toFixed(2)} Ha
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
                        max={plotInput.luasarea}
                        value={plotInput.luashasil}
                        onChange={(e) => updatePlotInput(index, 'luashasil', parseFloat(e.target.value) || 0)}
                        className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00"
                      />
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Sisa (Ha)
                      </label>
                      <div className={`w-full px-3 py-2 border rounded-lg ${
                        plotInput.luassisa > 0 
                          ? 'border-yellow-200 bg-yellow-50 text-yellow-800' 
                          : 'border-green-200 bg-green-50 text-green-800'
                      }`}>
                        {plotInput.luassisa.toFixed(2)} Ha
                      </div>
                      <p className="text-xs text-neutral-500 mt-1">Otomatis terhitung</p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Material Usage - Same as input page */}
        {materialInputs.length > 0 ? (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <Package className="w-5 h-5 text-orange-600" />
                Edit Sisa Material
              </h3>
            </div>
            <div className="p-6">
              <div className="space-y-4">
                {materialInputs.map((material, index) => (
                  <div key={material.itemcode} className="p-4 border border-neutral-200 rounded-xl">
                    <h4 className="font-semibold text-orange-900 mb-4">{material.itemname}</h4>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                      <div>
                        <label className="block text-sm font-medium text-neutral-700 mb-2">
                          Qty Diterima
                        </label>
                        <div className="w-full px-3 py-2 border border-neutral-200 rounded-lg bg-neutral-50 text-neutral-600">
                          {material.qtyditerima} {material.unit}
                        </div>
                      </div>
                      
                      <div>
                        <label className="block text-sm font-medium text-neutral-700 mb-2">
                          Sisa *
                        </label>
                        <input
                          type="number"
                          step="0.01"
                          min="0"
                          max={material.qtyditerima}
                          value={material.qtysisa}
                          onChange={(e) => updateMaterialInput(index, 'qtysisa', parseFloat(e.target.value) || 0)}
                          className="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                          placeholder="0.00"
                        />
                      </div>
                      
                      <div>
                        <label className="block text-sm font-medium text-neutral-700 mb-2">
                          Terpakai
                        </label>
                        <div className="w-full px-3 py-2 border border-green-200 rounded-lg bg-green-50 text-green-800">
                          {material.qtydigunakan.toFixed(2)} {material.unit}
                        </div>
                      </div>
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
        <div className="flex justify-center gap-4">
          <button
            onClick={goBack}
            className="flex items-center gap-3 px-8 py-4 bg-neutral-600 text-white rounded-2xl hover:bg-neutral-700 transition-colors text-lg font-medium shadow-lg"
          >
            <ArrowLeft className="w-5 h-5" />
            <span>Batal Edit</span>
          </button>

          <button
            onClick={saveResults}
            disabled={isLoading}
            className="flex items-center gap-3 px-8 py-4 bg-orange-600 text-white rounded-2xl hover:bg-orange-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-lg font-medium shadow-lg"
          >
            {isLoading ? (
              <>
                <Loader className="w-5 h-5 animate-spin" />
                <span>Menyimpan...</span>
              </>
            ) : (
              <>
                <Save className="w-5 h-5" />
                <span>Simpan Perubahan</span>
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
};

export default LKHEditPage;