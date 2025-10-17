// resources/js/pages/absen-mandor.tsx - REFACTORED
import React, { useState, useEffect } from 'react';
import Camera from '../components/camera';
import {
  FiArrowLeft, FiUsers, FiCheck, FiCalendar, FiEye, FiX,
  FiEdit3, FiClock, FiXCircle, FiCheckCircle, FiAlertTriangle, 
  FiMapPin, FiHome
} from 'react-icons/fi';

// ============================================================================
// TYPE DEFINITIONS
// ============================================================================

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
  absentype: 'HADIR' | 'LOKASI';
  checkintime: string | null;
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

type AbsenType = 'HADIR' | 'LOKASI';

// ============================================================================
// MAIN COMPONENT
// ============================================================================

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
  const [attendanceDate, setAttendanceDate] = useState(new Date().toISOString().split('T')[0]);
  const [viewingPhoto, setViewingPhoto] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<'all' | 'hadir' | 'lokasi' | 'pending' | 'approved' | 'rejected'>('all');
  const [selectedAbsenType, setSelectedAbsenType] = useState<AbsenType>('HADIR');
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
      console.log('👥 Loaded workers:', data.workers?.length || 0);
    } catch (error) {
      console.error('❌ Error loading workers:', error);
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
      console.log('📋 Loaded attendance:', data.attendance?.length || 0, 'for date:', attendanceDate);
    } catch (error) {
      console.error('❌ Error loading attendance:', error);
    } finally {
      setIsLoadingAttendance(false);
    }
  };

  // ✅ NEW: Get available workers with button state logic
  const getAvailableWorkers = () => {
    return workers.map(worker => {
      const hadirRecord = todayAttendance.find(
        att => att.tenagakerjaid === worker.tenagakerjaid && att.absentype === 'HADIR'
      );
      const lokasiRecord = todayAttendance.find(
        att => att.tenagakerjaid === worker.tenagakerjaid && att.absentype === 'LOKASI'
      );
      
      return {
        ...worker,
        hasHadir: !!hadirRecord,
        hasLokasi: !!lokasiRecord,
        // Worker hilang dari list jika sudah HADIR + LOKASI
        shouldShow: !(hadirRecord && lokasiRecord)
      };
    }).filter(worker => worker.shouldShow);
  };

  const getFilteredAttendance = () => {
    switch (activeTab) {
      case 'hadir':
        return todayAttendance.filter(att => att.absentype === 'HADIR');
      case 'lokasi':
        return todayAttendance.filter(att => att.absentype === 'LOKASI');
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

  const handleWorkerSelect = (worker: Worker, type: AbsenType) => {
    setSelectedWorker(worker);
    setSelectedAbsenType(type);
    setEditingAttendance(null);
    setIsCameraOpen(true);
    console.log('👤 Selected worker:', worker.nama, 'Type:', type);
  };

  const handleEditPhoto = (attendance: AttendanceRecord) => {
    setEditingAttendance(attendance);
    setSelectedWorker(null);
    setSelectedAbsenType(attendance.absentype);
    setIsCameraOpen(true);
    console.log('✏️ Editing attendance:', attendance.tenaga_kerja.nama, 'Type:', attendance.absentype);
  };

  const handlePhotoCapture = async (
    photoDataUrl: string, 
    gpsCoordinates?: { latitude: number; longitude: number },
    timestamp?: string
  ) => {
    if (!selectedWorker && !editingAttendance) return;

    console.log('📤 Submitting attendance with data:', {
      worker: selectedWorker?.nama || editingAttendance?.tenaga_kerja.nama,
      type: selectedAbsenType,
      hasGPS: !!gpsCoordinates,
      timestamp: timestamp,
      photoSize: photoDataUrl.length
    });

    setIsSubmitting(true);
    try {
      let url: string;
      let payload: any;

      if (editingAttendance) {
        url = routes.update_photo;
        payload = {
          absenno: editingAttendance.absenno,
          absen_id: editingAttendance.absen_id,
          tenagakerjaid: editingAttendance.tenagakerjaid,
          photo: photoDataUrl,
          latitude: gpsCoordinates?.latitude,
          longitude: gpsCoordinates?.longitude,
          timestamp: timestamp
        };
      } else if (selectedWorker) {
        url = routes.process_checkin;
        payload = {
          tenagakerjaid: selectedWorker.tenagakerjaid,
          photo: photoDataUrl,
          absentype: selectedAbsenType,
          latitude: gpsCoordinates?.latitude,
          longitude: gpsCoordinates?.longitude,
          timestamp: timestamp
        };
      } else {
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
      console.log('📥 Server response:', result);

      if (result.success) {
        await Promise.all([
          loadWorkersData(),
          loadAttendanceData()
        ]);
        
        const workerName = editingAttendance 
          ? editingAttendance.tenaga_kerja.nama 
          : selectedWorker?.nama;
        
        const message = editingAttendance 
          ? `Foto berhasil diupdate untuk ${workerName} (status direset ke PENDING)`
          : `Absen ${selectedAbsenType} berhasil untuk ${workerName}`;
        
        console.log('✅ Success:', message);
        alert(message);
      } else {
        console.error('❌ Server error:', result.error || result.message);
        alert(result.error || result.message || 'Gagal menyimpan');
      }
    } catch (error) {
      console.error('❌ Error submitting attendance:', error);
      alert('Terjadi kesalahan saat menyimpan');
    } finally {
      setIsSubmitting(false);
      setSelectedWorker(null);
      setEditingAttendance(null);
      setIsCameraOpen(false);
    }
  };

  const getStatusBadge = (status: string) => {
    const styles: Record<string, any> = {
      PENDING: { bg: '#fef3c7', color: '#92400e', icon: FiClock },
      APPROVED: { bg: '#dcfce7', color: '#166534', icon: FiCheckCircle },
      REJECTED: { bg: '#fee2e2', color: '#991b1b', icon: FiXCircle }
    };
    
    const style = styles[status];
    if (!style) return null;
    
    const Icon = style.icon;
    
    return (
      <span style={{
        display: 'inline-flex',
        alignItems: 'center',
        gap: '4px',
        padding: '4px 8px',
        backgroundColor: style.bg,
        color: style.color,
        fontSize: '12px',
        borderRadius: '9999px'
      }}>
        <Icon style={{ width: '12px', height: '12px' }} />
        {status === 'PENDING' ? 'Pending' : status === 'APPROVED' ? 'Approved' : 'Rejected'}
      </span>
    );
  };

  const getTypeBadge = (type: 'HADIR' | 'LOKASI') => {
    if (type === 'HADIR') {
      return (
        <span style={{
          display: 'inline-flex',
          alignItems: 'center',
          gap: '4px',
          padding: '4px 8px',
          backgroundColor: '#dbeafe',
          color: '#1e40af',
          fontSize: '12px',
          borderRadius: '9999px'
        }}>
          <FiHome style={{ width: '12px', height: '12px' }} />
          HADIR
        </span>
      );
    } else {
      return (
        <span style={{
          display: 'inline-flex',
          alignItems: 'center',
          gap: '4px',
          padding: '4px 8px',
          backgroundColor: '#f3e8ff',
          color: '#7c3aed',
          fontSize: '12px',
          borderRadius: '9999px'
        }}>
          <FiMapPin style={{ width: '12px', height: '12px' }} />
          LOKASI
        </span>
      );
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
      case 'hadir':
        return todayAttendance.filter(att => att.absentype === 'HADIR').length;
      case 'lokasi':
        return todayAttendance.filter(att => att.absentype === 'LOKASI').length;
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
  const isToday = attendanceDate === new Date().toISOString().split('T')[0];

  const hadirCount = todayAttendance.filter(att => att.absentype === 'HADIR').length;
  const lokasiCount = todayAttendance.filter(att => att.absentype === 'LOKASI').length;

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
              marginBottom: '16px',
              transition: 'color 0.2s'
            }}
          >
            <FiArrowLeft style={{ width: '16px', height: '16px' }} />
            Kembali ke Beranda
          </button>
          
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
            <div>
              <h1 style={{ fontSize: '30px', fontWeight: 'bold', color: 'black', marginBottom: '8px' }}>
                Sistem Absensi
              </h1>
              <p style={{ color: '#6b7280' }}>Absen HADIR (pagi) dan LOKASI (di kebun/plot)</p>
            </div>
          </div>
          
          {/* Date Info */}
          <div style={{
            marginTop: '16px',
            padding: '16px',
            backgroundColor: 'white',
            borderRadius: '8px',
            border: '1px solid #e5e7eb'
          }}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <div>
                <h3 style={{ fontWeight: '600', color: 'black' }}>
                  {formatDate(new Date().toISOString().split('T')[0])}
                  <span style={{
                    marginLeft: '8px',
                    padding: '4px 8px',
                    backgroundColor: '#dcfce7',
                    color: '#166534',
                    fontSize: '12px',
                    borderRadius: '9999px'
                  }}>
                    Hari ini
                  </span>
                </h3>
                <p style={{ fontSize: '14px', color: '#6b7280' }}>
                  {isLoadingWorkers ? (
                    "Memuat data pekerja..."
                  ) : (
                    `${availableWorkers.length} pekerja masih perlu absen • ${workers.length} total pekerja`
                  )}
                </p>
              </div>
              <div style={{ textAlign: 'right' }}>
                <div style={{ display: 'flex', gap: '16px' }}>
                  <div>
                    <div style={{ fontSize: '18px', fontWeight: 'bold', color: '#2563eb' }}>{hadirCount}</div>
                    <div style={{ fontSize: '12px', color: '#6b7280' }}>HADIR</div>
                  </div>
                  <div>
                    <div style={{ fontSize: '18px', fontWeight: 'bold', color: '#9333ea' }}>{lokasiCount}</div>
                    <div style={{ fontSize: '12px', color: '#6b7280' }}>LOKASI</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Main Content */}
        <div style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fit, minmax(500px, 1fr))',
          gap: '32px'
        }}>
          
          {/* Left Card - Belum Absen */}
          <div style={{
            backgroundColor: 'white',
            borderRadius: '8px',
            boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
            border: '1px solid #e5e7eb'
          }}>
            <div style={{
              borderBottom: '1px solid #e5e7eb',
              padding: '24px'
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <FiUsers style={{ width: '20px', height: '20px', color: '#f97316' }} />
                <h2 style={{ fontSize: '20px', fontWeight: '600', color: 'black' }}>
                  Perlu Absen {!isLoadingWorkers && `(${availableWorkers.length})`}
                </h2>
              </div>
              <p style={{ fontSize: '12px', color: '#6b7280', marginTop: '4px' }}>
                Absen HADIR (pagi) dan LOKASI (di kebun/plot)
              </p>
            </div>
            
            <div style={{ maxHeight: '384px', overflowY: 'auto' }}>
              {isLoadingWorkers ? (
                <div style={{ padding: '32px', textAlign: 'center' }}>
                  <div style={{
                    width: '40px',
                    height: '40px',
                    border: '4px solid #e5e7eb',
                    borderTopColor: '#f97316',
                    borderRadius: '50%',
                    animation: 'spin 1s linear infinite',
                    margin: '0 auto'
                  }} />
                  <p style={{ marginTop: '16px', color: '#6b7280' }}>Memuat data pekerja...</p>
                </div>
              ) : availableWorkers.length > 0 ? (
                <div>
                  {availableWorkers.map((worker) => (
                    <div
                      key={worker.tenagakerjaid}
                      style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        padding: '16px',
                        borderBottom: '1px solid #f3f4f6',
                        transition: 'background-color 0.2s'
                      }}
                    >
                      <div style={{ flex: 1 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                          <div style={{ fontWeight: '500', color: 'black' }}>{worker.nama}</div>
                          {worker.hasHadir && (
                            <span style={{
                              padding: '2px 8px',
                              backgroundColor: '#dbeafe',
                              color: '#1e40af',
                              fontSize: '11px',
                              borderRadius: '9999px',
                              fontWeight: '600'
                            }}>
                              ✓ HADIR
                            </span>
                          )}
                        </div>
                        <div style={{ fontSize: '14px', color: '#6b7280' }}>
                          {worker.nik} • {worker.gender === 'L' ? 'Laki-laki' : 'Perempuan'} • {getJenisKerja(worker.jenistenagakerja)}
                        </div>
                      </div>
                      
                      {isToday && (
                        <div style={{ display: 'flex', gap: '8px' }}>
                          {/* ✅ HADIR Button - Disabled if already HADIR */}
                          {!worker.hasHadir ? (
                            <button 
                              onClick={() => handleWorkerSelect(worker, 'HADIR')}
                              style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: '8px',
                                padding: '8px 16px',
                                backgroundColor: '#2563eb',
                                color: 'white',
                                border: 'none',
                                borderRadius: '8px',
                                cursor: 'pointer',
                                fontSize: '14px',
                                transition: 'background-color 0.2s'
                              }}
                            >
                              <FiHome style={{ width: '16px', height: '16px' }} />
                              HADIR
                            </button>
                          ) : (
                            <button 
                              disabled
                              style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: '8px',
                                padding: '8px 16px',
                                backgroundColor: '#e5e7eb',
                                color: '#9ca3af',
                                border: 'none',
                                borderRadius: '8px',
                                cursor: 'not-allowed',
                                fontSize: '14px'
                              }}
                              title="Sudah absen HADIR"
                            >
                              <FiCheck style={{ width: '16px', height: '16px' }} />
                              HADIR
                            </button>
                          )}
                          
                          {/* ✅ LOKASI Button - Only enabled if hasHadir = true */}
                          <button 
                            onClick={() => handleWorkerSelect(worker, 'LOKASI')}
                            disabled={!worker.hasHadir}
                            style={{
                              display: 'flex',
                              alignItems: 'center',
                              gap: '8px',
                              padding: '8px 16px',
                              backgroundColor: worker.hasHadir ? '#7c3aed' : '#e5e7eb',
                              color: worker.hasHadir ? 'white' : '#9ca3af',
                              border: 'none',
                              borderRadius: '8px',
                              cursor: worker.hasHadir ? 'pointer' : 'not-allowed',
                              fontSize: '14px',
                              transition: 'background-color 0.2s'
                            }}
                            title={!worker.hasHadir ? "Harus absen HADIR terlebih dahulu" : ""}
                          >
                            <FiMapPin style={{ width: '16px', height: '16px' }} />
                            LOKASI
                          </button>
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              ) : (
                <div style={{ padding: '32px', textAlign: 'center' }}>
                  <FiCheck style={{ width: '48px', height: '48px', color: '#10b981', margin: '0 auto 16px' }} />
                  <p style={{ color: '#6b7280', fontWeight: '500' }}>Semua pekerja sudah lengkap!</p>
                  <p style={{ fontSize: '12px', color: '#9ca3af', marginTop: '4px' }}>
                    HADIR dan LOKASI sudah tercatat
                  </p>
                </div>
              )}
            </div>
          </div>

          {/* Right Card - Data Absen */}
          <div style={{
            backgroundColor: 'white',
            borderRadius: '8px',
            boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
            border: '1px solid #e5e7eb'
          }}>
            <div style={{
              borderBottom: '1px solid #e5e7eb',
              padding: '24px'
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '12px' }}>
                <FiCheck style={{ width: '20px', height: '20px', color: '#2563eb' }} />
                <h2 style={{ fontSize: '20px', fontWeight: '600', color: 'black' }}>
                  Data Absen {!isLoadingAttendance && `(${todayAttendance.length})`}
                </h2>
              </div>
              
              {/* Date Filter */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px' }}>
                <FiCalendar style={{ width: '16px', height: '16px', color: '#6b7280' }} />
                <input
                  type="date"
                  value={attendanceDate}
                  onChange={(e) => setAttendanceDate(e.target.value)}
                  style={{
                    padding: '8px 12px',
                    fontSize: '14px',
                    border: '1px solid #d1d5db',
                    borderRadius: '8px',
                    outline: 'none'
                  }}
                />
                <span style={{ fontSize: '14px', color: '#6b7280' }}>
                  {formatDate(attendanceDate)}
                </span>
              </div>

              {/* Status Tabs */}
              <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                {[
                  { key: 'all', label: 'Semua', icon: FiUsers },
                  { key: 'hadir', label: 'HADIR', icon: FiHome },
                  { key: 'lokasi', label: 'LOKASI', icon: FiMapPin },
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
                      style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '4px',
                        padding: '8px 12px',
                        fontSize: '12px',
                        borderRadius: '8px',
                        border: 'none',
                        cursor: 'pointer',
                        transition: 'all 0.2s',
                        backgroundColor: isActive ? '#2563eb' : '#f3f4f6',
                        color: isActive ? 'white' : '#6b7280'
                      }}
                    >
                      <Icon style={{ width: '12px', height: '12px' }} />
                      <span>{tab.label}</span>
                      <span style={{
                        padding: '2px 6px',
                        borderRadius: '9999px',
                        fontSize: '12px',
                        backgroundColor: isActive ? 'rgba(255,255,255,0.2)' : '#e5e7eb'
                      }}>
                        {count}
                      </span>
                    </button>
                  );
                })}
              </div>
            </div>
            
            <div style={{ maxHeight: '384px', overflowY: 'auto' }}>
              {isLoadingAttendance ? (
                <div style={{ padding: '32px', textAlign: 'center' }}>
                  <div style={{
                    width: '40px',
                    height: '40px',
                    border: '4px solid #e5e7eb',
                    borderTopColor: '#2563eb',
                    borderRadius: '50%',
                    animation: 'spin 1s linear infinite',
                    margin: '0 auto'
                  }} />
                  <p style={{ marginTop: '16px', color: '#6b7280' }}>Memuat data absensi...</p>
                </div>
              ) : filteredAttendance.length > 0 ? (
                <div>
                  {filteredAttendance.map((record) => (
                    <div
                      key={`${record.tenagakerjaid}-${record.absenmasuk}`}
                      style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        padding: '16px',
                        borderBottom: '1px solid #f3f4f6'
                      }}
                    >
                      <div style={{ flex: 1 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '4px' }}>
                          <div style={{ fontWeight: '500', color: 'black' }}>{record.tenaga_kerja.nama}</div>
                          {getTypeBadge(record.absentype)}
                          {getStatusBadge(record.approval_status)}
                          {record.is_edited && (
                            <span style={{
                              padding: '2px 6px',
                              backgroundColor: '#dbeafe',
                              color: '#1e40af',
                              fontSize: '12px',
                              borderRadius: '4px'
                            }}>
                              Edited {record.edit_count}x
                            </span>
                          )}
                        </div>
                        <div style={{ fontSize: '14px', color: '#6b7280' }}>
                          {record.tenaga_kerja.nik} • {formatTime(record.absenmasuk)}
                          {record.absentype === 'LOKASI' && record.checkintime && (
                            <span style={{ marginLeft: '8px', color: '#7c3aed' }}>
                              • Checkin: {formatTime(record.checkintime)}
                            </span>
                          )}
                        </div>
                        {record.rejection_reason && (
                          <div style={{ fontSize: '12px', color: '#dc2626', marginTop: '4px' }}>
                            <FiAlertTriangle style={{ width: '12px', height: '12px', display: 'inline', marginRight: '4px' }} />
                            {record.rejection_reason}
                          </div>
                        )}
                      </div>
                      
                      <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                        <button
                          onClick={() => setViewingPhoto(record.fotoabsen)}
                          style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: '8px',
                            padding: '8px 12px',
                            backgroundColor: '#f3f4f6',
                            color: '#374151',
                            border: 'none',
                            borderRadius: '8px',
                            cursor: 'pointer',
                            fontSize: '14px',
                            transition: 'background-color 0.2s'
                          }}
                        >
                          <FiEye style={{ width: '16px', height: '16px' }} />
                          Lihat
                        </button>
                        
                        {(record.approval_status === 'REJECTED' || record.approval_status === 'PENDING') && isToday && (
                          <button
                            onClick={() => handleEditPhoto(record)}
                            style={{
                              display: 'flex',
                              alignItems: 'center',
                              gap: '8px',
                              padding: '8px 12px',
                              backgroundColor: record.approval_status === 'REJECTED' ? '#fee2e2' : '#fef3c7',
                              color: record.approval_status === 'REJECTED' ? '#991b1b' : '#92400e',
                              border: 'none',
                              borderRadius: '8px',
                              cursor: 'pointer',
                              fontSize: '14px',
                              transition: 'background-color 0.2s'
                            }}
                          >
                            <FiEdit3 style={{ width: '16px', height: '16px' }} />
                            Edit
                          </button>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div style={{ padding: '32px', textAlign: 'center' }}>
                  <FiUsers style={{ width: '48px', height: '48px', color: '#d1d5db', margin: '0 auto 16px' }} />
                  <p style={{ color: '#6b7280' }}>
                    {activeTab === 'all' 
                      ? 'Belum ada yang absen'
                      : `Tidak ada absensi dengan filter ${activeTab.toUpperCase()}`
                    }
                  </p>
                  <p style={{ fontSize: '12px', color: '#9ca3af', marginTop: '4px' }}>untuk {formatDate(attendanceDate)}</p>
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
              ? `${editingAttendance.tenaga_kerja.nama} (Edit ${editingAttendance.absentype})`
              : selectedWorker 
                ? `${selectedWorker.nama} - ${selectedAbsenType}`
                : undefined
          }
          requireGPS={selectedAbsenType === 'LOKASI'}
        />

        {/* Photo Viewer Modal */}
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
                  boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
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

        {/* Loading Overlay */}
        {isSubmitting && (
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
                Menyimpan absensi...
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

export default AbsenMandor;