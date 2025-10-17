// resources/js/pages/approver/dashboard-approver.tsx - WITH HADIR/LOKASI STATS

import React, { useState, useEffect } from 'react';
import {
  FiClock, FiCheck, FiX, FiUsers, FiRefreshCw, FiTrendingUp, FiArrowRight, 
  FiAlertCircle, FiHome, FiMapPin
} from 'react-icons/fi';

interface DashboardStats {
  pending_count: number;
  approved_today: number;
  rejected_today: number;
  total_workers_today: number;
  mandor_count: number;
  // NEW: HADIR/LOKASI breakdown
  hadir_pending?: number;
  lokasi_pending?: number;
  hadir_approved?: number;
  lokasi_approved?: number;
  hadir_rejected?: number;
  lokasi_rejected?: number;
}

interface ApiResponse {
  success: boolean;
  date: string;
  date_formatted: string;
  stats: DashboardStats;
  generated_at: string;
}

interface DashboardApproverProps {
  onSectionChange: (section: string) => void;
  routes: {
    dashboard_stats: string;
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
    mandor_count: 0,
    hadir_pending: 0,
    lokasi_pending: 0,
    hadir_approved: 0,
    lokasi_approved: 0,
    hadir_rejected: 0,
    lokasi_rejected: 0
  });
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [lastUpdated, setLastUpdated] = useState<string | null>(null);
  const [currentDate] = useState(new Date().toISOString().split('T')[0]);

  useEffect(() => {
    loadDashboardData();
    
    const interval = setInterval(loadDashboardData, 30000);
    return () => clearInterval(interval);
  }, []);

  const loadDashboardData = async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      const response = await fetch(routes.dashboard_stats, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf_token,
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data: ApiResponse = await response.json();
      
      if (data.success) {
        setStats(data.stats);
        setLastUpdated(new Date().toLocaleTimeString('id-ID', { 
          hour: '2-digit', 
          minute: '2-digit' 
        }));
      } else {
        throw new Error('API returned unsuccessful response');
      }
      
    } catch (error) {
      console.error('Error loading dashboard data:', error);
      setError(error instanceof Error ? error.message : 'Failed to load dashboard data');
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

  return (
    <div style={{ minHeight: '100vh', backgroundColor: 'white' }}>
      <div style={{ maxWidth: '1280px', margin: '0 auto', padding: '32px 24px' }}>
        {/* Header */}
        <div style={{ marginBottom: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '24px' }}>
            <div>
              <h1 style={{ fontSize: '30px', fontWeight: 'bold', color: 'black', marginBottom: '8px' }}>
                Dashboard
              </h1>
              <p style={{ color: '#737373' }}>{formatDate(currentDate)}</p>
              {lastUpdated && (
                <p style={{ fontSize: '14px', color: '#a3a3a3', marginTop: '4px' }}>
                  Terakhir diperbarui: {lastUpdated}
                </p>
              )}
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              {error && (
                <div style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  gap: '8px', 
                  padding: '8px 12px', 
                  color: '#b91c1c', 
                  backgroundColor: '#fef2f2', 
                  border: '1px solid #fecaca', 
                  borderRadius: '8px' 
                }}>
                  <FiAlertCircle style={{ width: '16px', height: '16px' }} />
                  <span style={{ fontSize: '14px' }}>Error loading data</span>
                </div>
              )}
              <button
                onClick={loadDashboardData}
                disabled={isLoading}
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '8px',
                  padding: '8px 16px',
                  color: '#525252',
                  border: '1px solid #d4d4d4',
                  backgroundColor: 'white',
                  borderRadius: '8px',
                  cursor: isLoading ? 'not-allowed' : 'pointer',
                  opacity: isLoading ? 0.5 : 1,
                  transition: 'background-color 0.2s'
                }}
              >
                <FiRefreshCw style={{ width: '16px', height: '16px' }} className={isLoading ? 'animate-spin' : ''} />
                <span style={{ fontSize: '14px' }}>Refresh</span>
              </button>
            </div>
          </div>

          {/* Today's Summary */}
          <div style={{ 
            backgroundColor: '#fafafa', 
            borderRadius: '8px', 
            border: '1px solid #e5e5e5', 
            padding: '24px', 
            marginBottom: '32px' 
          }}>
            <h3 style={{ fontSize: '18px', fontWeight: '600', color: 'black', marginBottom: '16px' }}>
              Ringkasan Hari Ini
            </h3>
            <div style={{ 
              display: 'grid', 
              gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', 
              gap: '16px' 
            }}>
              {/* Pending */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <div style={{ flexShrink: 0 }}>
                  <div style={{ 
                    width: '32px', 
                    height: '32px', 
                    backgroundColor: '#f5f5f5', 
                    borderRadius: '8px', 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center' 
                  }}>
                    <FiClock style={{ width: '16px', height: '16px', color: 'black' }} />
                  </div>
                </div>
                <div>
                  <div style={{ fontSize: '14px', color: '#737373' }}>Pending</div>
                  {isLoading ? (
                    <div style={{ 
                      animation: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite', 
                      backgroundColor: '#e5e5e5', 
                      height: '20px', 
                      width: '24px', 
                      borderRadius: '4px' 
                    }} />
                  ) : (
                    <div style={{ fontSize: '18px', fontWeight: '600', color: 'black' }}>
                      {stats.pending_count}
                    </div>
                  )}
                </div>
              </div>

              {/* Approved */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <div style={{ flexShrink: 0 }}>
                  <div style={{ 
                    width: '32px', 
                    height: '32px', 
                    backgroundColor: '#f5f5f5', 
                    borderRadius: '8px', 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center' 
                  }}>
                    <FiCheck style={{ width: '16px', height: '16px', color: 'black' }} />
                  </div>
                </div>
                <div>
                  <div style={{ fontSize: '14px', color: '#737373' }}>Approved</div>
                  {isLoading ? (
                    <div style={{ 
                      animation: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite', 
                      backgroundColor: '#e5e5e5', 
                      height: '20px', 
                      width: '24px', 
                      borderRadius: '4px' 
                    }} />
                  ) : (
                    <div style={{ fontSize: '18px', fontWeight: '600', color: 'black' }}>
                      {stats.approved_today}
                    </div>
                  )}
                </div>
              </div>

              {/* Rejected */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <div style={{ flexShrink: 0 }}>
                  <div style={{ 
                    width: '32px', 
                    height: '32px', 
                    backgroundColor: '#f5f5f5', 
                    borderRadius: '8px', 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center' 
                  }}>
                    <FiX style={{ width: '16px', height: '16px', color: 'black' }} />
                  </div>
                </div>
                <div>
                  <div style={{ fontSize: '14px', color: '#737373' }}>Rejected</div>
                  {isLoading ? (
                    <div style={{ 
                      animation: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite', 
                      backgroundColor: '#e5e5e5', 
                      height: '20px', 
                      width: '24px', 
                      borderRadius: '4px' 
                    }} />
                  ) : (
                    <div style={{ fontSize: '18px', fontWeight: '600', color: 'black' }}>
                      {stats.rejected_today}
                    </div>
                  )}
                </div>
              </div>

              {/* Workers */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <div style={{ flexShrink: 0 }}>
                  <div style={{ 
                    width: '32px', 
                    height: '32px', 
                    backgroundColor: '#f5f5f5', 
                    borderRadius: '8px', 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center' 
                  }}>
                    <FiUsers style={{ width: '16px', height: '16px', color: 'black' }} />
                  </div>
                </div>
                <div>
                  <div style={{ fontSize: '14px', color: '#737373' }}>Workers</div>
                  {isLoading ? (
                    <div style={{ 
                      animation: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite', 
                      backgroundColor: '#e5e5e5', 
                      height: '20px', 
                      width: '32px', 
                      borderRadius: '4px' 
                    }} />
                  ) : (
                    <div style={{ fontSize: '18px', fontWeight: '600', color: 'black' }}>
                      {stats.total_workers_today}
                    </div>
                  )}
                </div>
              </div>

              {/* Mandor */}
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <div style={{ flexShrink: 0 }}>
                  <div style={{ 
                    width: '32px', 
                    height: '32px', 
                    backgroundColor: '#f5f5f5', 
                    borderRadius: '8px', 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center' 
                  }}>
                    <FiTrendingUp style={{ width: '16px', height: '16px', color: 'black' }} />
                  </div>
                </div>
                <div>
                  <div style={{ fontSize: '14px', color: '#737373' }}>Mandor</div>
                  {isLoading ? (
                    <div style={{ 
                      animation: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite', 
                      backgroundColor: '#e5e5e5', 
                      height: '20px', 
                      width: '16px', 
                      borderRadius: '4px' 
                    }} />
                  ) : (
                    <div style={{ fontSize: '18px', fontWeight: '600', color: 'black' }}>
                      {stats.mandor_count}
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* NEW: HADIR vs LOKASI Breakdown */}
            <div style={{ 
              marginTop: '24px', 
              paddingTop: '24px', 
              borderTop: '1px solid #e5e5e5',
              display: 'grid',
              gridTemplateColumns: 'repeat(2, 1fr)',
              gap: '16px'
            }}>
              {/* HADIR */}
              <div style={{ 
                backgroundColor: 'white', 
                padding: '16px', 
                borderRadius: '8px',
                border: '1px solid #e5e5e5'
              }}>
                <div style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  gap: '8px',
                  marginBottom: '12px'
                }}>
                  <FiHome style={{ width: '16px', height: '16px', color: '#2563eb' }} />
                  <span style={{ fontSize: '14px', fontWeight: '600', color: '#2563eb' }}>HADIR</span>
                </div>
                <div style={{ display: 'flex', gap: '16px', fontSize: '14px' }}>
                  <div>
                    <div style={{ color: '#737373' }}>Pending</div>
                    <div style={{ fontWeight: '600', color: 'black' }}>{stats.hadir_pending || 0}</div>
                  </div>
                  <div>
                    <div style={{ color: '#737373' }}>Approved</div>
                    <div style={{ fontWeight: '600', color: 'black' }}>{stats.hadir_approved || 0}</div>
                  </div>
                  <div>
                    <div style={{ color: '#737373' }}>Rejected</div>
                    <div style={{ fontWeight: '600', color: 'black' }}>{stats.hadir_rejected || 0}</div>
                  </div>
                </div>
              </div>

              {/* LOKASI */}
              <div style={{ 
                backgroundColor: 'white', 
                padding: '16px', 
                borderRadius: '8px',
                border: '1px solid #e5e5e5'
              }}>
                <div style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  gap: '8px',
                  marginBottom: '12px'
                }}>
                  <FiMapPin style={{ width: '16px', height: '16px', color: '#7c3aed' }} />
                  <span style={{ fontSize: '14px', fontWeight: '600', color: '#7c3aed' }}>LOKASI</span>
                </div>
                <div style={{ display: 'flex', gap: '16px', fontSize: '14px' }}>
                  <div>
                    <div style={{ color: '#737373' }}>Pending</div>
                    <div style={{ fontWeight: '600', color: 'black' }}>{stats.lokasi_pending || 0}</div>
                  </div>
                  <div>
                    <div style={{ color: '#737373' }}>Approved</div>
                    <div style={{ fontWeight: '600', color: 'black' }}>{stats.lokasi_approved || 0}</div>
                  </div>
                  <div>
                    <div style={{ color: '#737373' }}>Rejected</div>
                    <div style={{ fontWeight: '600', color: 'black' }}>{stats.lokasi_rejected || 0}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Feature Cards */}
        <div style={{ 
          display: 'grid', 
          gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', 
          gap: '32px', 
          paddingTop: '64px', 
          paddingBottom: '96px' 
        }}>
          {/* Pending Approval Card */}
          <div
            onClick={() => onSectionChange('approval')}
            style={{ cursor: 'pointer' }}
          >
            <div style={{ 
              position: 'relative', 
              height: '256px', 
              overflow: 'hidden', 
              borderRadius: '16px', 
              background: 'linear-gradient(to bottom right, #171717, #404040)',
              border: '2px solid #d4d4d4',
              boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
              transition: 'transform 0.3s, box-shadow 0.3s',
              transform: 'translateY(0)'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'translateY(-8px)';
              e.currentTarget.style.boxShadow = '0 10px 15px rgba(0,0,0,0.2)';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'translateY(0)';
              e.currentTarget.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            }}
            >
              <div style={{ 
                position: 'absolute', 
                inset: 0, 
                background: 'linear-gradient(to bottom right, transparent, rgba(0,0,0,0.5))' 
              }} />
              
              <div style={{ 
                position: 'absolute', 
                inset: 0, 
                opacity: 0.1,
                backgroundImage: 'linear-gradient(45deg, transparent 48%, white 49%, white 51%, transparent 52%)',
                backgroundSize: '20px 20px'
              }} />

              <div style={{ 
                position: 'relative', 
                height: '100%', 
                display: 'flex', 
                flexDirection: 'column', 
                justifyContent: 'space-between', 
                padding: '32px' 
              }}>
                <div>
                  <div style={{ 
                    display: 'inline-flex', 
                    padding: '12px', 
                    backgroundColor: 'rgba(255,255,255,0.1)', 
                    backdropFilter: 'blur(10px)', 
                    borderRadius: '16px', 
                    marginBottom: '16px' 
                  }}>
                    <FiClock style={{ width: '32px', height: '32px', color: '#f5f5f5' }} />
                  </div>
                  <h3 style={{ fontSize: '24px', fontWeight: 'bold', color: '#f5f5f5', marginBottom: '8px' }}>
                    Pending Approval
                  </h3>
                  <p style={{ color: '#e5e5e5' }}>
                    Review and approve attendance records
                  </p>
                </div>
                
                <div style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  color: 'rgba(255,255,255,0.8)',
                  transition: 'color 0.2s'
                }}>
                  <span style={{ fontSize: '14px', fontWeight: '500' }}>Review Now</span>
                  <FiArrowRight style={{ 
                    width: '16px', 
                    height: '16px', 
                    marginLeft: '8px',
                    transition: 'transform 0.2s'
                  }} />
                </div>
              </div>
            </div>
          </div>

          {/* Approval History Card */}
          <div
            onClick={() => onSectionChange('history')}
            style={{ cursor: 'pointer' }}
          >
            <div style={{ 
              position: 'relative', 
              height: '256px', 
              overflow: 'hidden', 
              borderRadius: '16px', 
              background: 'linear-gradient(to bottom right, #262626, #525252)',
              border: '2px solid #d4d4d4',
              boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
              transition: 'transform 0.3s, box-shadow 0.3s',
              transform: 'translateY(0)'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'translateY(-8px)';
              e.currentTarget.style.boxShadow = '0 10px 15px rgba(0,0,0,0.2)';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'translateY(0)';
              e.currentTarget.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            }}
            >
              <div style={{ 
                position: 'absolute', 
                inset: 0, 
                background: 'linear-gradient(to bottom right, transparent, rgba(0,0,0,0.5))' 
              }} />
              
              <div style={{ 
                position: 'absolute', 
                inset: 0, 
                opacity: 0.1,
                backgroundImage: 'radial-gradient(circle, white 1px, transparent 1px)',
                backgroundSize: '30px 30px'
              }} />

              <div style={{ 
                position: 'relative', 
                height: '100%', 
                display: 'flex', 
                flexDirection: 'column', 
                justifyContent: 'space-between', 
                padding: '32px' 
              }}>
                <div>
                  <div style={{ 
                    display: 'inline-flex', 
                    padding: '12px', 
                    backgroundColor: 'rgba(255,255,255,0.1)', 
                    backdropFilter: 'blur(10px)', 
                    borderRadius: '16px', 
                    marginBottom: '16px' 
                  }}>
                    <FiCheck style={{ width: '32px', height: '32px', color: '#f5f5f5' }} />
                  </div>
                  <h3 style={{ fontSize: '24px', fontWeight: 'bold', color: '#f5f5f5', marginBottom: '8px' }}>
                    Approval History
                  </h3>
                  <p style={{ color: '#e5e5e5' }}>
                    View processed attendance records
                  </p>
                </div>
                
                <div style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  color: 'rgba(255,255,255,0.8)',
                  transition: 'color 0.2s'
                }}>
                  <span style={{ fontSize: '14px', fontWeight: '500' }}>View History</span>
                  <FiArrowRight style={{ 
                    width: '16px', 
                    height: '16px', 
                    marginLeft: '8px',
                    transition: 'transform 0.2s'
                  }} />
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