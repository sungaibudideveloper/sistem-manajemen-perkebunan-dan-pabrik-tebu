// resources/js/pages/approver/attendance-approval.tsx

import React, { useState, useEffect } from 'react';
import {
  FiArrowLeft, FiClock, FiCheck, FiX, FiEye, FiCalendar, FiUser, FiCamera, FiMapPin, FiRefreshCw
} from 'react-icons/fi';
import { LoadingCard, LoadingInline, LoadingOverlay } from '../../components/loading-spinner';

interface PendingAttendance {
  absenno: string;
  mandorid: string;
  mandor_nama: string;
  totalpekerja: number;
  status: string;
  uploaddate: string;
  updateBy: string;
  upload_time: string;
  upload_date_formatted: string;
  worker_count: number;
  has_photos: boolean;
  has_location: boolean;
}

interface WorkerDetail {
  id: number;
  tenagakerjaid: string;
  nama: string;
  nik: string;
  gender: string;
  jenistenagakerja: string;
  absenmasuk: string;
  absen_time: string;
  keterangan: string;
  has_photo: boolean;
  fotoabsen?: string;
  has_location: boolean;
  lokasifotolat?: number;
  lokasifotolng?: number;
  createdat: string;
}

interface AttendanceDetail {
  header: {
    absenno: string;
    mandorid: string;
    mandor_nama: string;
    totalpekerja: number;
    status: string;
    uploaddate: string;
    upload_date_formatted: string;
    updateBy: string;
  };
  workers: WorkerDetail[];
  summary: {
    total_workers: number;
    with_photos: number;
    with_location: number;
    earliest_checkin: string;
    latest_checkin: string;
  };
}

interface AttendanceApprovalProps {
  routes: {
    pending_attendance: string;
    attendance_detail: string;
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
  const [pendingRecords, setPendingRecords] = useState<PendingAttendance[]>([]);
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [isLoading, setIsLoading] = useState(true);
  const [isProcessing, setIsProcessing] = useState(false);
  
  // Detail modal states
  const [selectedRecord, setSelectedRecord] = useState<string | null>(null);
  const [attendanceDetail, setAttendanceDetail] = useState<AttendanceDetail | null>(null);
  const [isDetailLoading, setIsDetailLoading] = useState(false);
  
  // Photo viewer
  const [viewingPhoto, setViewingPhoto] = useState<string | null>(null);
  
  // Approval modal states
  const [showApprovalModal, setShowApprovalModal] = useState<{ type: 'approve' | 'reject'; absenno: string } | null>(null);
  const [approvalNotes, setApprovalNotes] = useState('');
  const [rejectionReason, setRejectionReason] = useState('');

  useEffect(() => {
    loadPendingAttendance();
  }, [selectedDate]);

  const loadPendingAttendance = async () => {
    setIsLoading(true);
    try {
      const response = await fetch(`${routes.pending_attendance}?date=${selectedDate}`);
      const data = await response.json();
      setPendingRecords(data.pending_attendance || []);
    } catch (error) {
      console.error('Error loading pending attendance:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const loadAttendanceDetail = async (absenno: string) => {
    setIsDetailLoading(true);
    try {
      const url = routes.attendance_detail.replace('__ABSENNO__', absenno);
      const response = await fetch(url);
      const data = await response.json();
      setAttendanceDetail(data);
    } catch (error) {
      console.error('Error loading attendance detail:', error);
    } finally {
      setIsDetailLoading(false);
    }
  };

  const handleViewDetail = (absenno: string) => {
    setSelectedRecord(absenno);
    loadAttendanceDetail(absenno);
  };

  const handleCloseDetail = () => {
    setSelectedRecord(null);
    setAttendanceDetail(null);
  };

  const handleApproval = async (type: 'approve' | 'reject') => {
    if (!showApprovalModal) return;
    
    setIsProcessing(true);
    try {
      const url = type === 'approve' ? routes.approve_attendance : routes.reject_attendance;
      const body = type === 'approve' 
        ? { 
            absenno: showApprovalModal.absenno,
            approval_notes: approvalNotes 
          }
        : { 
            absenno: showApprovalModal.absenno,
            rejection_reason: rejectionReason 
          };

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf_token
        },
        body: JSON.stringify(body)
      });

      const result = await response.json();

      if (result.success) {
        alert(result.message);
        // Refresh data
        await loadPendingAttendance();
        // Close modals
        setShowApprovalModal(null);
        setSelectedRecord(null);
        setApprovalNotes('');
        setRejectionReason('');
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
              <h1 className="text-3xl font-bold text-black mb-2">Pending Approval</h1>
              <p className="text-gray-600">Daftar absensi yang menunggu persetujuan</p>
            </div>
            <button
              onClick={loadPendingAttendance}
              className="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors"
            >
              <FiRefreshCw className="w-4 h-4" />
              <span className="text-sm">Refresh</span>
            </button>
          </div>
          
          {/* Date Filter */}
          <div className="mt-6 p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-4">
                <FiCalendar className="w-5 h-5 text-gray-500" />
                <input
                  type="date"
                  value={selectedDate}
                  onChange={(e) => setSelectedDate(e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span className="text-sm text-gray-600">
                  {formatDate(selectedDate)}
                </span>
              </div>
              <div className="text-right">
                <div className="text-2xl font-bold text-orange-600">
                  {isLoading ? (
                    <div className="animate-pulse bg-gray-200 h-8 w-12 rounded"></div>
                  ) : (
                    pendingRecords.length
                  )}
                </div>
                <div className="text-sm text-gray-500">Pending</div>
              </div>
            </div>
          </div>
        </div>

        {/* Pending Records */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="border-b border-gray-200 p-6">
            <div className="flex items-center gap-3">
              <FiClock className="w-5 h-5 text-orange-500" />
              <h2 className="text-xl font-semibold text-black">
                Menunggu Approval {isLoading ? '' : `(${pendingRecords.length})`}
              </h2>
              {isLoading && <LoadingInline color="orange" />}
            </div>
          </div>
          
          <div className="max-h-96 overflow-y-auto">
            {isLoading ? (
              <LoadingCard text="Memuat data pending approval..." />
            ) : pendingRecords.length > 0 ? (
              <div className="divide-y divide-gray-100">
                {pendingRecords.map((record) => (
                  <div key={record.absenno} className="p-6 hover:bg-gray-50 transition-colors">
                    <div className="flex items-center justify-between">
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <h3 className="font-semibold text-black">{record.absenno}</h3>
                          <span className="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full">
                            {record.status}
                          </span>
                        </div>
                        
                        <div className="flex items-center gap-6 text-sm text-gray-600">
                          <div className="flex items-center gap-2">
                            <FiUser className="w-4 h-4" />
                            <span>{record.mandor_nama}</span>
                          </div>
                          <div className="flex items-center gap-2">
                            <FiClock className="w-4 h-4" />
                            <span>{record.upload_time}</span>
                          </div>
                          <div>
                            <span className="font-medium">{record.totalpekerja}</span> pekerja
                          </div>
                          {record.has_photos && (
                            <div className="flex items-center gap-1 text-green-600">
                              <FiCamera className="w-4 h-4" />
                              <span className="text-xs">Foto</span>
                            </div>
                          )}
                          {record.has_location && (
                            <div className="flex items-center gap-1 text-blue-600">
                              <FiMapPin className="w-4 h-4" />
                              <span className="text-xs">Lokasi</span>
                            </div>
                          )}
                        </div>
                      </div>
                      
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => handleViewDetail(record.absenno)}
                          className="flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                        >
                          <FiEye className="w-4 h-4" />
                          <span className="text-sm">Detail</span>
                        </button>
                        <button
                          onClick={() => setShowApprovalModal({ type: 'approve', absenno: record.absenno })}
                          className="flex items-center gap-2 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        >
                          <FiCheck className="w-4 h-4" />
                          <span className="text-sm">Approve</span>
                        </button>
                        <button
                          onClick={() => setShowApprovalModal({ type: 'reject', absenno: record.absenno })}
                          className="flex items-center gap-2 px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        >
                          <FiX className="w-4 h-4" />
                          <span className="text-sm">Reject</span>
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="p-8 text-center">
                <FiCheck className="w-12 h-12 text-green-500 mx-auto mb-4" />
                <p className="text-gray-500">Tidak ada absensi yang menunggu approval</p>
                <p className="text-xs text-gray-400 mt-1">untuk {formatDate(selectedDate)}</p>
              </div>
            )}
          </div>
        </div>

        {/* Detail Modal */}
        {selectedRecord && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div className="bg-white rounded-lg max-w-4xl max-h-[90vh] overflow-hidden w-full mx-4">
              <div className="border-b border-gray-200 p-6">
                <div className="flex items-center justify-between">
                  <h3 className="text-xl font-semibold">Detail Absensi - {selectedRecord}</h3>
                  <button
                    onClick={handleCloseDetail}
                    className="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                  >
                    <FiX className="w-5 h-5" />
                  </button>
                </div>
              </div>
              
              <div className="p-6 max-h-96 overflow-y-auto">
                {isDetailLoading ? (
                  <LoadingCard text="Memuat detail absensi..." />
                ) : attendanceDetail ? (
                  <div className="space-y-6">
                    {/* Header Info */}
                    <div className="bg-gray-50 p-4 rounded-lg">
                      <div className="grid grid-cols-2 gap-4">
                        <div>
                          <span className="text-sm text-gray-500">Mandor:</span>
                          <p className="font-medium">{attendanceDetail.header.mandor_nama}</p>
                        </div>
                        <div>
                          <span className="text-sm text-gray-500">Total Pekerja:</span>
                          <p className="font-medium">{attendanceDetail.header.totalpekerja}</p>
                        </div>
                        <div>
                          <span className="text-sm text-gray-500">Upload:</span>
                          <p className="font-medium">{attendanceDetail.header.upload_date_formatted}</p>
                        </div>
                        <div>
                          <span className="text-sm text-gray-500">Status:</span>
                          <span className="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full">
                            {attendanceDetail.header.status}
                          </span>
                        </div>
                      </div>
                    </div>

                    {/* Workers List */}
                    <div>
                      <h4 className="font-medium mb-4">Daftar Pekerja ({attendanceDetail.workers.length})</h4>
                      <div className="space-y-3">
                        {attendanceDetail.workers.map((worker) => (
                          <div key={worker.id} className="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div className="flex-1">
                              <h5 className="font-medium">{worker.nama}</h5>
                              <p className="text-sm text-gray-500">{worker.nik} • {worker.jenistenagakerja}</p>
                              <p className="text-xs text-gray-400">Absen: {worker.absen_time}</p>
                            </div>
                            <div className="flex items-center gap-2">
                              {worker.has_photo && (
                                <button
                                  onClick={() => setViewingPhoto(worker.fotoabsen!)}
                                  className="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                >
                                  <FiCamera className="w-4 h-4" />
                                </button>
                              )}
                              {worker.has_location && (
                                <div className="p-2 bg-blue-100 text-blue-600 rounded-lg">
                                  <FiMapPin className="w-4 h-4" />
                                </div>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                ) : (
                  <p className="text-center text-gray-500">Gagal memuat detail</p>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Approval/Rejection Modal */}
        {showApprovalModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div className="bg-white rounded-lg max-w-md w-full mx-4 p-6">
              <div className="mb-4">
                <h3 className="text-xl font-semibold">
                  {showApprovalModal.type === 'approve' ? 'Approve Absensi' : 'Reject Absensi'}
                </h3>
                <p className="text-sm text-gray-500 mt-1">
                  {showApprovalModal.absenno}
                </p>
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
                  onClick={() => handleApproval(showApprovalModal.type)}
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