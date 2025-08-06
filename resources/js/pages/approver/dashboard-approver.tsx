// resources/js/pages/approver/dashboard-approver.tsx

import React, { useState, useEffect } from 'react';
import {
  FiClock, FiCheck, FiX, FiUsers, FiRefreshCw, FiTrendingUp, FiArrowRight
} from 'react-icons/fi';

interface DashboardStats {
  pending_count: number;
  approved_today: number;
  rejected_today: number;
  total_workers_today: number;
  mandor_count: number;
}

interface DashboardApproverProps {
  onSectionChange: (section: string) => void;
  routes: {
    [key: string]: string;
  };
  csrf_token: string;
}

const DashboardApprover: React.FC<DashboardApproverProps> = ({
  onSectionChange,
  routes,
  csrf_token
}) => {
  const [stats, setStats] = useState<DashboardStats>({
    pending_count: 0,
    approved_today: 0,
    rejected_today: 0,
    total_workers_today: 0,
    mandor_count: 0
  });
  const [isLoading, setIsLoading] = useState(true);
  const [currentDate] = useState(new Date().toISOString().split('T')[0]);

  useEffect(() => {
    loadDashboardData();
    
    // Auto refresh every 30 seconds
    const interval = setInterval(loadDashboardData, 30000);
    return () => clearInterval(interval);
  }, []);

  const loadDashboardData = async () => {
    setIsLoading(true);
    try {
      // Mock data untuk sekarang - nanti bisa diganti dengan API calls
      setTimeout(() => {
        setStats({
          pending_count: 3,
          approved_today: 8,
          rejected_today: 1,
          total_workers_today: 45,
          mandor_count: 4
        });
        setIsLoading(false);
      }, 800);
      
    } catch (error) {
      console.error('Error loading dashboard data:', error);
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

  return (
    <div className="min-h-screen bg-white">
      <div className="max-w-7xl mx-auto px-6 py-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center justify-between mb-6">
            <div>
              <h1 className="text-3xl font-bold text-black mb-2">Dashboard</h1>
              <p className="text-neutral-600">{formatDate(currentDate)}</p>
            </div>
            <button
              onClick={loadDashboardData}
              className="flex items-center gap-2 px-4 py-2 text-neutral-700 border border-neutral-300 rounded-lg hover:bg-neutral-50 transition-colors"
            >
              <FiRefreshCw className="w-4 h-4" />
              <span className="text-sm">Refresh</span>
            </button>
          </div>

          {/* Today's Summary - Minimal List */}
          <div className="bg-neutral-50 rounded-lg border border-neutral-200 p-6 mb-8">
            <h3 className="text-lg font-semibold text-black mb-4">Ringkasan Hari Ini</h3>
            <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
              <div className="flex items-center gap-3">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                    <FiClock className="w-4 h-4 text-black" />
                  </div>
                </div>
                <div>
                  <div className="text-sm text-neutral-600">Pending</div>
                  {isLoading ? (
                    <div className="animate-pulse bg-neutral-200 h-5 w-6 rounded"></div>
                  ) : (
                    <div className="text-lg font-semibold text-black">{stats.pending_count}</div>
                  )}
                </div>
              </div>

              <div className="flex items-center gap-3">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                    <FiCheck className="w-4 h-4 text-black" />
                  </div>
                </div>
                <div>
                  <div className="text-sm text-neutral-600">Approved</div>
                  {isLoading ? (
                    <div className="animate-pulse bg-neutral-200 h-5 w-6 rounded"></div>
                  ) : (
                    <div className="text-lg font-semibold text-black">{stats.approved_today}</div>
                  )}
                </div>
              </div>

              <div className="flex items-center gap-3">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                    <FiX className="w-4 h-4 text-black" />
                  </div>
                </div>
                <div>
                  <div className="text-sm text-neutral-600">Rejected</div>
                  {isLoading ? (
                    <div className="animate-pulse bg-neutral-200 h-5 w-6 rounded"></div>
                  ) : (
                    <div className="text-lg font-semibold text-black">{stats.rejected_today}</div>
                  )}
                </div>
              </div>

              <div className="flex items-center gap-3">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                    <FiUsers className="w-4 h-4 text-black" />
                  </div>
                </div>
                <div>
                  <div className="text-sm text-neutral-600">Workers</div>
                  {isLoading ? (
                    <div className="animate-pulse bg-neutral-200 h-5 w-8 rounded"></div>
                  ) : (
                    <div className="text-lg font-semibold text-black">{stats.total_workers_today}</div>
                  )}
                </div>
              </div>

              <div className="flex items-center gap-3">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 bg-neutral-100 rounded-lg flex items-center justify-center">
                    <FiTrendingUp className="w-4 h-4 text-black" />
                  </div>
                </div>
                <div>
                  <div className="text-sm text-neutral-600">Mandor</div>
                  {isLoading ? (
                    <div className="animate-pulse bg-neutral-200 h-5 w-4 rounded"></div>
                  ) : (
                    <div className="text-lg font-semibold text-black">{stats.mandor_count}</div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Feature Cards - EXACT SAME as Mandor */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 pt-16 pb-24">
          {/* Pending Approval Card */}
          <div
            onClick={() => onSectionChange('approval')}
            className="group cursor-pointer"
          >
            <div className="relative h-64 overflow-hidden rounded-2xl bg-gradient-to-br from-neutral-900 to-neutral-700 hover:-translate-y-2 transition-transform duration-300 border-2 border-neutral-300 hover:border-neutral-400 shadow-lg hover:shadow-xl">
              <div className="absolute inset-0 bg-gradient-to-br from-transparent to-black/50" />
              
              {/* Pattern overlay */}
              <div className="absolute inset-0 opacity-10">
                <div className="absolute inset-0" 
                  style={{
                    backgroundImage: `linear-gradient(45deg, transparent 48%, white 49%, white 51%, transparent 52%)`,
                    backgroundSize: '20px 20px'
                  }}
                />
              </div>

              <div className="relative h-full flex flex-col justify-between p-8">
                <div>
                  <div className="inline-flex p-3 bg-white/10 backdrop-blur rounded-2xl mb-4">
                    <FiClock className="w-8 h-8 text-neutral" />
                  </div>
                  <h3 className="text-2xl font-bold text-neutral mb-2">
                    Pending Approval
                  </h3>
                  <p className="text-neutral-200">
                    Review and approve attendance records
                  </p>
                </div>
                
                <div className="flex items-center text-white/80 group-hover:text-white transition-colors">
                  <span className="text-sm font-medium">Review Now</span>
                  <FiArrowRight className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                </div>
              </div>
            </div>
          </div>

          {/* Approval History Card */}
          <div
            onClick={() => onSectionChange('history')}
            className="group cursor-pointer"
          >
            <div className="relative h-64 overflow-hidden rounded-2xl bg-gradient-to-br from-neutral-800 to-neutral-600 hover:-translate-y-2 transition-transform duration-300 border-2 border-neutral-300 hover:border-neutral-400 shadow-lg hover:shadow-xl">
              <div className="absolute inset-0 bg-gradient-to-br from-transparent to-black/50" />
              
              {/* Dots pattern */}
              <div className="absolute inset-0 opacity-10">
                <div className="absolute inset-0"
                  style={{
                    backgroundImage: `radial-gradient(circle, white 1px, transparent 1px)`,
                    backgroundSize: '30px 30px'
                  }}
                />
              </div>

              <div className="relative h-full flex flex-col justify-between p-8">
                <div>
                  <div className="inline-flex p-3 bg-white/10 backdrop-blur rounded-2xl mb-4">
                    <FiCheck className="w-8 h-8 text-neutral" />
                  </div>
                  <h3 className="text-2xl font-bold text-neutral mb-2">
                    Approval History
                  </h3>
                  <p className="text-neutral-200">
                    View processed attendance records
                  </p>
                </div>
                
                <div className="flex items-center text-white/80 group-hover:text-white transition-colors">
                  <span className="text-sm font-medium">View History</span>
                  <FiArrowRight className="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DashboardApprover;