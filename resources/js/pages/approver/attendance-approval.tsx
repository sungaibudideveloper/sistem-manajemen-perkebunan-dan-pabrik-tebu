// resources/js/pages/approver/attendance-approval.tsx - UPDATED for Individual Approval

import React, { useState, useEffect } from 'react';
import {
  FiArrowLeft, FiClock, FiCheck, FiX, FiEye, FiCalendar, FiUser, FiCamera, FiMapPin, 
  FiRefreshCw, FiCheckCircle, FiXCircle, FiUsers, FiFilter, FiAlertTriangle
} from 'react-icons/fi';
import { LoadingCard, LoadingInline, LoadingOverlay } from '../../components/loading-spinner';

interface MandorInfo {
  mandorid: string;
  mandor_nama: string;
  pending_count: number;
}

interface PendingWorker {
  absenno: string;
  absen_id: number;
  tenagakerjaid: string;
  pekerja_nama: string;
  pekerja_nik: string;
  pekerja_gender: string;
  jenistenagakerja: string;
  absenmasuk: string;
  absen_time: string;
  absen_date_formatted: string;
  has_photo: boolean;
  has_location: boolean;
  fotoabsen?: string;
  lokasifotolat?: number;
  lokasifotolng?: number;
  keterangan: string;
  approval_status: string;
}

interface PendingByMandor {
  mandorid: string;
  mandor_nama: string;
  pending_count: number;
  workers: PendingWorker[];
}

interface AttendanceApprovalProps {
  routes: {
    pending_attendance: string;
    mandors_pending: string;
    approve_attendance: string;
    reject_attendance: string;
  };
  csrf_token: string;
  onSectionChange: (section: string) => void;
}

const AttendanceApproval: React.FC<AttendanceApprovalProps> = ({ 
  routes,
  csrf_token,
  onSectionChange 
}) => {
  const [pendingByMandor, setPendingByMandor] = useState<PendingByMandor[]>([]);
  const [mandorList, setMandorList] = useState<MandorInfo[]>([]);
  const [selectedMandor, setSelectedMandor] = useState<string>('');
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [isLoading, setIsLoading] = useState(true);
  const [isProcessing, setIsProcessing] = useState(false);
  
  // Selection states
  const [selectedItems, setSelectedItems] = useState<Set<string>>(new Set());
  
  // Photo viewer
  const [viewingPhoto, setViewingPhoto] = useState<string | null>(null);
  
  // Approval modal states
  const [showApprovalModal, setShowApprovalModal] = useState<{ 
    type: 'approve' | 'reject'; 
    items: PendingWorker[] 
  } | null>(null);
  const [approvalNotes, setApprovalNotes] = useState('');
  const [rejectionReason, setRejectionReason] = useState('');

  useEffect(() => {
    loadPendingAttendance();
  }, [selectedDate, selectedMandor]);

  const loadPendingAttendance = async () => {
    setIsLoading(true);
    try {
      const params = new URLSearchParams({
        date: selectedDate,
        ...(selectedMandor && { mandor_id: selectedMandor })
      });
      
      const response = await fetch(`${routes.pending_attendance}?${params}`);
      const data = await response.json();
      
      setPendingByMandor(data.pending_by_mandor || []);
      setMandorList(data.mandor_list || []);
      
      // Clear selections when data changes
      setSelectedItems(new Set());
    } catch (error) {
      console.error('Error loading pending attendance:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const getAllWorkers = (): PendingWorker[] => {
    return pendingByMandor.flatMap(mandor => mandor.workers);
  };

  const getSelectedWorkers = (): PendingWorker[] => {
    const allWorkers = getAllWorkers();
    return allWorkers.filter(worker => 
      selectedItems.has(`${worker.absenno}-${worker.absen_id}`)
    );
  };

  const handleItemSelection = (worker: PendingWorker, checked: boolean) => {
    const key = `${worker.absenno}-${worker.absen_id}`;
    const newSelected = new Set(selectedItems);
    
    if (checked) {
      newSelected.add(key);
    } else {
      newSelected.delete(key);
    }
    
    setSelectedItems(newSelected);
  };

  const handleSelectAll = (checked: boolean) => {
    if (checked) {
      const allKeys = getAllWorkers().map(worker => `${worker.absenno}-${worker.absen_id}`);
      setSelectedItems(new Set(allKeys));
    } else {
      setSelectedItems(new Set());
    }
  };

  const handleMandorSelectAll = (mandorId: string, checked: boolean) => {
    const mandorData = pendingByMandor.find(m => m.mandorid === mandorId);
    if (!mandorData) return;

    const newSelected = new Set(selectedItems);
    mandorData.workers.forEach(worker => {
      const key = `${worker.absenno}-${worker.absen_id}`;
      if (checked) {
        newSelected.add(key);
      } else {
        newSelected.delete(key);
      }
    });

    setSelectedItems(newSelected);
  };

  const handleBulkAction = (type: 'approve' | 'reject') => {
    const selectedWorkers = getSelectedWorkers();
    if (selectedWorkers.length === 0) {
      alert('Pilih minimal satu item untuk di' + (type === 'approve' ? 'approve' : 'reject'));
      return;
    }

    setShowApprovalModal({ type, items: selectedWorkers });
  };

  const handleSubmitApproval = async () => {
    if (!showApprovalModal) return;
    
    const { type, items } = showApprovalModal;
    
    // Validation
    if (type === 'reject' && !rejectionReason.trim()) {
      alert('Alasan penolakan harus diisi');
      return;
    }
    
    setIsProcessing(true);
    try {
      const url = type === 'approve' ? routes.approve_attendance : routes.reject_attendance;
      const payload = {
        items: items.map(item => ({
          absenno: item.absenno,
          absen_id: item.absen_id,
          tenagakerjaid: item.tenagakerjaid
        })),
        ...(type === 'approve' 
          ? { approval_notes: approvalNotes } 
          : { rejection_reason: rejectionReason }
        )
      };

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
        alert(result.message);
        // Refresh data
        await loadPendingAttendance();
        // Close modal and reset
        setShowApprovalModal(null);
        setApprovalNotes('');
        setRejectionReason('');
        setSelectedItems(new Set());
      } else {
        alert(result.error || result.message);
      }
    } catch (error) {
      console.error('Error processing approval:', error);
      alert('Terjadi kesalahan saat memproses approval');
    } finally {
      setIsProcessing(false);
    }
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const allWorkers = getAllWorkers();
  const selectedWorkers = getSelectedWorkers();
  const totalPending = allWorkers.length;

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
            <span className="text-sm font-medium">Kembali ke Dashboard</span>
          </button>
          
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-black mb-2">Approval Absensi</h1>
              <p className="text-gray-600">Approve atau reject absensi pekerja secara individual</p>
            </div>
            <button
              onClick={loadPendingAttendance}
              disabled={isLoading}
              className="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50"
            >
              <FiRefreshCw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span className="text-sm">Refresh</span>
            </button>
          </div>
        </div>

        {/* Filters */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
          <div className="flex items-center gap-4 mb-4">
            <FiFilter className="w-5 h-5 text-gray-500" />
            <h3 className="font-medium text-gray-900">Filter</h3>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {/* Date Filter */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                <FiCalendar className="w-4 h-4 inline mr-1" />
                Tanggal
              </label>
              <input
                type="date"
                value={selectedDate}
                onChange={(e) => setSelectedDate(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>

            {/* Mandor Filter */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                <FiUser className="w-4 h-4 inline mr-1" />
                Mandor
              </label>
              <select
                value={selectedMandor}
                onChange={(e) => setSelectedMandor(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Semua Mandor</option>
                {mandorList.map((mandor) => (
                  <option key={mandor.mandorid} value={mandor.mandorid}>
                    {mandor.mandor_nama} ({mandor.pending_count})
                  </option>
                ))}
              </select>
            </div>

            {/* Stats */}
            <div className="flex items-end">
              <div className="bg-orange-50 border border-orange-200 rounded-lg p-3 w-full">
                <div className="text-sm text-orange-600 mb-1">Total Pending</div>
                <div className="text-2xl font-bold text-orange-800">
                  {isLoading ? (
                    <div className="animate-pulse bg-orange-200 h-8 w-12 rounded"></div>
                  ) : (
                    totalPending
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Bulk Actions */}
        {selectedItems.size > 0 && (
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                <FiCheckCircle className="w-5 h-5 text-blue-600" />
                <span className="text-blue-800 font-medium">
                  {selectedItems.size} item terpilih
                </span>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => handleBulkAction('approve')}
                  className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                >
                  <FiCheck className="w-4 h-4" />
                  <span className="text-sm">Approve Selected</span>
                </button>
                <button
                  onClick={() => handleBulkAction('reject')}
                  className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                  <FiX className="w-4 h-4" />
                  <span className="text-sm">Reject Selected</span>
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Pending Records */}
        <div className="space-y-6">
          {isLoading ? (
            <LoadingCard text="Memuat data pending approval..." />
          ) : pendingByMandor.length > 0 ? (
            pendingByMandor.map((mandorData) => (
              <div key={mandorData.mandorid} className="bg-white rounded-lg shadow-sm border border-gray-200">
                {/* Mandor Header */}
                <div className="border-b border-gray-200 p-4">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <input
                        type="checkbox"
                        checked={mandorData.workers.every(worker => 
                          selectedItems.has(`${worker.absenno}-${worker.absen_id}`)
                        )}
                        onChange={(e) => handleMandorSelectAll(mandorData.mandorid, e.target.checked)}
                        className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      />
                      <FiUser className="w-5 h-5 text-blue-600" />
                      <div>
                        <h3 className="font-semibold text-black">{mandorData.mandor_nama}</h3>
                        <p className="text-sm text-gray-500">
                          {mandorData.pending_count} pekerja menunggu approval
                        </p>
                      </div>
                    </div>
                    <span className="px-3 py-1 bg-orange-100 text-orange-800 text-sm rounded-full">
                      {mandorData.pending_count} Pending
                    </span>
                  </div>
                </div>

                {/* Workers List */}
                <div className="divide-y divide-gray-100">
                  {mandorData.workers.map((worker) => {
                    const isSelected = selectedItems.has(`${worker.absenno}-${worker.absen_id}`);
                    
                    return (
                      <div
                        key={`${worker.absenno}-${worker.absen_id}`}
                        className={`p-4 transition-colors ${
                          isSelected ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50'
                        }`}
                      >
                        <div className="flex items-center gap-4">
                          <input
                            type="checkbox"
                            checked={isSelected}
                            onChange={(e) => handleItemSelection(worker, e.target.checked)}
                            className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                          />
                          
                          <div className="flex-1">
                            <div className="flex items-center gap-2 mb-1">
                              <h4 className="font-medium text-black">{worker.pekerja_nama}</h4>
                              <span className="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                {worker.approval_status}
                              </span>
                            </div>
                            
                            <div className="flex items-center gap-4 text-sm text-gray-600">
                              <span>{worker.pekerja_nik}</span>
                              <span>{worker.pekerja_gender}</span>
                              <span>{worker.jenistenagakerja}</span>
                              <div className="flex items-center gap-1">
                                <FiClock className="w-3 h-3" />
                                <span>{worker.absen_time}</span>
                              </div>
                              {worker.has_photo && (
                                <div className="flex items-center gap-1 text-green-600">
                                  <FiCamera className="w-3 h-3" />
                                  <span>Foto</span>
                                </div>
                              )}
                              {worker.has_location && (
                                <div className="flex items-center gap-1 text-blue-600">
                                  <FiMapPin className="w-3 h-3" />
                                  <span>Lokasi</span>
                                </div>
                              )}
                            </div>
                          </div>
                          
                          <div className="flex items-center gap-2">
                            {worker.has_photo && (
                              <button
                                onClick={() => setViewingPhoto(worker.fotoabsen!)}
                                className="p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors"
                              >
                                <FiEye className="w-4 h-4" />
                              </button>
                            )}
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            ))
          ) : (
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
              <FiCheckCircle className="w-12 h-12 text-green-500 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">Tidak ada pending approval</h3>
              <p className="text-gray-500">
                Semua absensi untuk {formatDate(selectedDate)} sudah diproses
                {selectedMandor && ` untuk mandor yang dipilih`}
              </p>
            </div>
          )}
        </div>

        {/* Select All Checkbox - Fixed Position */}
        {totalPending > 0 && (
          <div className="fixed bottom-6 right-6 bg-white rounded-lg shadow-lg border border-gray-200 p-4">
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={selectedItems.size === totalPending}
                onChange={(e) => handleSelectAll(e.target.checked)}
                className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span className="text-sm font-medium">Pilih Semua ({totalPending})</span>
            </label>
          </div>
        )}

        {/* Approval/Rejection Modal */}
        {showApprovalModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div className="bg-white rounded-lg max-w-md w-full mx-4 p-6">
              <div className="mb-4">
                <h3 className="text-xl font-semibold flex items-center gap-2">
                  {showApprovalModal.type === 'approve' ? (
                    <>
                      <FiCheckCircle className="w-6 h-6 text-green-600" />
                      Approve Absensi
                    </>
                  ) : (
                    <>
                      <FiXCircle className="w-6 h-6 text-red-600" />
                      Reject Absensi
                    </>
                  )}
                </h3>
                <p className="text-sm text-gray-500 mt-2">
                  {showApprovalModal.items.length} item akan di{showApprovalModal.type === 'approve' ? 'approve' : 'reject'}
                </p>
              </div>

              {/* Items Preview */}
              <div className="mb-4 max-h-32 overflow-y-auto bg-gray-50 rounded-lg p-3">
                {showApprovalModal.items.map((item, index) => (
                  <div key={`${item.absenno}-${item.absen_id}`} className="text-sm text-gray-600">
                    {index + 1}. {item.pekerja_nama} ({item.absen_time})
                  </div>
                ))}
              </div>

              {showApprovalModal.type === 'approve' ? (
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Catatan Approval (opsional)
                  </label>
                  <textarea
                    value={approvalNotes}
                    onChange={(e) => setApprovalNotes(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    rows={3}
                    placeholder="Tambahkan catatan jika diperlukan..."
                  />
                </div>
              ) : (
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Penolakan *
                  </label>
                  <textarea
                    value={rejectionReason}
                    onChange={(e) => setRejectionReason(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    rows={3}
                    placeholder="Jelaskan alasan penolakan..."
                    required
                  />
                </div>
              )}

              <div className="flex gap-3">
                <button
                  onClick={() => setShowApprovalModal(null)}
                  className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Batal
                </button>
                <button
                  onClick={handleSubmitApproval}
                  disabled={showApprovalModal.type === 'reject' && !rejectionReason.trim()}
                  className={`flex-1 px-4 py-2 text-white rounded-lg transition-colors ${
                    showApprovalModal.type === 'approve'
                      ? 'bg-green-600 hover:bg-green-700 disabled:bg-green-400'
                      : 'bg-red-600 hover:bg-red-700 disabled:bg-red-400'
                  }`}
                >
                  {showApprovalModal.type === 'approve' ? 'Approve' : 'Reject'}
                </button>
              </div>
            </div>
          </div>
        )}

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

        {/* Processing Overlay */}
        {isProcessing && (
          <LoadingOverlay text="Memproses approval..." />
        )}
      </div>
    </div>
  );
};

export default AttendanceApproval;