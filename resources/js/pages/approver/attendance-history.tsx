// resources/js/pages/approver/attendance-history.tsx - WITH TABS

import React, { useState, useEffect } from 'react';
import {
  FiArrowLeft, FiCheck, FiX, FiEye, FiCalendar, FiUser, FiFilter, FiRefreshCw,
  FiHome, FiMapPin
} from 'react-icons/fi';

interface HistoryRecord {
  absenno: string;
  absen_id: number;
  tenagakerjaid: string;
  pekerja_nama: string;
  pekerja_nik: string;
  mandorid: string;
  mandor_nama: string;
  absenmasuk: string;
  absen_time: string;
  absen_date_formatted: string;
  absentype: 'HADIR' | 'LOKASI';
  checkintime?: string;
  approval_status: string;
  status_label: string;
  processed_date: string;
  processed_date_formatted: string;
  approved_by: string;
  rejection_reason?: string;
  is_edited: boolean;
  edit_count: number;
}

interface AttendanceHistoryProps {
  routes: {
    attendance_history: string;
  };
  csrf_token: string;
  onSectionChange: (section: string) => void;
}

const AttendanceHistory: React.FC<AttendanceHistoryProps> = ({ 
  routes,
  csrf_token,
  onSectionChange 
}) => {
  const [historyRecords, setHistoryRecords] = useState<HistoryRecord[]>([]);
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]);
  const [statusFilter, setStatusFilter] = useState('ALL');
  const [mandorFilter, setMandorFilter] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  
  // NEW: Tab state
  const [activeTab, setActiveTab] = useState<'HADIR' | 'LOKASI'>('HADIR');

  useEffect(() => {
    loadHistoryData();
  }, [selectedDate, statusFilter, mandorFilter]);

  const loadHistoryData = async () => {
    setIsLoading(true);
    try {
      const params = new URLSearchParams();
      if (selectedDate) params.append('date', selectedDate);
      if (statusFilter !== 'ALL') params.append('status', statusFilter);
      if (mandorFilter) params.append('mandor_id', mandorFilter);
      
      const response = await fetch(`${routes.attendance_history}?${params.toString()}`);
      const data = await response.json();
      setHistoryRecords(data.history || []);
    } catch (error) {
      console.error('Error loading history data:', error);
    } finally {
      setIsLoading(false);
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

  // NEW: Filter by active tab
  const getFilteredRecords = (): HistoryRecord[] => {
    return historyRecords.filter(record => record.absentype === activeTab);
  };

  const getStatusBadge = (status: string) => {
    if (status === 'APPROVED') {
      return (
        <span style={{
          display: 'inline-flex',
          alignItems: 'center',
          gap: '4px',
          padding: '4px 8px',
          backgroundColor: '#dcfce7',
          color: '#166534',
          fontSize: '12px',
          borderRadius: '9999px'
        }}>
          <FiCheck style={{ width: '12px', height: '12px' }} />
          Disetujui
        </span>
      );
    } else if (status === 'REJECTED') {
      return (
        <span style={{
          display: 'inline-flex',
          alignItems: 'center',
          gap: '4px',
          padding: '4px 8px',
          backgroundColor: '#fee2e2',
          color: '#991b1b',
          fontSize: '12px',
          borderRadius: '9999px'
        }}>
          <FiX style={{ width: '12px', height: '12px' }} />
          Ditolak
        </span>
      );
    }
    return null;
  };

  const uniqueMandors = Array.from(
    new Set(historyRecords.map(r => JSON.stringify({ id: r.mandorid, name: r.mandor_nama })))
  ).map(str => JSON.parse(str));

  // NEW: Get counts per tab
  const hadirCount = historyRecords.filter(r => r.absentype === 'HADIR').length;
  const lokasiCount = historyRecords.filter(r => r.absentype === 'LOKASI').length;

  const filteredRecords = getFilteredRecords();

  const summary = {
    total: filteredRecords.length,
    approved: filteredRecords.filter(r => r.approval_status === 'APPROVED').length,
    rejected: filteredRecords.filter(r => r.approval_status === 'REJECTED').length,
  };

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
                Riwayat Approval
              </h1>
              <p style={{ color: '#6b7280' }}>Histori approval dan reject absensi HADIR dan LOKASI</p>
            </div>
            <button
              onClick={loadHistoryData}
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
          
          {/* Filters */}
          <div style={{ 
            marginTop: '24px', 
            padding: '16px', 
            backgroundColor: 'white', 
            borderRadius: '8px', 
            border: '1px solid #e5e7eb' 
          }}>
            <div style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: '16px' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <FiCalendar style={{ width: '16px', height: '16px', color: '#6b7280' }} />
                <input
                  type="date"
                  value={selectedDate}
                  onChange={(e) => setSelectedDate(e.target.value)}
                  style={{
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '8px',
                    outline: 'none'
                  }}
                />
                <span style={{ fontSize: '14px', color: '#6b7280' }}>
                  {formatDate(selectedDate)}
                </span>
              </div>
              
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <FiFilter style={{ width: '16px', height: '16px', color: '#6b7280' }} />
                <select
                  value={statusFilter}
                  onChange={(e) => setStatusFilter(e.target.value)}
                  style={{
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '8px',
                    outline: 'none'
                  }}
                >
                  <option value="ALL">Semua Status</option>
                  <option value="APPROVED">Disetujui</option>
                  <option value="REJECTED">Ditolak</option>
                </select>
              </div>

              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <FiUser style={{ width: '16px', height: '16px', color: '#6b7280' }} />
                <select
                  value={mandorFilter}
                  onChange={(e) => setMandorFilter(e.target.value)}
                  style={{
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '8px',
                    outline: 'none'
                  }}
                >
                  <option value="">Semua Mandor</option>
                  {uniqueMandors.map((mandor) => (
                    <option key={mandor.id} value={mandor.id}>
                      {mandor.name}
                    </option>
                  ))}
                </select>
              </div>
              
              {/* Summary */}
              <div style={{ marginLeft: 'auto', display: 'flex', alignItems: 'center', gap: '24px' }}>
                <div style={{ fontSize: '14px' }}>
                  <span style={{ color: '#6b7280' }}>Total: </span>
                  <span style={{ fontWeight: '600', color: '#111827' }}>{summary.total}</span>
                </div>
                <div style={{ fontSize: '14px' }}>
                  <span style={{ color: '#6b7280' }}>Approved: </span>
                  <span style={{ fontWeight: '600', color: '#16a34a' }}>{summary.approved}</span>
                </div>
                <div style={{ fontSize: '14px' }}>
                  <span style={{ color: '#6b7280' }}>Rejected: </span>
                  <span style={{ fontWeight: '600', color: '#dc2626' }}>{summary.rejected}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* NEW: Tabs */}
        <div style={{ display: 'flex', gap: '8px', marginBottom: '24px', borderBottom: '2px solid #e5e7eb' }}>
          <button
            onClick={() => setActiveTab('HADIR')}
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
            onClick={() => setActiveTab('LOKASI')}
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

        {/* History Records */}
        <div style={{ backgroundColor: 'white', borderRadius: '8px', boxShadow: '0 1px 3px rgba(0,0,0,0.1)', border: '1px solid #e5e7eb' }}>
          <div style={{ borderBottom: '1px solid #e5e7eb', padding: '24px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              <FiCheck style={{ width: '20px', height: '20px', color: '#10b981' }} />
              <h2 style={{ fontSize: '20px', fontWeight: '600', color: 'black' }}>
                Riwayat {activeTab} {!isLoading && `(${filteredRecords.length})`}
              </h2>
            </div>
          </div>
          
          <div style={{ maxHeight: '600px', overflowY: 'auto' }}>
            {isLoading ? (
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
                <p style={{ marginTop: '16px', color: '#6b7280' }}>Memuat riwayat approval...</p>
              </div>
            ) : filteredRecords.length > 0 ? (
              <div>
                {filteredRecords.map((record) => (
                  <div 
                    key={`${record.absenno}-${record.absen_id}`} 
                    style={{ 
                      padding: '24px', 
                      borderBottom: '1px solid #f3f4f6',
                      transition: 'background-color 0.2s'
                    }}
                  >
                    <div style={{ display: 'flex', alignItems: 'start', justifyContent: 'space-between' }}>
                      <div style={{ flex: 1 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '8px' }}>
                          <h3 style={{ fontWeight: '600', color: 'black', margin: 0 }}>{record.pekerja_nama}</h3>
                          {getStatusBadge(record.approval_status)}
                          {record.is_edited && (
                            <span style={{
                              padding: '2px 8px',
                              backgroundColor: '#dbeafe',
                              color: '#1e40af',
                              fontSize: '12px',
                              borderRadius: '9999px'
                            }}>
                              Edited {record.edit_count}x
                            </span>
                          )}
                        </div>
                        
                        <div style={{ 
                          display: 'grid', 
                          gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', 
                          gap: '16px', 
                          fontSize: '14px', 
                          color: '#6b7280', 
                          marginBottom: '12px' 
                        }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                            <FiUser style={{ width: '16px', height: '16px' }} />
                            <span>Mandor: {record.mandor_nama}</span>
                          </div>
                          <div>
                            <span>NIK: {record.pekerja_nik}</span>
                          </div>
                          <div>
                            <span>Absen: {record.absen_time}</span>
                          </div>
                          {record.checkintime && (
                            <div style={{ color: '#7c3aed' }}>
                              <FiMapPin style={{ width: '14px', height: '14px', display: 'inline', marginRight: '4px' }} />
                              Checkin: {new Date(record.checkintime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                            </div>
                          )}
                          <div style={{ gridColumn: 'span 2' }}>
                            Diproses: {record.processed_date_formatted}
                          </div>
                          <div>
                            {record.approval_status === 'APPROVED' ? 'Approved' : 'Rejected'} by: {record.approved_by?.split(' - ')[0]}
                          </div>
                        </div>
                        
                        {/* Rejection reason */}
                        {record.approval_status === 'REJECTED' && record.rejection_reason && (
                          <div style={{ 
                            marginTop: '8px', 
                            padding: '12px', 
                            backgroundColor: '#fef2f2', 
                            border: '1px solid #fecaca', 
                            borderRadius: '8px' 
                          }}>
                            <span style={{ fontSize: '12px', color: '#dc2626', fontWeight: '500' }}>Alasan Penolakan:</span>
                            <p style={{ fontSize: '14px', color: '#991b1b', marginTop: '4px', margin: 0 }}>
                              {record.rejection_reason}
                            </p>
                          </div>
                        )}
                        
                        {/* Approval notes */}
                        {record.approval_status === 'APPROVED' && record.approved_by?.includes(' - ') && !record.approved_by?.includes('REJECT:') && (
                          <div style={{ 
                            marginTop: '8px', 
                            padding: '12px', 
                            backgroundColor: '#f0fdf4', 
                            border: '1px solid #bbf7d0', 
                            borderRadius: '8px' 
                          }}>
                            <span style={{ fontSize: '12px', color: '#16a34a', fontWeight: '500' }}>Catatan:</span>
                            <p style={{ fontSize: '14px', color: '#166534', marginTop: '4px', margin: 0 }}>
                              {record.approved_by.split(' - ')[1]?.trim()}
                            </p>
                          </div>
                        )}
                      </div>
                      
                      <div style={{ fontSize: '12px', color: '#9ca3af' }}>
                        ID: {record.absenno}-{record.absen_id}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div style={{ padding: '32px', textAlign: 'center' }}>
                <FiCheck style={{ width: '48px', height: '48px', color: '#d1d5db', margin: '0 auto 16px' }} />
                <p style={{ color: '#6b7280' }}>Tidak ada riwayat {activeTab}</p>
                <p style={{ fontSize: '12px', color: '#9ca3af', marginTop: '4px' }}>
                  untuk {formatDate(selectedDate)} 
                  {statusFilter !== 'ALL' && ` dengan status ${statusFilter}`}
                  {mandorFilter && ` untuk mandor terpilih`}
                </p>
              </div>
            )}
          </div>
        </div>

        {/* Statistics Summary */}
        {filteredRecords.length > 0 && (
          <div style={{ 
            marginTop: '32px', 
            display: 'grid', 
            gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', 
            gap: '24px' 
          }}>
            <div style={{ 
              backgroundColor: 'white', 
              padding: '24px', 
              borderRadius: '8px', 
              boxShadow: '0 1px 3px rgba(0,0,0,0.1)', 
              border: '1px solid #e5e7eb' 
            }}>
              <div style={{ display: 'flex', alignItems: 'center' }}>
                <div style={{ padding: '12px', borderRadius: '8px', backgroundColor: '#dbeafe' }}>
                  <FiUser style={{ width: '24px', height: '24px', color: '#2563eb' }} />
                </div>
                <div style={{ marginLeft: '16px' }}>
                  <h3 style={{ fontSize: '14px', fontWeight: '500', color: '#6b7280', margin: 0 }}>Total Records</h3>
                  <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#111827' }}>{summary.total}</div>
                </div>
              </div>
            </div>
            
            <div style={{ 
              backgroundColor: 'white', 
              padding: '24px', 
              borderRadius: '8px', 
              boxShadow: '0 1px 3px rgba(0,0,0,0.1)', 
              border: '1px solid #e5e7eb' 
            }}>
              <div style={{ display: 'flex', alignItems: 'center' }}>
                <div style={{ padding: '12px', borderRadius: '8px', backgroundColor: '#dcfce7' }}>
                  <FiCheck style={{ width: '24px', height: '24px', color: '#16a34a' }} />
                </div>
                <div style={{ marginLeft: '16px' }}>
                  <h3 style={{ fontSize: '14px', fontWeight: '500', color: '#6b7280', margin: 0 }}>Approval Rate</h3>
                  <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#16a34a' }}>
                    {summary.total > 0 ? Math.round((summary.approved / summary.total) * 100) : 0}%
                  </div>
                </div>
              </div>
            </div>
            
            <div style={{ 
              backgroundColor: 'white', 
              padding: '24px', 
              borderRadius: '8px', 
              boxShadow: '0 1px 3px rgba(0,0,0,0.1)', 
              border: '1px solid #e5e7eb' 
            }}>
              <div style={{ display: 'flex', alignItems: 'center' }}>
                <div style={{ padding: '12px', borderRadius: '8px', backgroundColor: '#fee2e2' }}>
                  <FiX style={{ width: '24px', height: '24px', color: '#dc2626' }} />
                </div>
                <div style={{ marginLeft: '16px' }}>
                  <h3 style={{ fontSize: '14px', fontWeight: '500', color: '#6b7280', margin: 0 }}>Rejection Rate</h3>
                  <div style={{ fontSize: '24px', fontWeight: 'bold', color: '#dc2626' }}>
                    {summary.total > 0 ? Math.round((summary.rejected / summary.total) * 100) : 0}%
                  </div>
                </div>
              </div>
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

export default AttendanceHistory;