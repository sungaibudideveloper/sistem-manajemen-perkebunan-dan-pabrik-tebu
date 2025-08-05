import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  FiArrowLeft, FiCalendar, FiUsers, FiTruck, FiClipboard,
  FiCheck, FiClock, FiMapPin, FiPackage, FiSave,
  FiWifi, FiWifiOff, FiRefreshCw, FiPlus, FiX,
  FiUser, FiEdit3, FiCheckCircle, FiLoader, FiExternalLink
} from 'react-icons/fi';

// Types
interface LKHItem {
  lkhno: string;
  activitycode: string;
  activityname: string;
  blok: string;
  plot: string[];
  totalluasplan: number;
  jenistenagakerja: string;
  status: 'READY' | 'WAITING_MATERIAL' | 'IN_PROGRESS' | 'COMPLETED';
  estimated_workers: number;
  materials_ready: boolean;
  needs_material: boolean;
}

interface MaterialItem {
  itemcode: string;
  itemname: string;
  qty: number;
  qtyretur: number;
  unit: string;
  nouse: string;
  noretur?: string;
  status: 'ACTIVE' | 'SUBMITTED' | 'RECEIVED';
}

interface WorkerAssignment {
  tenagakerjaid: string;
  nama: string;
  nik: string;
  assigned: boolean;
}

interface AttendanceRecord {
  tenagakerjaid: string;
  absenmasuk: string;
  foto_base64: string;
  lokasi_lat: number;
  lokasi_lng: number;
  tenaga_kerja: {
    nama: string;
    nik: string;
    gender: string;
    jenistenagakerja: string;
  };
}

interface FieldCollectionProps {
  onSectionChange: (section: string) => void;
  routes: {
    lkh_ready: string;
    materials_available: string;
    materials_save_returns: string;
    sync_offline_data: string;
    // Add LKH assignment route template
    lkh_assign: string;
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
  const [currentPage, setCurrentPage] = useState<'index' | 'material-return'>('index');
  const [isOnline, setIsOnline] = useState(navigator.onLine);
  const [isSyncing, setIsSyncing] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  
  // Data States
  const [lkhList, setLkhList] = useState<LKHItem[]>([]);
  const [materialList, setMaterialList] = useState<MaterialItem[]>([]);

  // Material Return States
  const [materialReturns, setMaterialReturns] = useState<{[key: string]: number}>({});

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
    if (currentPage === 'index') {
      loadInitialData();
    }
  }, [currentPage]);

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
      
      const pendingMaterialReturns = localStorage.getItem('field_collection_material_returns');
      if (pendingMaterialReturns) {
        try {
          const data = JSON.parse(pendingMaterialReturns);
          if (!data.synced) {
            offlineData.material_returns = [data.data];
          }
        } catch (e) {
          console.error('Error parsing offline material returns:', e);
        }
      }
      
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
          if (pendingMaterialReturns) {
            const data = JSON.parse(pendingMaterialReturns);
            data.synced = true;
            localStorage.setItem('field_collection_material_returns', JSON.stringify(data));
          }
          
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

  // FIXED: Navigation helper using Laravel route with proper environment handling
  const navigateToLKH = (lkh: LKHItem) => {
    // Check if we have the route template from controller
    if (routes.lkh_assign && routes.lkh_assign.includes('__LKHNO__')) {
      // Use Laravel route helper with proper replacement
      const assignUrl = routes.lkh_assign.replace('__LKHNO__', lkh.lkhno);
      router.get(assignUrl, {}, {
        preserveState: false,
        preserveScroll: false,
        onError: (errors) => {
          console.error('Navigation error:', errors);
          // Fallback to window.location
          window.location.href = assignUrl;
        }
      });
    } else {
      // Fallback: construct URL manually for development environment
      const baseUrl = window.location.origin + '/tebu/public';
      const assignUrl = `${baseUrl}/mandor/lkh/${lkh.lkhno}/assign`;
      
      router.get(assignUrl, {}, {
        preserveState: false,
        preserveScroll: false,
        onError: (errors) => {
          console.error('Navigation error:', errors);
          // Final fallback
          window.location.href = assignUrl;
        }
      });
    }
  };

  // Navigate back to dashboard using callback
  const goBackToDashboard = () => {
    onSectionChange('dashboard');
  };

  // PAGE 1: INDEX - SIMPLIFIED
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
          
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                Koleksi Data Lapangan
              </h2>
              <div className="flex items-center gap-2 text-neutral-500">
                <FiCalendar className="w-4 h-4" />
                <span>{new Date().toLocaleDateString('id-ID', { 
                  weekday: 'long', 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                })}</span>
              </div>
            </div>
            
            {/* Network Status */}
            <div className="flex items-center gap-3">
              <div className={`flex items-center gap-2 px-3 py-2 rounded-lg ${
                isOnline ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
              }`}>
                {isOnline ? <FiWifi className="w-4 h-4" /> : <FiWifiOff className="w-4 h-4" />}
                <span className="text-sm font-medium">
                  {isOnline ? 'Online' : 'Offline'}
                </span>
              </div>
              
              {isOnline && (
                <button
                  onClick={syncPendingData}
                  disabled={isSyncing}
                  className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                  <FiRefreshCw className={`w-4 h-4 ${isSyncing ? 'animate-spin' : ''}`} />
                  <span>{isSyncing ? 'Syncing...' : 'Sync Data'}</span>
                </button>
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
                            <p className="text-sm text-neutral-600">{lkh.activityname}</p>
                          </div>
                          <div className={`px-2 py-1 rounded-full text-xs font-medium ${
                            lkh.status === 'READY' ? 'bg-green-100 text-green-700' :
                            lkh.status === 'WAITING_MATERIAL' ? 'bg-yellow-100 text-yellow-700' :
                            lkh.status === 'IN_PROGRESS' ? 'bg-blue-100 text-blue-700' :
                            'bg-gray-100 text-gray-700'
                          }`}>
                            {lkh.status === 'READY' ? 'Siap Dikerjakan' :
                             lkh.status === 'WAITING_MATERIAL' ? 'Menunggu Material' :
                             lkh.status === 'IN_PROGRESS' ? 'Sedang Dikerjakan' :
                             'Selesai'}
                          </div>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4 text-sm mb-3">
                          <div className="flex items-center gap-2">
                            <FiMapPin className="w-4 h-4 text-neutral-500" />
                            <span>Blok {lkh.blok} • {lkh.plot.length} plot</span>
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
                                  <span className="text-yellow-600">Waiting Material</span>
                                </>
                              )}
                            </div>
                          </div>
                        </div>

                        {/* Action Button - FIXED */}
                        <button
                          onClick={() => navigateToLKH(lkh)}
                          disabled={lkh.status === 'WAITING_MATERIAL'}
                          className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                          <FiExternalLink className="w-4 h-4" />
                          <span>Kerjakan LKH</span>
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Material Card */}
            <div className="bg-white rounded-2xl shadow-xl border border-neutral-200">
              <div className="border-b bg-neutral-50 rounded-t-2xl p-6">
                <div className="flex items-center justify-between">
                  <h3 className="text-xl font-semibold flex items-center gap-2">
                    <FiPackage className="w-5 h-5 text-green-600" />
                    Material & Retur
                  </h3>
                  <button
                    onClick={() => setCurrentPage('material-return')}
                    className="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium hover:bg-green-200 transition-colors"
                  >
                    Input Retur
                  </button>
                </div>
              </div>
              
              <div className="p-6">
                {materialList.length === 0 ? (
                  <div className="text-center py-8 text-neutral-500">
                    <FiPackage className="w-12 h-12 mx-auto mb-3 text-neutral-300" />
                    <p>Tidak ada material yang tersedia</p>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {materialList.map((material) => (
                      <div
                        key={material.itemcode}
                        className="p-4 border border-neutral-200 rounded-xl"
                      >
                        <div className="flex items-start justify-between mb-3">
                          <div>
                            <h4 className="font-semibold text-neutral-900">
                              {material.itemname}
                            </h4>
                            <p className="text-sm text-neutral-600">NO: {material.nouse}</p>
                          </div>
                          <div className={`px-2 py-1 rounded-full text-xs font-medium ${
                            material.status === 'ACTIVE' ? 'bg-yellow-100 text-yellow-700' :
                            material.status === 'SUBMITTED' ? 'bg-green-100 text-green-700' :
                            'bg-gray-100 text-gray-700'
                          }`}>
                            {material.status === 'ACTIVE' ? 'Siap Diambil' :
                             material.status === 'SUBMITTED' ? 'Sudah Diambil' :
                             'Dikembalikan'}
                          </div>
                        </div>
                        
                        <div className="grid grid-cols-3 gap-4 text-sm">
                          <div>
                            <span className="text-neutral-500">Qty:</span>
                            <p className="font-semibold">{material.qty} {material.unit}</p>
                          </div>
                          <div>
                            <span className="text-neutral-500">Retur:</span>
                            <p className="font-semibold text-orange-600">{material.qtyretur} {material.unit}</p>
                          </div>
                          <div>
                            <span className="text-neutral-500">Terpakai:</span>
                            <p className="font-semibold text-green-600">
                              {material.qty - material.qtyretur} {material.unit}
                            </p>
                          </div>
                        </div>
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

  // PAGE 2: MATERIAL RETURN (Keep as is, but simplified)
  const renderMaterialReturnPage = () => {
    const saveMaterialReturns = async () => {
      setIsLoading(true);
      
      try {
        if (isOnline) {
          const response = await fetch(routes.materials_save_returns, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf_token,
            },
            body: JSON.stringify({ material_returns: materialReturns }),
          });

          if (!response.ok) {
            throw new Error('Failed to save material returns');
          }

          const result = await response.json();
          
          if (result.success) {
            alert('Data retur material berhasil disimpan!');
            setMaterialList(prev => prev.map(material => ({
              ...material,
              qtyretur: materialReturns[material.itemcode] || material.qtyretur,
              status: (materialReturns[material.itemcode] || 0) > 0 ? 'RECEIVED' : material.status
            })));
            setMaterialReturns({});
          } else {
            throw new Error(result.message || 'Failed to save returns');
          }
        } else {
          saveToLocalStorage('material_returns', materialReturns);
          alert('Data retur tersimpan offline. Akan disinkronisasi saat online.');
        }
        
      } catch (error) {
        console.error('Error saving material returns:', error);
        saveToLocalStorage('material_returns', materialReturns);
        alert('Error menyimpan ke server. Data tersimpan offline.');
      } finally {
        setIsLoading(false);
      }
    };

    return (
      <div className="min-h-screen bg-gradient-to-b from-neutral-50 to-white">
        <div className="max-w-7xl mx-auto px-6 py-8">
          {/* Header */}
          <div className="mb-8">
            <button
              onClick={() => setCurrentPage('index')}
              className="flex items-center gap-2 text-neutral-600 hover:text-neutral-900 mb-4 transition-colors"
            >
              <FiArrowLeft className="w-4 h-4" />
              <span>Kembali ke Index</span>
            </button>
            
            <div className="flex items-center justify-between">
              <div>
                <h2 className="text-3xl font-bold tracking-tight text-neutral-900 mb-2">
                  Retur Material
                </h2>
                <p className="text-lg text-neutral-600">Input sisa dan pengembalian material</p>
              </div>
              
              <button
                onClick={saveMaterialReturns}
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
                    <span>Simpan Retur</span>
                  </>
                )}
              </button>
            </div>
          </div>

          {/* Material Return Forms */}
          <div className="space-y-6">
            {materialList.filter(m => m.status === 'SUBMITTED').map((material) => (
              <div key={material.itemcode} className="bg-white rounded-2xl shadow-lg border border-neutral-200">
                <div className="border-b bg-neutral-50 rounded-t-2xl p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <h3 className="text-xl font-semibold">{material.itemname}</h3>
                      <p className="text-sm text-neutral-600">NO: {material.nouse} • Kode: {material.itemcode}</p>
                    </div>
                    <div className="text-right">
                      <p className="text-sm text-neutral-500">Qty Diambil</p>
                      <p className="text-2xl font-bold text-blue-600">{material.qty} {material.unit}</p>
                    </div>
                  </div>
                </div>
                
                <div className="p-6">
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Retur / Sisa (tulis 0 jika habis dipakai)
                      </label>
                      <div className="relative">
                        <input
                          type="number"
                          step="0.1"
                          min="0"
                          max={material.qty}
                          value={materialReturns[material.itemcode] || 0}
                          onChange={(e) => setMaterialReturns(prev => ({
                            ...prev,
                            [material.itemcode]: parseFloat(e.target.value) || 0
                          }))}
                          className="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500"
                          placeholder="0"
                        />
                        <span className="absolute right-4 top-3 text-neutral-500">{material.unit}</span>
                      </div>
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Terpakai
                      </label>
                      <div className="px-4 py-3 bg-neutral-100 rounded-xl">
                        <span className="text-lg font-semibold text-green-600">
                          {material.qty - (materialReturns[material.itemcode] || 0)} {material.unit}
                        </span>
                      </div>
                    </div>
                    
                    <div>
                      <label className="block text-sm font-medium text-neutral-700 mb-2">
                        Status
                      </label>
                      <div className="px-4 py-3 bg-neutral-100 rounded-xl">
                        <span className={`font-semibold ${
                          (materialReturns[material.itemcode] || 0) > 0 
                            ? 'text-orange-600' 
                            : 'text-green-600'
                        }`}>
                          {(materialReturns[material.itemcode] || 0) > 0 ? 'Ada Sisa' : 'Habis Terpakai'}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  {(materialReturns[material.itemcode] || 0) > 0 && (
                    <div className="mt-4 p-4 bg-orange-50 border border-orange-200 rounded-xl">
                      <div className="flex items-center gap-2 text-orange-700">
                        <FiPackage className="w-4 h-4" />
                        <span className="font-medium">
                          Material akan dikembalikan: {materialReturns[material.itemcode]} {material.unit}
                        </span>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
          
          {materialList.filter(m => m.status === 'SUBMITTED').length === 0 && (
            <div className="bg-white rounded-2xl shadow-lg border border-neutral-200 p-12 text-center">
              <FiPackage className="w-16 h-16 text-neutral-400 mx-auto mb-4" />
              <h3 className="text-xl font-semibold text-neutral-600 mb-2">
                Tidak Ada Material untuk Diretur
              </h3>
              <p className="text-neutral-500">
                Semua material masih dalam status siap diambil atau sudah dikembalikan.
              </p>
            </div>
          )}
        </div>
      </div>
    );
  };

  // Main Render
  return (
    <div>
      {currentPage === 'index' && renderIndexPage()}
      {currentPage === 'material-return' && renderMaterialReturnPage()}
    </div>
  );
};

export default FieldCollectionSystem;