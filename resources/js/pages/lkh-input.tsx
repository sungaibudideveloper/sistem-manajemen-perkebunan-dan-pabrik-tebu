// resources/js/pages/lkh-input.tsx - COMPLETE: With Per-Plot Material Input
import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  ArrowLeft, Users, Save, Loader, MapPin, 
  CheckCircle, Edit3, User, Package, AlertCircle, Clock, ChevronDown, ChevronUp, Plus, Minus, Truck
} from 'lucide-react';

// Dynamic URL Helper Functions
const getBaseUrl = (): string => {
  const origin = window.location.origin;
  const pathname = window.location.pathname;
  
  if (pathname.includes('/tebu/public')) {
    return `${origin}/tebu/public`;
  }
  
  return origin;
};

const buildMandorUrl = (path: string): string => {
  const baseUrl = getBaseUrl();
  const cleanPath = path.startsWith('/') ? path.slice(1) : path;
  return `${baseUrl}/${cleanPath}`;
};

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
  jammasuk?: string;
  jamselesai?: string;
  totaljamkerja?: number;
  overtimehours?: number;
}

interface PlotInput {
  blok: string;
  plot: string;
  luasarea: number;
  luashasil: number;
  luassisa: number;
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

// UPDATED: Material structure for per-plot input
interface MaterialPlotBreakdown {
  plot: string;
  planned_usage: number;
  qtysisa: number;
  qtydigunakan: number;
}

interface MaterialInput {
  itemcode: string;
  itemname: string;
  unit: string;
  plot_breakdown: MaterialPlotBreakdown[];
  total_planned: number;
  total_sisa: number;
  total_digunakan: number;
}

interface MaterialInfo {
  itemcode: string;
  itemname: string;
  unit: string;
  plot_breakdown: Array<{
    plot: string;
    luasarea: number;
    dosage_per_ha: number;
    planned_usage: number;
  }>;
}

// Vehicle input interface
interface VehicleInput {
  nokendaraan: string;
  jenis: string;
  operator_nama: string;
  plots: string[];
  jammulai: string;
  jamselesai: string;
  total_luasarea?: number;
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

interface LKHInputProps {
  title: string;
  lkhData: LKHData;
  assignedWorkers: AssignedWorker[];
  plotData: Array<{blok: string, plot: string, luasarea: number, luashasil: number, luassisa: number}>;
  materials?: MaterialInfo[];
  vehicleInfo?: VehicleInfo;
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
  app: {
    name: string;
    url: string;
    logo_url: string;
  };
}

const LKHInputPage: React.FC<LKHInputProps> = ({
  app,
  lkhData,
  assignedWorkers,
  plotData = [],
  materials = [],
  vehicleInfo,
  routes,
  csrf_token,
  flash
}) => {
  const [plotInputs, setPlotInputs] = useState<PlotInput[]>([]);
  const [workerInputs, setWorkerInputs] = useState<WorkerInput[]>([]);
  const [materialInputs, setMaterialInputs] = useState<MaterialInput[]>([]);
  const [vehicleInputs, setVehicleInputs] = useState<VehicleInput[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [keterangan, setKeterangan] = useState('');
  const [expandedWorkers, setExpandedWorkers] = useState<Set<string>>(new Set());
  const [expandedVehicles, setExpandedVehicles] = useState<Set<string>>(new Set());

  const stripSeconds = (timeString: string): string => {
    if (!timeString) return '07:00';
    if (timeString.includes(':')) {
      return timeString.substring(0, 5);
    }
    return timeString;
  };

  const calculateWorkHours = (jamMasuk: string, jamSelesai: string): number => {
    try {
      const start = new Date(`2025-01-01T${jamMasuk}:00`);
      const end = new Date(`2025-01-01T${jamSelesai}:00`);
      
      if (end.getTime() <= start.getTime()) {
        end.setDate(end.getDate() + 1);
      }
      
      const diffHours = (end.getTime() - start.getTime()) / (1000 * 60 * 60);
      return Math.max(0, diffHours);
    } catch (error) {
      console.error('Error calculating work hours:', error);
      return 8;
    }
  };

  useEffect(() => {
    if (flash?.success) {
      alert(flash.success);
    }
    if (flash?.error) {
      alert('Error: ' + flash.error);
    }
  }, [flash]);

  useEffect(() => {
    const initialPlots: PlotInput[] = plotData.map(plot => ({
      blok: plot.blok,
      plot: plot.plot,
      luasarea: plot.luasarea,
      luashasil: plot.luashasil || 0,
      luassisa: plot.luassisa || plot.luasarea
    }));
    setPlotInputs(initialPlots);
  }, [plotData]);

  useEffect(() => {
    const initialWorkers: WorkerInput[] = assignedWorkers.map(worker => {
      const jamMasuk = stripSeconds(worker.jammasuk || '07:00:00');
      const jamSelesai = stripSeconds(worker.jamselesai || '15:00:00');
      
      const totalJamKerja = calculateWorkHours(jamMasuk, jamSelesai);
      const isFullTime = jamMasuk === '07:00' && jamSelesai === '15:00';
      
      return {
        tenagakerjaid: worker.tenagakerjaid,
        nama: worker.nama,
        nik: worker.nik,
        jammasuk: jamMasuk,
        jamselesai: jamSelesai,
        totaljamkerja: totalJamKerja,
        overtimehours: worker.overtimehours || 0,
        isFullTime: isFullTime
      };
    });
    setWorkerInputs(initialWorkers);
  }, [assignedWorkers]);

  // Initialize material inputs with per-plot breakdown
  useEffect(() => {
    const initialMaterials: MaterialInput[] = materials.map(material => {
      const plotBreakdown: MaterialPlotBreakdown[] = material.plot_breakdown.map(plot => ({
        plot: plot.plot,
        planned_usage: plot.planned_usage,
        qtysisa: 0,
        qtydigunakan: plot.planned_usage
      }));

      const totalPlanned = plotBreakdown.reduce((sum, plot) => sum + plot.planned_usage, 0);

      return {
        itemcode: material.itemcode,
        itemname: material.itemname,
        unit: material.unit,
        plot_breakdown: plotBreakdown,
        total_planned: totalPlanned,
        total_sisa: 0,
        total_digunakan: totalPlanned
      };
    });
    setMaterialInputs(initialMaterials);
  }, [materials]);

  useEffect(() => {
    if (!vehicleInfo) {
      setVehicleInputs([]);
      return;
    }

    let initialVehicles: VehicleInput[] = [];

    if (vehicleInfo.is_multiple) {
      initialVehicles = vehicleInfo.vehicles.map(vehicle => ({
        nokendaraan: vehicle.nokendaraan,
        jenis: vehicle.jenis,
        operator_nama: vehicle.operator_nama,
        plots: vehicle.plots,
        jammulai: '07:00',
        jamselesai: '15:00',
        total_luasarea: vehicle.total_luasarea
      }));
    } else {
      initialVehicles = [{
        nokendaraan: vehicleInfo.nokendaraan,
        jenis: vehicleInfo.jenis,
        operator_nama: vehicleInfo.operator_nama,
        plots: vehicleInfo.plots,
        jammulai: '07:00',
        jamselesai: '15:00'
      }];
    }

    setVehicleInputs(initialVehicles);
  }, [vehicleInfo]);

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
        if (worker.jammasuk && worker.jamselesai) {
          worker.totaljamkerja = calculateWorkHours(worker.jammasuk, worker.jamselesai);
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
      
      if (worker.jammasuk && worker.jamselesai) {
        worker.totaljamkerja = calculateWorkHours(worker.jammasuk, worker.jamselesai);
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

  const adjustVehicleTime = (index: number, field: 'jammulai' | 'jamselesai', direction: 'up' | 'down') => {
    setVehicleInputs(prev => {
      const updated = [...prev];
      const vehicle = { ...updated[index] };
      
      const currentTime = vehicle[field];
      const [hours] = currentTime.split(':').map(Number);
      
      let newHours = hours;
      if (direction === 'up') {
        newHours = Math.min(23, hours + 1);
      } else {
        newHours = Math.max(0, hours - 1);
      }
      
      const newTime = `${newHours.toString().padStart(2, '0')}:00`;
      vehicle[field] = newTime;
      
      updated[index] = vehicle;
      return updated;
    });
  };

  const toggleVehicleExpanded = (nokendaraan: string) => {
    const newExpanded = new Set(expandedVehicles);
    if (newExpanded.has(nokendaraan)) {
      newExpanded.delete(nokendaraan);
    } else {
      newExpanded.add(nokendaraan);
    }
    setExpandedVehicles(newExpanded);
  };

  // Update material per plot with auto-calculation of totals
  const updateMaterialPlotInput = (materialIndex: number, plotIndex: number, field: 'qtysisa', value: number) => {
    setMaterialInputs(prev => {
      const updated = [...prev];
      const material = { ...updated[materialIndex] };
      const plotBreakdown = [...material.plot_breakdown];
      const plotMaterial = { ...plotBreakdown[plotIndex] };
      
      if (field === 'qtysisa') {
        plotMaterial.qtysisa = Math.max(0, Math.min(value, plotMaterial.planned_usage));
        plotMaterial.qtydigunakan = plotMaterial.planned_usage - plotMaterial.qtysisa;
      }
      
      plotBreakdown[plotIndex] = plotMaterial;
      material.plot_breakdown = plotBreakdown;
      
      // Recalculate totals
      material.total_sisa = plotBreakdown.reduce((sum, plot) => sum + plot.qtysisa, 0);
      material.total_digunakan = plotBreakdown.reduce((sum, plot) => sum + plot.qtydigunakan, 0);
      
      updated[materialIndex] = material;
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
    const hasValidInput = plotInputs.some(plot => plot.luashasil > 0);
    if (!hasValidInput) {
      alert('Input minimal 1 plot dengan hasil yang dikerjakan');
      return;
    }

    setIsLoading(true);
    
    try {
      const vehicleSubmissionData = vehicleInputs.flatMap(vehicle => 
        vehicle.plots.map(plot => ({
          nokendaraan: vehicle.nokendaraan,
          plot: plot,
          jammulai: vehicle.jammulai + ':00',
          jamselesai: vehicle.jamselesai + ':00'
        }))
      );

      // Send material data per plot
      const materialSubmissionData = materialInputs.flatMap(material =>
        material.plot_breakdown.map(plotMaterial => ({
          itemcode: material.itemcode,
          plot: plotMaterial.plot,
          qtyditerima: plotMaterial.planned_usage,
          qtysisa: plotMaterial.qtysisa,
          qtydigunakan: plotMaterial.qtydigunakan,
          keterangan: null
        }))
      );

      router.post(routes.lkh_save_results, {
        worker_inputs: workerInputs.map(worker => ({
          tenagakerjaid: worker.tenagakerjaid,
          jammasuk: worker.jammasuk + ':00',
          jamselesai: worker.jamselesai + ':00',
          overtimehours: worker.overtimehours
        })),
        plot_inputs: plotInputs.map(plot => ({
          plot: plot.plot,
          luashasil: plot.luashasil,
          luassisa: plot.luassisa
        })),
        material_inputs: materialSubmissionData,
        vehicle_inputs: vehicleSubmissionData,
        keterangan: keterangan,
        _token: csrf_token
      }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: (page: any) => {
          if (page.props?.flash?.success) {
            const mandorIndexUrl = buildMandorUrl('mandor');
            window.location.href = mandorIndexUrl;
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
    const assignUrl = buildMandorUrl(`mandor/lkh/${lkhData.lkhno}/assign`);
    window.location.href = assignUrl;
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
            {app?.logo_url ? (
              <img 
                src={app.logo_url} 
                alt={`Logo ${app?.name || 'App'}`} 
                className="w-10 h-10 object-contain"
                onError={(e) => {
                  console.log('Logo failed to load:', app.logo_url);
                  (e.target as HTMLImageElement).style.display = 'none';
                }}
              />
            ) : (
              <div className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-lg">{app?.name?.charAt(0) || 'A'}</span>
              </div>
            )}
            <div>
              <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                Input Hasil Pekerjaan
              </h2>
              <p className="text-lg text-neutral-600">{lkhData.lkhno} - {lkhData.activitycode} - {lkhData.activityname}</p>
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

        {/* Vehicle Time Input Section */}
        {vehicleInputs.length > 0 && (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <Truck className="w-5 h-5 text-orange-600" />
                Waktu Kerja Kendaraan ({vehicleInputs.length} unit)
              </h3>
              <p className="text-sm text-neutral-600 mt-1">
                Input jam mulai dan selesai untuk setiap kendaraan.
              </p>
            </div>
            
            <div className="p-6">
              <div className="space-y-3">
                {vehicleInputs.map((vehicle, index) => (
                  <div key={vehicle.nokendaraan} className="border border-neutral-200 rounded-xl overflow-hidden">
                    <div 
                      className="p-4 bg-orange-50 cursor-pointer hover:bg-orange-100 transition-colors"
                      onClick={() => toggleVehicleExpanded(vehicle.nokendaraan)}
                    >
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                          <Truck className="w-5 h-5 text-orange-600" />
                          <div>
                            <h4 className="font-semibold text-orange-900">{vehicle.nokendaraan}</h4>
                            <p className="text-sm text-orange-700">{vehicle.jenis} - {vehicle.operator_nama}</p>
                          </div>
                        </div>
                        
                        <div className="flex items-center gap-4">
                          <div className="text-center">
                            <p className="text-sm text-orange-600 font-medium">
                              {vehicle.jammulai} - {vehicle.jamselesai}
                            </p>
                            <p className="text-xs text-orange-500">
                              Plot: {vehicle.plots.join(', ')}
                            </p>
                          </div>
                          
                          {expandedVehicles.has(vehicle.nokendaraan) ? (
                            <ChevronUp className="w-5 h-5 text-neutral-500" />
                          ) : (
                            <ChevronDown className="w-5 h-5 text-neutral-500" />
                          )}
                        </div>
                      </div>
                    </div>

                    {expandedVehicles.has(vehicle.nokendaraan) && (
                      <div className="p-4 border-t bg-white">
                        <div className="grid grid-cols-2 gap-4">
                          <div>
                            <label className="block text-xs font-medium text-neutral-700 mb-1">
                              Jam Mulai
                            </label>
                            <div className="flex items-center">
                              <button
                                type="button"
                                onClick={() => adjustVehicleTime(index, 'jammulai', 'down')}
                                className="p-1 border border-neutral-300 rounded-l-lg bg-white hover:bg-neutral-50"
                              >
                                <Minus className="w-4 h-4" />
                              </button>
                              <input
                                type="text"
                                value={vehicle.jammulai}
                                readOnly
                                className="w-full px-2 py-2 text-sm text-center border-t border-b border-neutral-300 focus:outline-none bg-white"
                              />
                              <button
                                type="button"
                                onClick={() => adjustVehicleTime(index, 'jammulai', 'up')}
                                className="p-1 border border-neutral-300 rounded-r-lg bg-white hover:bg-neutral-50"
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
                                onClick={() => adjustVehicleTime(index, 'jamselesai', 'down')}
                                className="p-1 border border-neutral-300 rounded-l-lg bg-white hover:bg-neutral-50"
                              >
                                <Minus className="w-4 h-4" />
                              </button>
                              <input
                                type="text"
                                value={vehicle.jamselesai}
                                readOnly
                                className="w-full px-2 py-2 text-sm text-center border-t border-b border-neutral-300 focus:outline-none bg-white"
                              />
                              <button
                                type="button"
                                onClick={() => adjustVehicleTime(index, 'jamselesai', 'up')}
                                className="p-1 border border-neutral-300 rounded-r-lg bg-white hover:bg-neutral-50"
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
        )}

        {/* Worker Time Input */}
        <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
          <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
            <h3 className="font-semibold flex items-center gap-2">
              <Clock className="w-5 h-5 text-purple-600" />
              Waktu Kerja Pekerja ({workerInputs.length} orang)
            </h3>
            <p className="text-sm text-neutral-600 mt-1">
              Default: Full Time (07:00-15:00). Klik pekerja untuk mengatur waktu custom.
            </p>
          </div>
          
          <div className="p-6">
            <div className="space-y-3">
              {workerInputs.map((worker, index) => (
                <div key={worker.tenagakerjaid} className="border border-neutral-200 rounded-xl overflow-hidden">
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
                        
                        {expandedWorkers.has(worker.tenagakerjaid) ? (
                          <ChevronUp className="w-5 h-5 text-neutral-500" />
                        ) : (
                          <ChevronDown className="w-5 h-5 text-neutral-500" />
                        )}
                      </div>
                    </div>
                  </div>

                  {expandedWorkers.has(worker.tenagakerjaid) && (
                    <div className="p-4 border-t bg-white">
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
                        {plotInput.luasarea.toFixed(2)} Ha
                      </div>
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
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Material Usage Per Plot */}
        {materialInputs.length > 0 ? (
          <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 mb-8">
            <div className="border-b bg-neutral-50 rounded-t-2xl p-4">
              <h3 className="font-semibold flex items-center gap-2">
                <Package className="w-5 h-5 text-orange-600" />
                Input Sisa Material per Plot
              </h3>
              <p className="text-sm text-neutral-600 mt-1">
                Input sisa material untuk setiap plot. Terpakai akan otomatis terhitung.
              </p>
            </div>
            <div className="p-6">
              <div className="space-y-6">
                {materialInputs.map((material, materialIndex) => (
                  <div key={material.itemcode} className="border border-orange-200 rounded-xl overflow-hidden">
                    <div className="bg-orange-50 p-4">
                      <h4 className="font-semibold text-orange-900 text-lg">{material.itemname}</h4>
                      <p className="text-sm text-orange-700">
                        Total Rencana: {material.total_planned.toFixed(3)} {material.unit}
                      </p>
                    </div>
                    
                    <div className="p-4">
                      <div className="space-y-4">
                        {material.plot_breakdown.map((plotMaterial, plotIndex) => (
                          <div key={plotMaterial.plot} className="p-3 border border-neutral-200 rounded-lg bg-neutral-50">
                            <div className="flex items-center justify-between mb-3">
                              <h5 className="font-medium text-neutral-900">Plot {plotMaterial.plot}</h5>
                              <span className="text-sm text-neutral-600">
                                Rencana: {plotMaterial.planned_usage.toFixed(3)} {material.unit}
                              </span>
                            </div>
                            
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                              <div>
                                <label className="block text-xs font-medium text-neutral-700 mb-1">
                                  Rencana Pakai
                                </label>
                                <div className="px-2 py-2 text-sm border border-neutral-200 rounded bg-white text-neutral-600">
                                  {plotMaterial.planned_usage.toFixed(3)} {material.unit}
                                </div>
                              </div>
                              
                              <div>
                                <label className="block text-xs font-medium text-neutral-700 mb-1">
                                  Sisa *
                                </label>
                                <input
                                  type="number"
                                  step="0.001"
                                  min="0"
                                  max={plotMaterial.planned_usage}
                                  value={plotMaterial.qtysisa}
                                  onChange={(e) => updateMaterialPlotInput(materialIndex, plotIndex, 'qtysisa', parseFloat(e.target.value) || 0)}
                                  className="w-full px-2 py-2 text-sm border border-neutral-300 rounded focus:outline-none focus:ring-1 focus:ring-orange-500"
                                  placeholder="0.000"
                                />
                              </div>
                              
                              <div>
                                <label className="block text-xs font-medium text-neutral-700 mb-1">
                                  Terpakai
                                </label>
                                <div className={`px-2 py-2 text-sm border rounded ${
                                  plotMaterial.qtydigunakan > 0 
                                    ? 'border-green-200 bg-green-50 text-green-800' 
                                    : 'border-neutral-200 bg-neutral-50 text-neutral-600'
                                }`}>
                                  {plotMaterial.qtydigunakan.toFixed(3)} {material.unit}
                                </div>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                      
                      <div className="mt-4 p-3 bg-orange-50 rounded-lg border border-orange-200">
                        <div className="grid grid-cols-3 gap-4 text-center">
                          <div>
                            <p className="text-xs text-orange-600 font-medium">Total Rencana</p>
                            <p className="text-sm font-semibold text-orange-900">
                              {material.total_planned.toFixed(3)} {material.unit}
                            </p>
                          </div>
                          <div>
                            <p className="text-xs text-orange-600 font-medium">Total Sisa</p>
                            <p className="text-sm font-semibold text-orange-900">
                              {material.total_sisa.toFixed(3)} {material.unit}
                            </p>
                          </div>
                          <div>
                            <p className="text-xs text-orange-600 font-medium">Total Terpakai</p>
                            <p className="text-sm font-semibold text-orange-900">
                              {material.total_digunakan.toFixed(3)} {material.unit}
                            </p>
                          </div>
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

        {/* Save Button */}
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