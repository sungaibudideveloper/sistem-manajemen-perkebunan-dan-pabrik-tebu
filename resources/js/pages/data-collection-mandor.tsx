// resources\js\pages\data-collection-mandor.tsx (FIXED VERSION - Remove Amber Text)
import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  FiArrowLeft, FiCalendar, FiUsers, FiTruck, FiClipboard,
  FiCheck, FiClock, FiMapPin, FiPackage,
  FiWifi, FiWifiOff, FiRefreshCw, FiCheckCircle, FiLoader, FiExternalLink,
  FiChevronDown, FiChevronUp, FiAlertTriangle, FiBox, FiEye, FiInfo
} from 'react-icons/fi';

// Dynamic URL Helper Functions
const getBaseUrl = (): string => {
  const origin = window.location.origin;
  const pathname = window.location.pathname;
  
  // Check if we're in development (has /tebu/public) or production
  if (pathname.includes('/tebu/public')) {
    return `${origin}/tebu/public`;
  }
  
  // Production or other environments
  return origin;
};

const buildMandorUrl = (path: string): string => {
  const baseUrl = getBaseUrl();
  // Remove leading slash if exists to avoid double slashes
  const cleanPath = path.startsWith('/') ? path.slice(1) : path;
  return `${baseUrl}/${cleanPath}`;
};

// Types
interface LKHItem {
  lkhno: string;
  activitycode: string;
  activityname: string;
  blok: string;
  plot: string[];
  totalluasplan: number;
  jenistenagakerja: string;
  status: 'READY' | 'WAITING_MATERIAL' | 'IN_PROGRESS' | 'COMPLETED' | 'DRAFT';
  mobile_status: 'EMPTY' | 'DRAFT' | 'COMPLETED';
  estimated_workers: number;
  materials_ready: boolean;
  needs_material: boolean;
}

interface PlotBreakdown {
  plot: string;
  blok: string;
  luasarea: number;
  usage: number;
  usage_formatted: string;
}

interface MaterialItem {
  itemcode: string;
  itemname: string;
  total_qty: number;
  total_qtyretur: number;
  total_qtydigunakan: number;
  unit: string;
  status: 'ACTIVE' | 'DISPATCHED' | 'RECEIVED_BY_MANDOR' | 'RETURNED_BY_MANDOR' | 'RETURN_RECEIVED' | 'COMPLETED';
  lkh_details: Array<{lkhno: string, qty: number}>;
  plot_breakdown: PlotBreakdown[];
  herbisidagroupid: number;
  dosageperha: number;
}

interface FieldCollectionProps {
  onSectionChange: (section: string) => void;
  routes: {
    lkh_ready: string;
    materials_available: string;
    material_confirm_pickup: string;
    sync_offline_data: string;
    lkh_assign: string;
    complete_all_lkh: string;
    [key: string]: string;
  };
  csrf_token: string;
}

const FieldCollectionSystem: React.FC<FieldCollectionProps> = ({ 
  onSectionChange, 
  routes, 
  csrf_token 
}) => {
  // States
  const [isOnline, setIsOnline] = useState(navigator.onLine);
  const [isSyncing, setIsSyncing] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  
  // Data States
  const [lkhList, setLkhList] = useState<LKHItem[]>([]);
  const [materialList, setMaterialList] = useState<MaterialItem[]>([]);

  // Material Management States
  const [expandedItems, setExpandedItems] = useState<Set<string>>(new Set());
  const [confirmingPickup, setConfirmingPickup] = useState<string | null>(null);
  const [overallStatus, setOverallStatus] = useState<'ACTIVE' | 'DISPATCHED' | 'RECEIVED_BY_MANDOR' | 'RETURNED_BY_MANDOR' | 'RETURN_RECEIVED' | 'COMPLETED'>('ACTIVE');

  // Network status monitoring
  useEffect(() => {
    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);
    
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    
    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, []);

  // Load initial data when component mounts
  useEffect(() => {
    loadInitialData();
  }, []);

  // API Functions
  const loadInitialData = async () => {
    setIsLoading(true);
    try {
      await Promise.all([
        loadLKHData(),
        loadMaterialsData()
      ]);
    } catch (error) {
      console.error('Error loading initial data:', error);
      handleOfflineData();
    } finally {
      setIsLoading(false);
    }
  };

  const loadLKHData = async () => {
    try {
      const response = await fetch(routes.lkh_ready, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf_token,
        },
      });

      if (!response.ok) throw new Error('Failed to fetch LKH data');
      
      const data = await response.json();
      setLkhList(data.lkh_list || []);
      
      saveToLocalStorage('lkh_data', data.lkh_list || []);
    } catch (error) {
      console.error('Error loading LKH data:', error);
      const cachedData = localStorage.getItem('field_collection_lkh_data');
      if (cachedData) {
        const parsed = JSON.parse(cachedData);
        setLkhList(parsed.data || []);
      }
    }
  };

  const loadMaterialsData = async () => {
    try {
      const response = await fetch(routes.materials_available, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf_token,
        },
      });

      if (!response.ok) throw new Error('Failed to fetch materials data');
      
      const data = await response.json();
      setMaterialList(data.materials || []);
      
      // Determine overall status based on new flow
      if (data.materials && data.materials.length > 0) {
        const statuses = data.materials.map((m: MaterialItem) => m.status);
        if (statuses.every((s: string) => s === 'COMPLETED')) {
          setOverallStatus('COMPLETED');
        } else if (statuses.some((s: string) => s === 'RETURN_RECEIVED')) {
          setOverallStatus('RETURN_RECEIVED');
        } else if (statuses.some((s: string) => s === 'RETURNED_BY_MANDOR')) {
          setOverallStatus('RETURNED_BY_MANDOR');
        } else if (statuses.some((s: string) => s === 'RECEIVED_BY_MANDOR')) {
          setOverallStatus('RECEIVED_BY_MANDOR');
        } else if (statuses.some((s: string) => s === 'DISPATCHED')) {
          setOverallStatus('DISPATCHED');
        } else {
          setOverallStatus('ACTIVE');
        }
      }
      
      saveToLocalStorage('materials_data', data.materials || []);
    } catch (error) {
      console.error('Error loading materials data:', error);
      const cachedData = localStorage.getItem('field_collection_materials_data');
      if (cachedData) {
        const parsed = JSON.parse(cachedData);
        setMaterialList(parsed.data || []);
      }
    }
  };

  const handleOfflineData = () => {
    try {
      const cachedLKH = localStorage.getItem('field_collection_lkh_data');
      const cachedMaterials = localStorage.getItem('field_collection_materials_data');
      
      if (cachedLKH) {
        const parsed = JSON.parse(cachedLKH);
        setLkhList(parsed.data || []);
      }
      
      if (cachedMaterials) {
        const parsed = JSON.parse(cachedMaterials);
        setMaterialList(parsed.data || []);
      }
    } catch (error) {
      console.error('Error loading offline data:', error);
    }
  };

  // Semi-offline data management
  const saveToLocalStorage = (key: string, data: any) => {
    try {
      localStorage.setItem(`field_collection_${key}`, JSON.stringify({
        data,
        timestamp: Date.now(),
        synced: false
      }));
    } catch (error) {
      console.error('Error saving to localStorage:', error);
    }
  };

  const syncPendingData = async () => {
    if (!isOnline) return;
    
    setIsSyncing(true);
    try {
      const offlineData: any = {};
      
      if (Object.keys(offlineData).length > 0) {
        const response = await fetch(routes.sync_offline_data, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf_token,
          },
          body: JSON.stringify({ offline_data: offlineData }),
        });
        
        if (response.ok) {
          console.log('Data synced successfully');
          await loadInitialData();
        }
      }
      
    } catch (error) {
      console.error('Sync failed:', error);
    } finally {
      setIsSyncing(false);
    }
  };

  // UPDATED: Navigation helper with dynamic URLs - FIXED
  const navigateToLKH = (lkh: LKHItem) => {
    console.log('Navigating to LKH:', lkh.lkhno, 'Status:', lkh.mobile_status);
    
    let targetPath = '';
    
    // Determine navigation based on mobile_status
    if (lkh.mobile_status === 'EMPTY') {
      // New LKH - go to assign first
      targetPath = `mandor/lkh/${lkh.lkhno}/assign`;
    } else if (lkh.mobile_status === 'DRAFT') {
      // Already has input - go to view mode
      targetPath = `mandor/lkh/${lkh.lkhno}/view`;
    } else if (lkh.mobile_status === 'COMPLETED') {
      // COMPLETED - go to view mode (readonly)
      targetPath = `mandor/lkh/${lkh.lkhno}/view`;
    }

    // Build full URL using dynamic helper
    const fullUrl = buildMandorUrl(targetPath);
    console.log('Target URL:', fullUrl);
    
    // Navigate using window.location for reliable cross-page navigation
    window.location.href = fullUrl;
  };

  const goBackToDashboard = () => {
    onSectionChange('dashboard');
  };

  // Complete All LKH Function - Enhanced with STRICT validation
  const getCompletableCount = () => {
    const total = lkhList.length;
    const ready = lkhList.filter(lkh => lkh.mobile_status === 'DRAFT').length;
    const completed = lkhList.filter(lkh => lkh.mobile_status === 'COMPLETED').length;
    const empty = lkhList.filter(lkh => lkh.mobile_status === 'EMPTY').length;
    
    return { total, ready, completed, empty };
  };

  const completeAllLKH = async () => {
    const { ready, total, empty } = getCompletableCount();
    
    // STRICT: All LKH must be DRAFT
    if (ready !== total) {
      alert(`Semua LKH harus diselesaikan terlebih dahulu.\n\nStatus saat ini: ${ready}/${total} LKH sudah diinput.\n\nSilakan selesaikan ${empty} LKH yang belum dikerjakan.`);
      return;
    }

    const confirmMessage = `Akan submit semua ${ready} LKH dan menghitung total material return.\n\nSetelah submit, data tidak bisa diubah lagi.\n\nLanjutkan?`;
    
    if (!confirm(confirmMessage)) {
      return;
    }

    setIsLoading(true);
    
    try {
      // Call API to complete all LKH
      const response = await fetch(routes.complete_all_lkh || buildMandorUrl('api/mandor/lkh/complete-all'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf_token,
        },
        body: JSON.stringify({ 
          date: new Date().toISOString().split('T')[0] 
        }),
      });

      const result = await response.json();
      
      if (result.success) {
        alert(`✅ Berhasil submit ${ready} LKH!\n\nMaterial return berhasil dihitung dan data sudah masuk sistem untuk review admin.`);
        await loadInitialData(); // Refresh data
      } else {
        throw new Error(result.message || 'Failed to complete LKH');
      }
    } catch (error) {
      console.error('Error completing all LKH:', error);
      alert('❌ Error saat submit LKH. Silakan coba lagi.');
    } finally {
      setIsLoading(false);
    }
  };

  // Material Management Functions
  const toggleExpanded = (itemcode: string) => {
    const newExpanded = new Set(expandedItems);
    if (newExpanded.has(itemcode)) {
      newExpanded.delete(itemcode);
    } else {
      newExpanded.add(itemcode);
    }
    setExpandedItems(newExpanded);
  };

  // Updated status badge with new flow
  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'ACTIVE':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-full">
            <FiClock className="w-4 h-4" />
            Menunggu Admin
          </span>
        );
      case 'DISPATCHED':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">
            <FiTruck className="w-4 h-4" />
            Siap Diambil
          </span>
        );
      case 'RECEIVED_BY_MANDOR':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full">
            <FiCheckCircle className="w-4 h-4" />
            Material Ready
          </span>
        );
      case 'RETURNED_BY_MANDOR':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 text-sm font-medium rounded-full">
            <FiPackage className="w-4 h-4" />
            Sedang Diproses Return
          </span>
        );
      case 'RETURN_RECEIVED':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-700 text-sm font-medium rounded-full">
            <FiCheck className="w-4 h-4" />
            Return Diterima
          </span>
        );
      case 'COMPLETED':
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-700 text-sm font-medium rounded-full">
            <FiCheck className="w-4 h-4" />
            Selesai
          </span>
        );
      default:
        return (
          <span className="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-700 text-sm font-medium rounded-full">
            <FiAlertTriangle className="w-4 h-4" />
            Unknown
          </span>
        );
    }
  };

  // Confirm pickup function
  const confirmPickup = async (itemcode?: string) => {
    setConfirmingPickup(itemcode || 'ALL');
    
    const confirmed = confirm(`Apakah Anda yakin sudah menerima ${itemcode ? 'material ini' : 'semua material'}?`);
    
    if (confirmed) {
      try {
        const response = await fetch(routes.material_confirm_pickup, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf_token,
          },
          body: JSON.stringify({ itemcode: itemcode || 'ALL' }),
        });

        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          await loadMaterialsData(); // Refresh data
        } else {
          alert(result.message || 'Error mengkonfirmasi penerimaan material');
        }
      } catch (error) {
        console.error('Error confirming pickup:', error);
        alert('Error mengkonfirmasi penerimaan material');
      }
    }
    
    setConfirmingPickup(null);
  };

  // Render Plot Cards Component - Compact Version
  const renderPlotCards = (plotBreakdown: PlotBreakdown[]) => {
    if (!plotBreakdown || plotBreakdown.length === 0) {
      return (
        <div className="text-center py-2">
          <p className="text-neutral-500 text-xs">
            Tidak ada data breakdown per plot
          </p>
        </div>
      );
    }

    return (
      <div className="space-y-1">
        {plotBreakdown.map((plot, index) => (
          <div 
            key={`plot-${plot.blok}-${plot.plot}-${index}`} 
            className="py-1 px-2 bg-neutral-50 rounded text-xs hover:bg-blue-50 transition-colors"
          >
            <div className="font-medium text-neutral-900">
              Plot {plot.plot} ({plot.luasarea} Ha)
            </div>
            <div className="font-medium text-blue-600 text-xs">
              {plot.usage_formatted}
            </div>
          </div>
        ))}
      </div>
    );
  };

  // PAGE: INDEX
  const renderIndexPage = () => (
    <div className="min-h-screen bg-gradient-to-b from-neutral-50 to-white">
      <div className="max-w-7xl mx-auto px-6 py-8">
        {/* Header */}
        <div className="mb-8">
          <button
            onClick={goBackToDashboard}
            className="flex items-center gap-2 text-neutral-600 hover:text-neutral-900 mb-4 transition-colors"
          >
            <FiArrowLeft className="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </button>
          
          <div>
            <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
              Koleksi Data Lapangan
            </h2>
            <div className="flex items-center gap-2 text-neutral-500 mb-4">
              <FiCalendar className="w-4 h-4" />
              <span>{new Date().toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
              })}</span>
            </div>

            {/* Action Row - Sync & Complete All - UPDATED */}
            <div className="flex items-center gap-4 flex-wrap">
              {/* Complete All Button - Enhanced with COMPLETED status */}
              {lkhList.length > 0 && (
                (() => {
                  const { ready, total, completed } = getCompletableCount();
                  const allCompleted = completed === total && total > 0;
                  
                  if (allCompleted) {
                    // All LKH completed - show disabled success button
                    return (
                      <div className="flex items-center gap-4">
                        <button
                          disabled
                          className="flex items-center gap-2 px-6 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed text-sm font-medium"
                        >
                          <FiCheckCircle className="w-4 h-4" />
                          <span>Sudah Selesai</span>
                        </button>
                        
                        <div className="flex items-center gap-2 text-sm text-green-600 bg-green-100 px-3 py-2 rounded-lg">
                          <FiCheckCircle className="w-4 h-4" />
                          <span>Semua data sudah disubmit ke sistem</span>
                        </div>
                      </div>
                    );
                  } else {
                    // Still in progress - show normal flow
                    return (
                      <>
                        <button
                          onClick={completeAllLKH}
                          disabled={isLoading || ready !== total}
                          className="flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                        >
                          {isLoading ? (
                            <>
                              <FiLoader className="w-4 h-4 animate-spin" />
                              <span>Memproses...</span>
                            </>
                          ) : (
                            <>
                              <FiCheckCircle className="w-4 h-4" />
                              <span>Complete All Data</span>
                            </>
                          )}
                        </button>

                        {/* FIXED: Progress Info without amber text */}
                        <div className="flex items-center gap-2 text-sm text-neutral-600 bg-neutral-100 px-3 py-2 rounded-lg">
                          <FiClipboard className="w-4 h-4" />
                          <span>
                            {ready}/{total} LKH Sudah Diinput
                          </span>
                        </div>
                      </>
                    );
                  }
                })()
              )}
            </div>
          </div>
        </div>

        {/* Loading State */}
        {isLoading && (
          <div className="flex items-center justify-center py-12">
            <div className="flex items-center gap-3 text-neutral-600">
              <FiLoader className="w-6 h-6 animate-spin" />
              <span>Memuat data...</span>
            </div>
          </div>
        )}

        {!isLoading && (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {/* LKH Card */}
            <div className="bg-white rounded-2xl shadow-xl border border-neutral-200">
              <div className="border-b bg-neutral-50 rounded-t-2xl p-6">
                <div className="flex items-center justify-between">
                  <h3 className="text-xl font-semibold flex items-center gap-2">
                    <FiClipboard className="w-5 h-5 text-blue-600" />
                    Laporan Kerja Harian (LKH)
                  </h3>
                  <span className="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                    {lkhList.length} tersedia
                  </span>
                </div>
              </div>
              
              <div className="p-6">
                {lkhList.length === 0 ? (
                  <div className="text-center py-8 text-neutral-500">
                    <FiClipboard className="w-12 h-12 mx-auto mb-3 text-neutral-300" />
                    <p>Tidak ada LKH yang tersedia hari ini</p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {lkhList.map((lkh) => (
                      <div
                        key={lkh.lkhno}
                        className="p-4 border border-neutral-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 transition-all group"
                      >
                        <div className="flex items-start justify-between mb-3">
                          <div>
                            <h4 className="font-semibold text-neutral-900 group-hover:text-blue-900">
                              {lkh.lkhno}
                            </h4>
                            <p className="text-sm text-neutral-600 font-medium mt-1">{lkh.activitycode} - {lkh.activityname}</p>
                          </div>
                          <div className="flex items-center gap-2">
                            <div className={`px-2 py-1 rounded-full text-xs font-medium ${
                              lkh.mobile_status === 'EMPTY' && lkh.status === 'READY' ? 'bg-yellow-100 text-yellow-700' :
                              lkh.mobile_status === 'EMPTY' && lkh.status === 'WAITING_MATERIAL' ? 'bg-red-100 text-red-700' :
                              lkh.mobile_status === 'DRAFT' ? 'bg-green-100 text-green-700' :
                              lkh.mobile_status === 'COMPLETED' ? 'bg-gray-100 text-gray-700' :
                              'bg-blue-100 text-blue-700'
                            }`}>
                              {lkh.mobile_status === 'EMPTY' && lkh.status === 'READY' ? 'Siap Dikerjakan' :
                               lkh.mobile_status === 'EMPTY' && lkh.status === 'WAITING_MATERIAL' ? 'Menunggu Material' :
                               lkh.mobile_status === 'DRAFT' ? 'Sudah Diinput' :
                               lkh.mobile_status === 'COMPLETED' ? 'Selesai' :
                               'Sedang Dikerjakan'}
                            </div>
                            
                            {/* UPDATED: View Button for COMPLETED LKH with dynamic URL */}
                            {lkh.mobile_status === 'COMPLETED' && (
                              <button
                                onClick={(e) => {
                                  e.stopPropagation();
                                  const viewUrl = buildMandorUrl(`mandor/lkh/${lkh.lkhno}/view`);
                                  console.log('Opening view URL:', viewUrl);
                                  window.location.href = viewUrl;
                                }}
                                className="flex items-center gap-1 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors"
                              >
                                <FiEye className="w-3 h-3" />
                                View
                              </button>
                            )}
                          </div>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4 text-sm mb-3">
                          <div className="flex items-center gap-2">
                            <FiMapPin className="w-4 h-4 text-neutral-500" />
                            <span>Plot: {Array.isArray(lkh.plot) ? lkh.plot.join(', ') : lkh.plot}</span>
                          </div>
                          <div className="flex items-center gap-2">
                            <FiUsers className="w-4 h-4 text-neutral-500" />
                            <span>~{lkh.estimated_workers} pekerja</span>
                          </div>
                        </div>
                        
                        <div className="mb-4 pt-3 border-t border-neutral-100">
                          <div className="flex items-center justify-between text-sm">
                            <span className="text-neutral-600">Target: {lkh.totalluasplan} Ha</span>
                            <div className="flex items-center gap-1">
                              {!lkh.needs_material ? (
                                <span></span>
                              ) : lkh.materials_ready ? (
                                <>
                                  <FiCheckCircle className="w-4 h-4 text-green-500" />
                                  <span className="text-green-600">Material Ready</span>
                                </>
                              ) : (
                                <>
                                  <FiClock className="w-4 h-4 text-yellow-500" />
                                  <span className="text-yellow-600">Menunggu Material</span>
                                </>
                              )}
                            </div>
                          </div>
                        </div>

                        <button
                          onClick={() => navigateToLKH(lkh)}
                          disabled={lkh.status === 'WAITING_MATERIAL' || lkh.mobile_status === 'COMPLETED'}
                          className={`w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${
                            lkh.mobile_status === 'DRAFT' 
                              ? 'bg-green-600 hover:bg-green-700 text-white'
                              : lkh.mobile_status === 'COMPLETED'
                              ? 'bg-gray-400 text-white cursor-not-allowed'
                              : lkh.status === 'WAITING_MATERIAL'
                              ? 'bg-gray-400 text-white cursor-not-allowed'
                              : 'bg-blue-600 hover:bg-blue-700 text-white'
                          }`}
                        >
                          {lkh.mobile_status === 'DRAFT' ? (
                            <FiEye className="w-4 h-4" />
                          ) : (
                            <FiExternalLink className="w-4 h-4" />
                          )}
                          <span>
                            {lkh.mobile_status === 'COMPLETED' ? 'Sudah Selesai' :
                             lkh.mobile_status === 'DRAFT' ? 'Lihat LKH' : 
                             lkh.status === 'WAITING_MATERIAL' ? 'Menunggu Material' :
                             'Kerjakan LKH'}
                          </span>
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Material Card - READ ONLY VERSION */}
            <div className="bg-white rounded-2xl shadow-xl border border-neutral-200">
              {/* Header */}
              <div className="border-b bg-green-50 rounded-t-2xl p-6">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-4">
                    <FiPackage className="w-6 h-6 text-green-600" />
                    <div>
                      <h3 className="text-xl font-semibold text-neutral-900">
                        Status Material
                      </h3>
                      <p className="text-sm text-neutral-600">
                        Monitoring Material Hari Ini
                      </p>
                    </div>
                  </div>
                  
                  <div className="flex items-center gap-3 flex-wrap">
                    {materialList.length > 0 && getStatusBadge(overallStatus)}
                    
                    {/* Confirm Receipt Button - Only enabled when DISPATCHED */}
                    {overallStatus === 'DISPATCHED' && materialList.length > 0 && (
                      <button
                        onClick={() => confirmPickup()}
                        disabled={confirmingPickup === 'ALL'}
                        className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                      >
                        {confirmingPickup === 'ALL' ? (
                          <FiLoader className="w-4 h-4 animate-spin" />
                        ) : (
                          <FiCheckCircle className="w-4 h-4" />
                        )}
                        Konfirmasi Terima
                      </button>
                    )}
                  </div>
                </div>
              </div>
              
              {/* Material Items - READ ONLY */}
              <div className="p-6">
                {materialList.length === 0 ? (
                  <div className="text-center py-8 text-neutral-500">
                    <FiPackage className="w-12 h-12 mx-auto mb-3 text-neutral-300" />
                    <p>Tidak ada material yang tersedia</p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {materialList.map((material) => (
                      <div key={material.itemcode} className="border border-neutral-200 rounded-xl overflow-hidden">
                        {/* Material Item Header */}
                        <div 
                          className="p-4 bg-neutral-50 cursor-pointer hover:bg-neutral-100 transition-colors"
                          onClick={() => toggleExpanded(material.itemcode)}
                        >
                          <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                              <FiBox className="w-5 h-5 text-neutral-600" />
                              <div>
                                <h4 className="font-semibold text-neutral-900">
                                  {material.itemname}
                                </h4>
                                <p className="text-sm text-neutral-600">
                                  {material.itemcode}
                                </p>
                              </div>
                            </div>
                            
                            <div className="flex items-center gap-4">
                              <div className="grid grid-cols-3 gap-6 text-center">
                                <div>
                                  <p className="text-xs text-neutral-500">Qty Diterima</p>
                                  <p className="font-semibold">{material.total_qty} {material.unit}</p>
                                </div>
                                <div>
                                  <p className="text-xs text-neutral-500">Terpakai</p>
                                  <p className="font-semibold text-green-600">{material.total_qtydigunakan || 0} {material.unit}</p>
                                </div>
                                <div>
                                  <p className="text-xs text-neutral-500">Sisa/Retur</p>
                                  <p className="font-semibold text-orange-600">{material.total_qtyretur || 0} {material.unit}</p>
                                </div>
                              </div>
                              
                              <div className="flex items-center gap-2">
                                {expandedItems.has(material.itemcode) ? (
                                  <FiChevronUp className="w-5 h-5 text-neutral-500" />
                                ) : (
                                  <FiChevronDown className="w-5 h-5 text-neutral-500" />
                                )}
                              </div>
                            </div>
                          </div>
                        </div>

                        {/* Plot Breakdown - Compact */}
                        {expandedItems.has(material.itemcode) && (
                          <div className="p-3 border-t bg-white">
                            <h5 className="font-medium text-neutral-900 mb-2 flex items-center gap-1 text-sm">
                              <FiMapPin className="w-3 h-3 text-blue-600" />
                              Detail Per Plot (Rencana)
                            </h5>
                            
                            {renderPlotCards(material.plot_breakdown)}
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );

  // Main Render
  return (
    <div>
      {renderIndexPage()}
    </div>
  );
};

export default FieldCollectionSystem;