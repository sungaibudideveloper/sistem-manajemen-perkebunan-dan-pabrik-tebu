import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import {
  FiArrowLeft, FiRefreshCw, FiCamera, FiUsers, FiCheck, FiCalendar, FiEye, FiX,
  FiEdit3, FiClock, FiXCircle, FiCheckCircle, FiAlertTriangle
} from 'react-icons/fi';
import Camera from '../components/camera';
import { LoadingCard, LoadingInline, LoadingOverlay } from '../components/loading-spinner';

interface Worker {
  tenagakerjaid: string;
  nama: string;
  nik: string;
  gender: string;
  jenistenagakerja: number;
}

interface AttendanceRecord {
  absenno: string;
  absen_id: number;
  tenagakerjaid: string;
  absenmasuk: string;
  fotoabsen: string;
  lokasifotolat: number | null;
  lokasifotolng: number | null;
  approval_status: 'PENDING' | 'APPROVED' | 'REJECTED';
  approval_date: string | null;
  approved_by: string | null;
  rejection_reason: string | null;
  rejection_date: string | null;
  is_edited: boolean;
  edit_count: number;
  tenaga_kerja: {
    nama: string;
    nik: string;
    gender: string;
    jenistenagakerja: number;
  };
}

interface AbsenMandorProps {
  routes: {
    workers: string;
    attendance_today: string;
    process_checkin: string;
    update_photo: string;
    rejected_attendance: string;
  };
  csrf_token: string;
  onSectionChange: (section: string) => void;
}

const AbsenMandor: React.FC<AbsenMandorProps> = ({ 
  routes,
  csrf_token,
  onSectionChange 
}) => {
  const [workers, setWorkers] = useState<Worker[]>([]);
  const [todayAttendance, setTodayAttendance] = useState<AttendanceRecord[]>([]);
  const [selectedWorker, setSelectedWorker] = useState<Worker | null>(null);
  const [editingAttendance, setEditingAttendance] = useState<AttendanceRecord | null>(null);
  const [isCameraOpen, setIsCameraOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [attendanceDate, setAttendanceDate] = useState(new Date().toISOString().split('T')[0]);
  const [viewingPhoto, setViewingPhoto] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<'all' | 'pending' | 'approved' | 'rejected'>('all');
  
  // Loading states
  const [isLoadingWorkers, setIsLoadingWorkers] = useState(true);
  const [isLoadingAttendance, setIsLoadingAttendance] = useState(true);

  useEffect(() => {
    loadWorkersData();
  }, []);

  useEffect(() => {
    loadAttendanceData();
  }, [attendanceDate]);

  const loadWorkersData = async () => {
    setIsLoadingWorkers(true);
    try {
      const response = await fetch(routes.workers);
      const data = await response.json();
      setWorkers(data.workers || []);
    } catch (error) {
      console.error('Error loading workers:', error);
    } finally {
      setIsLoadingWorkers(false);
    }
  };

  const loadAttendanceData = async () => {
    setIsLoadingAttendance(true);
    try {
      const response = await fetch(`${routes.attendance_today}?date=${attendanceDate}`);
      const data = await response.json();
      setTodayAttendance(data.attendance || []);
    } catch (error) {
      console.error('Error loading attendance:', error);
    } finally {
      setIsLoadingAttendance(false);
    }
  };

  const getAvailableWorkers = () => {
    const attendedWorkerIds = todayAttendance.map(att => att.tenagakerjaid);
    return workers.filter(worker => !attendedWorkerIds.includes(worker.tenagakerjaid));
  };

  const getFilteredAttendance = () => {
    switch (activeTab) {
      case 'pending':
        return todayAttendance.filter(att => att.approval_status === 'PENDING');
      case 'approved':
        return todayAttendance.filter(att => att.approval_status === 'APPROVED');
      case 'rejected':
        return todayAttendance.filter(att => att.approval_status === 'REJECTED');
      default:
        return todayAttendance;
    }
  };

  const handleWorkerSelect = (worker: Worker) => {
    setSelectedWorker(worker);
    setEditingAttendance(null);
    setIsCameraOpen(true);
  };

  const handleEditPhoto = (attendance: AttendanceRecord) => {
    setEditingAttendance(attendance);
    setSelectedWorker(null);
    setIsCameraOpen(true);
  };

  const handlePhotoCapture = async (photoDataUrl: string) => {
    if (!selectedWorker && !editingAttendance) return;

    setIsSubmitting(true);
    try {
      let url: string;
      let payload: any;

      if (editingAttendance) {
        // Update existing photo
        url = routes.update_photo;
        payload = {
          absenno: editingAttendance.absenno,
          absen_id: editingAttendance.absen_id,
          tenagakerjaid: editingAttendance.tenagakerjaid,
          photo: photoDataUrl
        };
      } else if (selectedWorker) {
        // New attendance
        url = routes.process_checkin;
        payload = {
          tenagakerjaid: selectedWorker.tenagakerjaid,
          photo: photoDataUrl
        };
      } else {
        // Should never reach here, but safety check
        throw new Error('No worker or editing attendance selected');
      }

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf_token
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if (result.success) {
        // AUTO REFRESH data setelah berhasil absen/update
        await Promise.all([
          loadWorkersData(),
          loadAttendanceData()
        ]);
        
        const workerName = editingAttendance 
          ? editingAttendance.tenaga_kerja.nama 
          : selectedWorker?.nama;
        
        const message = editingAttendance 
          ? `Foto berhasil diupdate untuk ${workerName} (status direset ke PENDING)`
          : `Absen berhasil untuk ${workerName}`;
        
        alert(message);
      } else {
        alert(result.error || result.message || 'Gagal menyimpan');
      }
    } catch (error) {
      console.error('Error submitting attendance:', error);
      alert('Terjadi kesalahan saat menyimpan');
    } finally {
      setIsSubmitting(false);
      setSelectedWorker(null);
      setEditingAttendance(null);
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'PENDING':
        return (
          <span className="flex items-center gap-1 px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
            <FiClock className="w-3 h-3" />
            Pending
          </span>
        );
      case 'APPROVED':
        return (
          <span className="flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
            <FiCheckCircle className="w-3 h-3" />
            Approved
          </span>
        );
      case 'REJECTED':
        return (
          <span className="flex items-center gap-1 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
            <FiXCircle className="w-3 h-3" />
            Rejected
          </span>
        );
      default:
        return null;
    }
  };

  const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('id-ID', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long', 
      day: 'numeric'
    });
  };

  const getJenisKerja = (jenis: number) => {
    switch(jenis) {
      case 1: return 'Harian';
      case 2: return 'Borongan';
      default: return 'Lainnya';
    }
  };

  const getTabCount = (tab: string) => {
    switch (tab) {
      case 'pending':
        return todayAttendance.filter(att => att.approval_status === 'PENDING').length;
      case 'approved':
        return todayAttendance.filter(att => att.approval_status === 'APPROVED').length;
      case 'rejected':
        return todayAttendance.filter(att => att.approval_status === 'REJECTED').length;
      default:
        return todayAttendance.length;
    }
  };

  const availableWorkers = getAvailableWorkers();
  const filteredAttendance = getFilteredAttendance();
  const isToday = selectedDate === new Date().toISOString().split('T')[0];

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-6 py-8">
        {/* Header */}
        <div className="mb-8">
          <button
            onClick={() => onSectionChange('dashboard')}
            className="flex items-center gap-2 text-gray-600 hover:text-black mb-4 transition-colors"
          >
            <FiArrowLeft className="w-4 h-4" />
            <span className="text-sm font-medium">Kembali ke Beranda</span>
          </button>
          
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-black mb-2">Sistem Absensi</h1>
              <p className="text-gray-600">Pencatatan kehadiran pekerja dengan foto dan approval individual</p>
            </div>
          </div>
          
          {/* Date Info */}
          <div className="mt-4 p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-semibold text-black">
                  {formatDate(new Date().toISOString().split('T')[0])}
                  <span className="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Hari ini</span>
                </h3>
                <p className="text-sm text-gray-500">
                  {isLoadingWorkers ? (
                    "Memuat data pekerja..."
                  ) : (
                    `${availableWorkers.length} pekerja belum absen • ${workers.length} total pekerja`
                  )}
                </p>
              </div>
              <div className="text-right">
                <div className="text-2xl font-bold text-black">
                  {isLoadingWorkers ? (
                    <div className="animate-pulse bg-gray-200 h-8 w-12 rounded"></div>
                  ) : (
                    `${workers.length > 0 ? Math.round(((workers.length - availableWorkers.length) / workers.length) * 100) : 0}%`
                  )}
                </div>
                <div className="text-sm text-gray-500">Kehadiran</div>
              </div>
            </div>
          </div>
        </div>

        {/* Main Content */}
        <div className="grid grid-cols-1 2xl:grid-cols-2 gap-8">
          
          {/* Left Card - Belum Absen */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="border-b border-gray-200 p-6">
              <div className="flex items-center gap-3">
                <FiUsers className="w-5 h-5 text-orange-500" />
                <h2 className="text-xl font-semibold text-black">
                  Belum Absen {isLoadingWorkers ? '' : `(${availableWorkers.length})`}
                </h2>
                {isLoadingWorkers && (
                  <LoadingInline color="orange" />
                )}
              </div>
            </div>
            
            <div className="max-h-96 overflow-y-auto">
              {isLoadingWorkers ? (
                <LoadingCard text="Memuat data pekerja..." />
              ) : availableWorkers.length > 0 ? (
                <div className="divide-y divide-gray-100">
                  {availableWorkers.map((worker) => (
                    <div
                      key={worker.tenagakerjaid}
                      className="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                    >
                      <div className="flex-1">
                        <div className="font-medium text-black">{worker.nama}</div>
                        <div className="text-sm text-gray-500">
                          {worker.nik} • {worker.gender === 'L' ? 'Laki-laki' : 'Perempuan'} • {getJenisKerja(worker.jenistenagakerja)}
                        </div>
                      </div>
                      
                      {isToday && (
                        <button 
                          onClick={() => handleWorkerSelect(worker)}
                          className="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors"
                        >
                          <FiCamera className="w-4 h-4" />
                          <span className="text-sm">Absen</span>
                        </button>
                      )}
                    </div>
                  ))}
                </div>
              ) : (
                <div className="p-8 text-center">
                  <FiCheck className="w-12 h-12 text-green-500 mx-auto mb-4" />
                  <p className="text-gray-500">Semua pekerja sudah absen</p>
                </div>
              )}
            </div>
          </div>

          {/* Right Card - Data Absen */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="border-b border-gray-200 p-6">
              <div className="flex items-center gap-3 mb-3">
                <FiCheck className="w-5 h-5 text-blue-500" />
                <h2 className="text-xl font-semibold text-black">
                  Data Absen {isLoadingAttendance ? '' : `(${todayAttendance.length})`}
                </h2>
                {isLoadingAttendance && (
                  <LoadingInline color="blue" />
                )}
              </div>
              
              {/* Date Filter */}
              <div className="flex items-center gap-2 mb-4">
                <FiCalendar className="w-4 h-4 text-gray-500" />
                <input
                  type="date"
                  value={attendanceDate}
                  onChange={(e) => setAttendanceDate(e.target.value)}
                  className="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span className="text-sm text-gray-500">
                  {formatDate(attendanceDate)}
                </span>
              </div>

              {/* Status Tabs */}
              <div className="flex gap-2">
                {[
                  { key: 'all', label: 'Semua', icon: FiUsers },
                  { key: 'pending', label: 'Pending', icon: FiClock },
                  { key: 'approved', label: 'Approved', icon: FiCheckCircle },
                  { key: 'rejected', label: 'Rejected', icon: FiXCircle },
                ].map(tab => {
                  const Icon = tab.icon;
                  const count = getTabCount(tab.key);
                  const isActive = activeTab === tab.key;
                  
                  return (
                    <button
                      key={tab.key}
                      onClick={() => setActiveTab(tab.key as any)}
                      className={`flex items-center gap-1 px-3 py-2 text-xs rounded-lg transition-colors ${
                        isActive 
                          ? 'bg-blue-600 text-white' 
                          : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                      }`}
                    >
                      <Icon className="w-3 h-3" />
                      <span>{tab.label}</span>
                      <span className={`px-1.5 py-0.5 rounded-full text-xs ${
                        isActive ? 'bg-white bg-opacity-20' : 'bg-gray-200'
                      }`}>
                        {count}
                      </span>
                    </button>
                  );
                })}
              </div>
            </div>
            
            <div className="max-h-96 overflow-y-auto">
              {isLoadingAttendance ? (
                <LoadingCard text="Memuat data absensi..." />
              ) : filteredAttendance.length > 0 ? (
                <div className="divide-y divide-gray-100">
                  {filteredAttendance.map((record) => (
                    <div
                      key={`${record.tenagakerjaid}-${record.absenmasuk}`}
                      className="flex items-center justify-between p-4"
                    >
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <div className="font-medium text-black">{record.tenaga_kerja.nama}</div>
                          {getStatusBadge(record.approval_status)}
                          {record.is_edited && (
                            <span className="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded">
                              Edited {record.edit_count}x
                            </span>
                          )}
                        </div>
                        <div className="text-sm text-gray-500">
                          {record.tenaga_kerja.nik} • {formatTime(record.absenmasuk)}
                        </div>
                        {record.rejection_reason && (
                          <div className="text-xs text-red-600 mt-1">
                            <FiAlertTriangle className="w-3 h-3 inline mr-1" />
                            {record.rejection_reason}
                          </div>
                        )}
                      </div>
                      
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => setViewingPhoto(record.fotoabsen)}
                          className="flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                        >
                          <FiEye className="w-4 h-4" />
                          <span className="text-sm">Lihat</span>
                        </button>
                        
                        {/* Edit button - only for REJECTED or PENDING and today */}
                        {(record.approval_status === 'REJECTED' || record.approval_status === 'PENDING') && isToday && (
                          <button
                            onClick={() => handleEditPhoto(record)}
                            className={`flex items-center gap-2 px-3 py-2 rounded-lg transition-colors ${
                              record.approval_status === 'REJECTED'
                                ? 'bg-red-100 text-red-700 hover:bg-red-200'
                                : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                            }`}
                          >
                            <FiEdit3 className="w-4 h-4" />
                            <span className="text-sm">Edit</span>
                          </button>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="p-8 text-center">
                  <FiUsers className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                  <p className="text-gray-500">
                    {activeTab === 'all' 
                      ? 'Belum ada yang absen'
                      : `Tidak ada absensi dengan status ${activeTab.toUpperCase()}`
                    }
                  </p>
                  <p className="text-xs text-gray-400 mt-1">untuk {formatDate(attendanceDate)}</p>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Camera Modal */}
        <Camera
          isOpen={isCameraOpen}
          onClose={() => {
            setIsCameraOpen(false);
            setSelectedWorker(null);
            setEditingAttendance(null);
          }}
          onCapture={handlePhotoCapture}
          workerName={
            editingAttendance 
              ? `${editingAttendance.tenaga_kerja.nama} (Edit)`
              : selectedWorker?.nama
          }
        />

        {/* Photo Viewer Modal */}
        {viewingPhoto && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90">
            <div className="relative max-w-4xl max-h-[90vh] p-4">
              <button
                onClick={() => setViewingPhoto(null)}
                className="absolute -top-2 -right-2 p-2 bg-white rounded-full shadow-lg hover:bg-gray-100 transition-colors z-10"
              >
                <FiX className="w-5 h-5" />
              </button>
              <img
                src={viewingPhoto}
                alt="Foto Absensi"
                className="max-w-full max-h-full object-contain rounded-lg"
              />
            </div>
          </div>
        )}

        {/* Loading Overlay for Submission */}
        {isSubmitting && (
          <LoadingOverlay text="Menyimpan absensi..." />
        )}
      </div>
    </div>
  );
};

export default AbsenMandor;