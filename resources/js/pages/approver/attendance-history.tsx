// resources/js/pages/approver/attendance-history.tsx - FIXED

import React, { useState, useEffect } from 'react';
import {
  FiArrowLeft, FiCheck, FiX, FiEye, FiCalendar, FiUser, FiFilter, FiRefreshCw
} from 'react-icons/fi';
import { LoadingCard, LoadingInline } from '../../components/loading-spinner';

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
  approval_status: string;
  status_label: string;
  processed_date: string;
  processed_date_formatted: string;
  approved_by: string;
  rejection_reason?: string;
  is_edited: boolean;
  edit_count: number;
}

// FIXED: Remove attendance_detail from props
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
  const [statusFilter, setStatusFilter] = useState('ALL'); // ALL, APPROVED, REJECTED
  const [mandorFilter, setMandorFilter] = useState(''); // Filter by mandor
  const [isLoading, setIsLoading] = useState(true);

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

  // REMOVED: handleViewDetail function since attendance_detail route doesn't exist

  const getStatusBadge = (status: string) => {
    if (status === 'APPROVED') {
      return (
        <span className="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
          <FiCheck className="w-3 h-3" />
          Disetujui
        </span>
      );
    } else if (status === 'REJECTED') {
      return (
        <span className="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
          <FiX className="w-3 h-3" />
          Ditolak
        </span>
      );
    }
    return null;
  };

  const summary = {
    total: historyRecords.length,
    approved: historyRecords.filter(r => r.approval_status === 'APPROVED').length,
    rejected: historyRecords.filter(r => r.approval_status === 'REJECTED').length,
  };

  // Get unique mandors for filter
  const uniqueMandors = Array.from(
    new Set(historyRecords.map(r => JSON.stringify({ id: r.mandorid, name: r.mandor_nama })))
  ).map(str => JSON.parse(str));

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
              <h1 className="text-3xl font-bold text-black mb-2">Riwayat Approval</h1>
              <p className="text-gray-600">Histori approval dan reject absensi individual</p>
            </div>
            <button
              onClick={loadHistoryData}
              disabled={isLoading}
              className="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50"
            >
              <FiRefreshCw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span className="text-sm">Refresh</span>
            </button>
          </div>
          
          {/* Filters */}
          <div className="mt-6 p-4 bg-white rounded-lg border border-gray-200">
            <div className="flex flex-wrap items-center gap-4">
              <div className="flex items-center gap-2">
                <FiCalendar className="w-4 h-4 text-gray-500" />
                <input
                  type="date"
                  value={selectedDate}
                  onChange={(e) => setSelectedDate(e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                />
                <span className="text-sm text-gray-600">
                  {formatDate(selectedDate)}
                </span>
              </div>
              
              <div className="flex items-center gap-2">
                <FiFilter className="w-4 h-4 text-gray-500" />
                <select
                  value={statusFilter}
                  onChange={(e) => setStatusFilter(e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                >
                  <option value="ALL">Semua Status</option>
                  <option value="APPROVED">Disetujui</option>
                  <option value="REJECTED">Ditolak</option>
                </select>
              </div>

              <div className="flex items-center gap-2">
                <FiUser className="w-4 h-4 text-gray-500" />
                <select
                  value={mandorFilter}
                  onChange={(e) => setMandorFilter(e.target.value)}
                  className="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
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
              <div className="ml-auto flex items-center gap-6">
                <div className="text-sm">
                  <span className="text-gray-500">Total: </span>
                  <span className="font-semibold text-gray-900">{summary.total}</span>
                </div>
                <div className="text-sm">
                  <span className="text-gray-500">Approved: </span>
                  <span className="font-semibold text-green-600">{summary.approved}</span>
                </div>
                <div className="text-sm">
                  <span className="text-gray-500">Rejected: </span>
                  <span className="font-semibold text-red-600">{summary.rejected}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* History Records */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="border-b border-gray-200 p-6">
            <div className="flex items-center gap-3">
              <FiCheck className="w-5 h-5 text-green-500" />
              <h2 className="text-xl font-semibold text-black">
                Riwayat Approval {isLoading ? '' : `(${historyRecords.length})`}
              </h2>
              {isLoading && <LoadingInline color="green" />}
            </div>
          </div>
          
          <div className="max-h-[600px] overflow-y-auto">
            {isLoading ? (
              <LoadingCard text="Memuat riwayat approval..." />
            ) : historyRecords.length > 0 ? (
              <div className="divide-y divide-gray-100">
                {historyRecords.map((record) => (
                  <div key={`${record.absenno}-${record.absen_id}`} className="p-6 hover:bg-gray-50 transition-colors">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <h3 className="font-semibold text-black">{record.pekerja_nama}</h3>
                          {getStatusBadge(record.approval_status)}
                          {record.is_edited && (
                            <span className="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                              Edited {record.edit_count}x
                            </span>
                          )}
                        </div>
                        
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 mb-3">
                          <div className="flex items-center gap-2">
                            <FiUser className="w-4 h-4" />
                            <span>Mandor: {record.mandor_nama}</span>
                          </div>
                          <div>
                            <span>NIK: {record.pekerja_nik}</span>
                          </div>
                          <div>
                            <span>Absen: {record.absen_time}</span>
                          </div>
                          <div className="md:col-span-2">
                            Diproses: {record.processed_date_formatted}
                          </div>
                          <div>
                            {record.approval_status === 'APPROVED' ? 'Approved' : 'Rejected'} by: {record.approved_by?.split(' - ')[0]}
                          </div>
                        </div>
                        
                        {/* Rejection reason if available */}
                        {record.approval_status === 'REJECTED' && record.rejection_reason && (
                          <div className="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <span className="text-xs text-red-600 font-medium">Alasan Penolakan:</span>
                            <p className="text-sm text-red-700 mt-1">
                              {record.rejection_reason}
                            </p>
                          </div>
                        )}
                        
                        {/* Approval notes if available */}
                        {record.approval_status === 'APPROVED' && record.approved_by?.includes(' - ') && !record.approved_by?.includes('REJECT:') && (
                          <div className="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <span className="text-xs text-green-600 font-medium">Catatan:</span>
                            <p className="text-sm text-green-700 mt-1">
                              {record.approved_by.split(' - ')[1]?.trim()}
                            </p>
                          </div>
                        )}
                      </div>
                      
                      {/* REMOVED: Detail button since route doesn't exist */}
                      <div className="text-xs text-gray-400">
                        ID: {record.absenno}-{record.absen_id}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="p-8 text-center">
                <FiCheck className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                <p className="text-gray-500">Tidak ada riwayat approval</p>
                <p className="text-xs text-gray-400 mt-1">
                  untuk {formatDate(selectedDate)} 
                  {statusFilter !== 'ALL' && ` dengan status ${statusFilter}`}
                  {mandorFilter && ` untuk mandor terpilih`}
                </p>
              </div>
            )}
          </div>
        </div>

        {/* Statistics Summary */}
        {historyRecords.length > 0 && (
          <div className="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-3 rounded-lg bg-blue-100">
                  <FiUser className="w-6 h-6 text-blue-600" />
                </div>
                <div className="ml-4">
                  <h3 className="text-sm font-medium text-gray-500">Total Records</h3>
                  <div className="text-2xl font-bold text-gray-900">{summary.total}</div>
                </div>
              </div>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-3 rounded-lg bg-green-100">
                  <FiCheck className="w-6 h-6 text-green-600" />
                </div>
                <div className="ml-4">
                  <h3 className="text-sm font-medium text-gray-500">Approval Rate</h3>
                  <div className="text-2xl font-bold text-green-600">
                    {summary.total > 0 ? Math.round((summary.approved / summary.total) * 100) : 0}%
                  </div>
                </div>
              </div>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-3 rounded-lg bg-red-100">
                  <FiX className="w-6 h-6 text-red-600" />
                </div>
                <div className="ml-4">
                  <h3 className="text-sm font-medium text-gray-500">Rejection Rate</h3>
                  <div className="text-2xl font-bold text-red-600">
                    {summary.total > 0 ? Math.round((summary.rejected / summary.total) * 100) : 0}%
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default AttendanceHistory;