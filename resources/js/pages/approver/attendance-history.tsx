// resources/js/pages/approver/attendance-history.tsx

import React, { useState, useEffect } from 'react';
import {
  FiArrowLeft, FiCheck, FiX, FiEye, FiCalendar, FiUser, FiFilter, FiRefreshCw
} from 'react-icons/fi';
import { LoadingCard, LoadingInline } from '../../components/loading-spinner';

interface HistoryRecord {
  absenno: string;
  mandorid: string;
  mandor_nama: string;
  totalpekerja: number;
  status: string;
  status_label: string;
  uploaddate: string;
  upload_date_formatted: string;
  processed_date: string;
  processed_date_formatted: string;
  updateBy: string;
}

interface AttendanceHistoryProps {
  routes: {
    attendance_history: string;
    attendance_detail: string;
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
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadHistoryData();
  }, [selectedDate, statusFilter]);

  const loadHistoryData = async () => {
    setIsLoading(true);
    try {
      const params = new URLSearchParams();
      if (selectedDate) params.append('date', selectedDate);
      if (statusFilter !== 'ALL') params.append('status', statusFilter);
      
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

  const handleViewDetail = (absenno: string) => {
    const url = routes.attendance_detail.replace('__ABSENNO__', absenno);
    window.open(url, '_blank');
  };

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
    approved: historyRecords.filter(r => r.status === 'APPROVED').length,
    rejected: historyRecords.filter(r => r.status === 'REJECTED').length,
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
              <h1 className="text-3xl font-bold text-black mb-2">Riwayat Approval</h1>
              <p className="text-gray-600">Histori approval dan reject absensi</p>
            </div>
            <button
              onClick={loadHistoryData}
              className="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors"
            >
              <FiRefreshCw className="w-4 h-4" />
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
                  <div key={`${record.absenno}-${record.processed_date}`} className="p-6 hover:bg-gray-50 transition-colors">
                    <div className="flex items-center justify-between">
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <h3 className="font-semibold text-black">{record.absenno}</h3>
                          {getStatusBadge(record.status)}
                        </div>
                        
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                          <div className="flex items-center gap-2">
                            <FiUser className="w-4 h-4" />
                            <span>{record.mandor_nama}</span>
                          </div>
                          <div>
                            <span className="font-medium">{record.totalpekerja}</span> pekerja
                          </div>
                          <div>
                            Upload: {record.upload_date_formatted}
                          </div>
                          <div className="md:col-span-2">
                            Diproses: {record.processed_date_formatted}
                          </div>
                          <div>
                            {record.status === 'APPROVED' ? 'Approved' : 'Rejected'} by: {record.updateBy?.split(' - ')[0]}
                          </div>
                        </div>
                        
                        {/* Rejection reason if available */}
                        {record.status === 'REJECTED' && record.updateBy?.includes('REJECT:') && (
                          <div className="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg">
                            <span className="text-xs text-red-600 font-medium">Alasan Penolakan:</span>
                            <p className="text-sm text-red-700 mt-1">
                              {record.updateBy.split('REJECT:')[1]?.trim()}
                            </p>
                          </div>
                        )}
                        
                        {/* Approval notes if available */}
                        {record.status === 'APPROVED' && record.updateBy?.includes(' - ') && !record.updateBy?.includes('REJECT:') && (
                          <div className="mt-2 p-2 bg-green-50 border border-green-200 rounded-lg">
                            <span className="text-xs text-green-600 font-medium">Catatan:</span>
                            <p className="text-sm text-green-700 mt-1">
                              {record.updateBy.split(' - ')[1]?.trim()}
                            </p>
                          </div>
                        )}
                      </div>
                      
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => handleViewDetail(record.absenno)}
                          className="flex items-center gap-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                        >
                          <FiEye className="w-4 h-4" />
                          <span className="text-sm">Detail</span>
                        </button>
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