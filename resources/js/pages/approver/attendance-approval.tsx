// resources/js/pages/approver/attendance-approval.tsx - WITH TABS

import React, { useState, useEffect } from 'react';
import {
  FiArrowLeft, FiClock, FiCheck, FiX, FiEye, FiCalendar, FiUser, FiCamera, FiMapPin, 
  FiRefreshCw, FiCheckCircle, FiXCircle, FiUsers, FiFilter, FiAlertTriangle, FiHome
} from 'react-icons/fi';

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
  absentype: 'HADIR' | 'LOKASI';
  checkintime?: string;
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
  
  // NEW: Tab state
  const [activeTab, setActiveTab] = useState<'HADIR' | 'LOKASI'>('HADIR');
  
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

  // NEW: Filter by active tab
  const getFilteredMandorData = (): PendingByMandor[] => {
    return pendingByMandor.map(mandor => ({
      ...mandor,
      workers: mandor.workers.filter(worker => worker.absentype === activeTab),
      pending_count: mandor.workers.filter(worker => worker.absentype === activeTab).length
    })).filter(mandor => mandor.pending_count > 0);
  };

  const getAllWorkers = (): PendingWorker[] => {
    return getFilteredMandorData().flatMap(mandor => mandor.workers);
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
    const mandorData = getFilteredMandorData().find(m => m.mandorid === mandorId);
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
        await loadPendingAttendance();
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

  // NEW: Get counts per tab
  const hadirCount = pendingByMandor.flatMap(m => m.workers).filter(w => w.absentype === 'HADIR').length;
  const lokasiCount = pendingByMandor.flatMap(m => m.workers).filter(w => w.absentype === 'LOKASI').length;

  const allWorkers = getAllWorkers();
  const totalPending = allWorkers.length;
  const filteredMandorData = getFilteredMandorData();

  return (
    <div style={{ minHeight: '100vh', backgroundColor: '#f9fafb' }}>
      <div style={{ maxWidth: '1280px', margin: '0 auto', padding: '32px 24px' }}>
        {/* Header */}
        <div style={{ marginBottom: '32px' }}>
          <button
            onClick={() => onSectionChange('dashboard')}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
              color: '#6b7280',
              background: 'none',
              border: 'none',
              cursor: 'pointer',
              fontSize: '14px',
              fontWeight: '500',
              marginBottom: '16px'
            }}
          >
            <FiArrowLeft style={{ width: '16px', height: '16px' }} />
            Kembali ke Dashboard
          </button>
          
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div>
              <h1 style={{ fontSize: '30px', fontWeight: 'bold', color: 'black', marginBottom: '8px' }}>
                Approval Absensi
              </h1>
              <p style={{ color: '#6b7280' }}>Approve atau reject absensi HADIR dan LOKASI</p>
            </div>
            <button
              onClick={loadPendingAttendance}
              disabled={isLoading}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                padding: '8px 16px',
                backgroundColor: 'black',
                color: 'white',
                border: 'none',
                borderRadius: '8px',
                cursor: isLoading ? 'not-allowed' : 'pointer',
                fontSize: '14px',
                opacity: isLoading ? 0.5 : 1
              }}
            >
              <FiRefreshCw style={{ width: '16px', height: '16px' }} className={isLoading ? 'animate-spin' : ''} />
              Refresh
            </button>
          </div>
        </div>

        {/* Filters */}
        <div style={{ backgroundColor: 'white', borderRadius: '8px', boxShadow: '0 1px 3px rgba(0,0,0,0.1)', border: '1px solid #e5e7eb', padding: '24px', marginBottom: '24px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '16px', marginBottom: '16px' }}>
            <FiFilter style={{ width: '20px', height: '20px', color: '#6b7280' }} />
            <h3 style={{ fontWeight: '500', color: '#111827', margin: 0 }}>Filter</h3>
          </div>
          
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: '16px' }}>
            {/* Date Filter */}
            <div>
              <label style={{ display: 'block', fontSize: '14px', fontWeight: '500', color: '#374151', marginBottom: '8px' }}>
                <FiCalendar style={{ width: '16px', height: '16px', display: 'inline', marginRight: '4px' }} />
                Tanggal
              </label>
              <input
                type="date"
                value={selectedDate}
                onChange={(e) => setSelectedDate(e.target.value)}
                style={{
                  width: '100%',
                  padding: '8px 12px',
                  border: '1px solid #d1d5db',
                  borderRadius: '8px',
                  outline: 'none'
                }}
              />
            </div>

            {/* Mandor Filter */}
            <div>
              <label style={{ display: 'block', fontSize: '14px', fontWeight: '500', color: '#374151', marginBottom: '8px' }}>
                <FiUser style={{ width: '16px', height: '16px', display: 'inline', marginRight: '4px' }} />
                Mandor
              </label>
              <select
                value={selectedMandor}
                onChange={(e) => setSelectedMandor(e.target.value)}
                style={{
                  width: '100%',
                  padding: '8px 12px',
                  border: '1px solid #d1d5db',
                  borderRadius: '8px',
                  outline: 'none'
                }}
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
            <div style={{ display: 'flex', alignItems: 'end' }}>
              <div style={{ backgroundColor: '#fef3c7', border: '1px solid #fcd34d', borderRadius: '8px', padding: '12px', width: '100%' }}>
                <div style={{ fontSize: '14px', color: '#92400e', marginBottom: '4px' }}>Total Pending</div>
                <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#78350f' }}>
                  {isLoading ? '...' : (hadirCount + lokasiCount)}
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* NEW: Tabs */}
        <div style={{ display: 'flex', gap: '8px', marginBottom: '24px', borderBottom: '2px solid #e5e7eb' }}>
          <button
            onClick={() => {
              setActiveTab('HADIR');
              setSelectedItems(new Set());
            }}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
              padding: '12px 24px',
              backgroundColor: 'transparent',
              color: activeTab === 'HADIR' ? '#2563eb' : '#6b7280',
              border: 'none',
              borderBottom: activeTab === 'HADIR' ? '3px solid #2563eb' : '3px solid transparent',
              cursor: 'pointer',
              fontSize: '16px',
              fontWeight: '600',
              transition: 'all 0.2s'
            }}
          >
            <FiHome style={{ width: '20px', height: '20px' }} />
            HADIR
            <span style={{
              padding: '2px 8px',
              backgroundColor: activeTab === 'HADIR' ? '#dbeafe' : '#f3f4f6',
              color: activeTab === 'HADIR' ? '#1e40af' : '#6b7280',
              borderRadius: '9999px',
              fontSize: '12px',
              fontWeight: '600'
            }}>
              {hadirCount}
            </span>
          </button>
          
          <button
            onClick={() => {
              setActiveTab('LOKASI');
              setSelectedItems(new Set());
            }}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
              padding: '12px 24px',
              backgroundColor: 'transparent',
              color: activeTab === 'LOKASI' ? '#7c3aed' : '#6b7280',
              border: 'none',
              borderBottom: activeTab === 'LOKASI' ? '3px solid #7c3aed' : '3px solid transparent',
              cursor: 'pointer',
              fontSize: '16px',
              fontWeight: '600',
              transition: 'all 0.2s'
            }}
          >
            <FiMapPin style={{ width: '20px', height: '20px' }} />
            LOKASI
            <span style={{
              padding: '2px 8px',
              backgroundColor: activeTab === 'LOKASI' ? '#f3e8ff' : '#f3f4f6',
              color: activeTab === 'LOKASI' ? '#6b21a8' : '#6b7280',
              borderRadius: '9999px',
              fontSize: '12px',
              fontWeight: '600'
            }}>
              {lokasiCount}
            </span>
          </button>
        </div>

        {/* Bulk Actions */}
        {selectedItems.size > 0 && (
          <div style={{ backgroundColor: '#dbeafe', border: '1px solid #93c5fd', borderRadius: '8px', padding: '16px', marginBottom: '24px' }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <FiCheckCircle style={{ width: '20px', height: '20px', color: '#2563eb' }} />
                <span style={{ color: '#1e40af', fontWeight: '500' }}>
                  {selectedItems.size} item terpilih
                </span>
              </div>
              <div style={{ display: 'flex', gap: '8px' }}>
                <button
                  onClick={() => handleBulkAction('approve')}
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px',
                    padding: '8px 16px',
                    backgroundColor: '#16a34a',
                    color: 'white',
                    border: 'none',
                    borderRadius: '8px',
                    cursor: 'pointer',
                    fontSize: '14px'
                  }}
                >
                  <FiCheck style={{ width: '16px', height: '16px' }} />
                  Approve Selected
                </button>
                <button
                  onClick={() => handleBulkAction('reject')}
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px',
                    padding: '8px 16px',
                    backgroundColor: '#dc2626',
                    color: 'white',
                    border: 'none',
                    borderRadius: '8px',
                    cursor: 'pointer',
                    fontSize: '14px'
                  }}
                >
                  <FiX style={{ width: '16px', height: '16px' }} />
                  Reject Selected
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Pending Records */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}>
          {isLoading ? (
            <div style={{ backgroundColor: 'white', borderRadius: '8px', padding: '32px', textAlign: 'center' }}>
              <div style={{
                width: '40px',
                height: '40px',
                border: '4px solid #e5e7eb',
                borderTopColor: '#2563eb',
                borderRadius: '50%',
                animation: 'spin 1s linear infinite',
                margin: '0 auto'
              }} />
              <p style={{ marginTop: '16px', color: '#6b7280' }}>Memuat data pending approval...</p>
            </div>
          ) : filteredMandorData.length > 0 ? (
            filteredMandorData.map((mandorData) => (
              <div key={mandorData.mandorid} style={{ backgroundColor: 'white', borderRadius: '8px', boxShadow: '0 1px 3px rgba(0,0,0,0.1)', border: '1px solid #e5e7eb' }}>
                {/* Mandor Header */}
                <div style={{ borderBottom: '1px solid #e5e7eb', padding: '16px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                      <input
                        type="checkbox"
                        checked={mandorData.workers.every(worker => 
                          selectedItems.has(`${worker.absenno}-${worker.absen_id}`)
                        )}
                        onChange={(e) => handleMandorSelectAll(mandorData.mandorid, e.target.checked)}
                        style={{ width: '16px', height: '16px', cursor: 'pointer' }}
                      />
                      <FiUser style={{ width: '20px', height: '20px', color: '#2563eb' }} />
                      <div>
                        <h3 style={{ fontWeight: '600', color: 'black', margin: 0 }}>{mandorData.mandor_nama}</h3>
                        <p style={{ fontSize: '14px', color: '#6b7280', margin: '4px 0 0 0' }}>
                          {mandorData.pending_count} {activeTab} menunggu approval
                        </p>
                      </div>
                    </div>
                    <span style={{
                      padding: '4px 12px',
                      backgroundColor: '#fef3c7',
                      color: '#92400e',
                      fontSize: '14px',
                      borderRadius: '9999px'
                    }}>
                      {mandorData.pending_count} Pending
                    </span>
                  </div>
                </div>

                {/* Workers List */}
                <div>
                  {mandorData.workers.map((worker) => {
                    const isSelected = selectedItems.has(`${worker.absenno}-${worker.absen_id}`);
                    
                    return (
                      <div
                        key={`${worker.absenno}-${worker.absen_id}`}
                        style={{
                          padding: '16px',
                          borderBottom: '1px solid #f3f4f6',
                          backgroundColor: isSelected ? '#eff6ff' : 'transparent',
                          borderLeft: isSelected ? '4px solid #2563eb' : 'none',
                          transition: 'background-color 0.2s'
                        }}
                      >
                        <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                          <input
                            type="checkbox"
                            checked={isSelected}
                            onChange={(e) => handleItemSelection(worker, e.target.checked)}
                            style={{ width: '16px', height: '16px', cursor: 'pointer' }}
                          />
                          
                          <div style={{ flex: 1 }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '4px' }}>
                              <h4 style={{ fontWeight: '500', color: 'black', margin: 0 }}>{worker.pekerja_nama}</h4>
                              <span style={{
                                padding: '2px 8px',
                                backgroundColor: '#fef3c7',
                                color: '#92400e',
                                fontSize: '12px',
                                borderRadius: '9999px'
                              }}>
                                {worker.approval_status}
                              </span>
                            </div>
                            
                            <div style={{ display: 'flex', alignItems: 'center', gap: '16px', fontSize: '14px', color: '#6b7280' }}>
                              <span>{worker.pekerja_nik}</span>
                              <span>{worker.pekerja_gender}</span>
                              <span>{worker.jenistenagakerja}</span>
                              <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
                                <FiClock style={{ width: '12px', height: '12px' }} />
                                <span>{worker.absen_time}</span>
                              </div>
                              {worker.checkintime && (
                                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', color: '#7c3aed' }}>
                                  <FiMapPin style={{ width: '12px', height: '12px' }} />
                                  <span>Checkin: {new Date(worker.checkintime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</span>
                                </div>
                              )}
                              {worker.has_photo && (
                                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', color: '#16a34a' }}>
                                  <FiCamera style={{ width: '12px', height: '12px' }} />
                                  <span>Foto</span>
                                </div>
                              )}
                              {worker.has_location && (
                                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', color: '#2563eb' }}>
                                  <FiMapPin style={{ width: '12px', height: '12px' }} />
                                  <span>GPS</span>
                                </div>
                              )}
                            </div>
                          </div>
                          
                          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                            {worker.has_photo && (
                              <button
                                onClick={() => setViewingPhoto(worker.fotoabsen!)}
                                style={{
                                  padding: '8px',
                                  backgroundColor: '#f3f4f6',
                                  color: '#6b7280',
                                  border: 'none',
                                  borderRadius: '8px',
                                  cursor: 'pointer'
                                }}
                              >
                                <FiEye style={{ width: '16px', height: '16px' }} />
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
            <div style={{ backgroundColor: 'white', borderRadius: '8px', boxShadow: '0 1px 3px rgba(0,0,0,0.1)', border: '1px solid #e5e7eb', padding: '32px', textAlign: 'center' }}>
              <FiCheckCircle style={{ width: '48px', height: '48px', color: '#10b981', margin: '0 auto 16px' }} />
              <h3 style={{ fontSize: '18px', fontWeight: '500', color: '#111827', marginBottom: '8px' }}>
                Tidak ada pending {activeTab}
              </h3>
              <p style={{ color: '#6b7280' }}>
                Semua absensi {activeTab} untuk {formatDate(selectedDate)} sudah diproses
              </p>
            </div>
          )}
        </div>

        {/* Select All Checkbox */}
        {totalPending > 0 && (
          <div style={{
            position: 'fixed',
            bottom: '24px',
            right: '24px',
            backgroundColor: 'white',
            borderRadius: '8px',
            boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
            border: '1px solid #e5e7eb',
            padding: '16px'
          }}>
            <label style={{ display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' }}>
              <input
                type="checkbox"
                checked={selectedItems.size === totalPending}
                onChange={(e) => handleSelectAll(e.target.checked)}
                style={{ width: '16px', height: '16px', cursor: 'pointer' }}
              />
              <span style={{ fontSize: '14px', fontWeight: '500' }}>Pilih Semua ({totalPending})</span>
            </label>
          </div>
        )}

        {/* Approval/Rejection Modal */}
        {showApprovalModal && (
          <div style={{
            position: 'fixed',
            inset: 0,
            zIndex: 50,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            backgroundColor: 'rgba(0,0,0,0.5)'
          }}>
            <div style={{
              backgroundColor: 'white',
              borderRadius: '8px',
              maxWidth: '500px',
              width: '90%',
              padding: '24px'
            }}>
              <div style={{ marginBottom: '16px' }}>
                <h3 style={{ fontSize: '20px', fontWeight: '600', display: 'flex', alignItems: 'center', gap: '8px' }}>
                  {showApprovalModal.type === 'approve' ? (
                    <>
                      <FiCheckCircle style={{ width: '24px', height: '24px', color: '#16a34a' }} />
                      Approve Absensi
                    </>
                  ) : (
                    <>
                      <FiXCircle style={{ width: '24px', height: '24px', color: '#dc2626' }} />
                      Reject Absensi
                    </>
                  )}
                </h3>
                <p style={{ fontSize: '14px', color: '#6b7280', marginTop: '8px' }}>
                  {showApprovalModal.items.length} item akan di{showApprovalModal.type === 'approve' ? 'approve' : 'reject'}
                </p>
              </div>

              {/* Items Preview */}
              <div style={{ marginBottom: '16px', maxHeight: '128px', overflowY: 'auto', backgroundColor: '#f9fafb', borderRadius: '8px', padding: '12px' }}>
                {showApprovalModal.items.map((item, index) => (
                  <div key={`${item.absenno}-${item.absen_id}`} style={{ fontSize: '14px', color: '#6b7280' }}>
                    {index + 1}. {item.pekerja_nama} ({item.absen_time})
                  </div>
                ))}
              </div>

              {showApprovalModal.type === 'approve' ? (
                <div style={{ marginBottom: '16px' }}>
                  <label style={{ display: 'block', fontSize: '14px', fontWeight: '500', color: '#374151', marginBottom: '8px' }}>
                    Catatan Approval (opsional)
                  </label>
                  <textarea
                    value={approvalNotes}
                    onChange={(e) => setApprovalNotes(e.target.value)}
                    style={{
                      width: '100%',
                      padding: '8px 12px',
                      border: '1px solid #d1d5db',
                      borderRadius: '8px',
                      outline: 'none',
                      resize: 'vertical'
                    }}
                    rows={3}
                    placeholder="Tambahkan catatan jika diperlukan..."
                  />
                </div>
              ) : (
                <div style={{ marginBottom: '16px' }}>
                  <label style={{ display: 'block', fontSize: '14px', fontWeight: '500', color: '#374151', marginBottom: '8px' }}>
                    Alasan Penolakan *
                  </label>
                  <textarea
                    value={rejectionReason}
                    onChange={(e) => setRejectionReason(e.target.value)}
                    style={{
                      width: '100%',
                      padding: '8px 12px',
                      border: '1px solid #d1d5db',
                      borderRadius: '8px',
                      outline: 'none',
                      resize: 'vertical'
                    }}
                    rows={3}
                    placeholder="Jelaskan alasan penolakan..."
                    required
                  />
                </div>
              )}

              <div style={{ display: 'flex', gap: '12px' }}>
                <button
                  onClick={() => setShowApprovalModal(null)}
                  style={{
                    flex: 1,
                    padding: '8px 16px',
                    border: '1px solid #d1d5db',
                    color: '#374151',
                    backgroundColor: 'white',
                    borderRadius: '8px',
                    cursor: 'pointer'
                  }}
                >
                  Batal
                </button>
                <button
                  onClick={handleSubmitApproval}
                  disabled={showApprovalModal.type === 'reject' && !rejectionReason.trim()}
                  style={{
                    flex: 1,
                    padding: '8px 16px',
                    color: 'white',
                    backgroundColor: showApprovalModal.type === 'approve' ? '#16a34a' : '#dc2626',
                    border: 'none',
                    borderRadius: '8px',
                    cursor: 'pointer',
                    opacity: (showApprovalModal.type === 'reject' && !rejectionReason.trim()) ? 0.5 : 1
                  }}
                >
                  {showApprovalModal.type === 'approve' ? 'Approve' : 'Reject'}
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Photo Viewer */}
        {viewingPhoto && (
          <div style={{
            position: 'fixed',
            inset: 0,
            zIndex: 50,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            backgroundColor: 'rgba(0,0,0,0.9)'
          }}>
            <div style={{ position: 'relative', maxWidth: '1024px', maxHeight: '90vh', padding: '16px' }}>
              <button
                onClick={() => setViewingPhoto(null)}
                style={{
                  position: 'absolute',
                  top: '-8px',
                  right: '-8px',
                  padding: '8px',
                  backgroundColor: 'white',
                  borderRadius: '50%',
                  border: 'none',
                  cursor: 'pointer',
                  zIndex: 10
                }}
              >
                <FiX style={{ width: '20px', height: '20px' }} />
              </button>
              <img
                src={viewingPhoto}
                alt="Foto Absensi"
                style={{
                  maxWidth: '100%',
                  maxHeight: '100%',
                  objectFit: 'contain',
                  borderRadius: '8px'
                }}
              />
            </div>
          </div>
        )}

        {/* Processing Overlay */}
        {isProcessing && (
          <div style={{
            position: 'fixed',
            inset: 0,
            zIndex: 100,
            backgroundColor: 'rgba(0,0,0,0.75)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
          }}>
            <div style={{
              backgroundColor: 'white',
              padding: '32px',
              borderRadius: '12px',
              textAlign: 'center',
              minWidth: '250px'
            }}>
              <div style={{
                width: '48px',
                height: '48px',
                border: '4px solid #e5e7eb',
                borderTopColor: '#2563eb',
                borderRadius: '50%',
                animation: 'spin 1s linear infinite',
                margin: '0 auto 16px'
              }} />
              <p style={{ fontSize: '16px', fontWeight: '500', color: '#1f2937' }}>
                Memproses approval...
              </p>
            </div>
          </div>
        )}
      </div>

      <style>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
};

export default AttendanceApproval;