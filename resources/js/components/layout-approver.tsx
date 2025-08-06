// resources/js/components/layout-approver.tsx - FIXED to remove attendance_detail dependency
import React, { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import Header from './header'; // Generic Header
import Sidebar from './sidebar'; // Generic Sidebar
import {
  FiHome, FiCheck, FiClock
} from 'react-icons/fi';

// User interface
interface User {
  id: number;
  name: string;
  email: string;
  userid: string;
  companycode: string;
  company_name: string;
}

interface SharedProps {
  csrf_token: string;
  [key: string]: any;
}

// FIXED: Remove attendance_detail from ExtendedRoutes
interface ExtendedRoutes {
  logout: string;
  home: string;
  approver_index: string;
  pending_attendance: string;
  // REMOVED: attendance_detail: string;
  approve_attendance: string;
  reject_attendance: string;
  attendance_history: string;
  mandors_pending: string; // Added this route that's actually used
  [key: string]: string; // This allows any additional route
}

interface LayoutApproverProps {
  user: User;
  routes: ExtendedRoutes;
  csrf_token?: string;
  activeSection: string;
  onSectionChange: (section: string) => void;
  children: React.ReactNode;
}

const LayoutApprover: React.FC<LayoutApproverProps> = ({
  user,
  routes,
  csrf_token: propsCsrfToken,
  activeSection,
  onSectionChange,
  children
}) => {
  const [isOnline, setIsOnline] = useState(navigator.onLine);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [currentTime, setCurrentTime] = useState(new Date());
 
  // Get csrf_token from shared props as fallback
  const { csrf_token: pageCsrfToken } = usePage<SharedProps>().props;
  const csrf_token = propsCsrfToken || pageCsrfToken;

  useEffect(() => {
    // Online/Offline detection
    const handleOnline = () => setIsOnline(true);
    const handleOffline = () => setIsOnline(false);
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    // Clock update
    const clockInterval = setInterval(() => {
      setCurrentTime(new Date());
    }, 1000);

    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
      clearInterval(clockInterval);
    };
  }, []);

  // Define menu items specific to Approver
  const approverMenuItems = [
    {
      id: 'dashboard',
      icon: FiHome,
      label: 'Dashboard',
      description: 'Ringkasan sistem approval'
    },
    {
      id: 'approval',
      icon: FiClock,
      label: 'Pending Approval',
      description: 'Absensi menunggu persetujuan'
    },
    {
      id: 'history',
      icon: FiCheck,
      label: 'Riwayat Approval',
      description: 'Riwayat approval & reject'
    }
  ];

  return (
    <div className="min-h-screen bg-white">
      <Sidebar
        isOpen={sidebarOpen}
        onClose={() => setSidebarOpen(false)}
        activeSection={activeSection}
        onSectionChange={onSectionChange}
        user={user}
        csrf_token={csrf_token}
        routes={routes}
        menuItems={approverMenuItems}
        title="SB TEBU APPS"
        subtitle="Sistem Approval"
        roleLabel="Absen Approver"
      />
     
      <Header
        onMenuClick={() => setSidebarOpen(true)}
        user={user}
        isOnline={isOnline}
        currentTime={currentTime}
        csrf_token={csrf_token}
        routes={routes}
        title="SB Tebu Apps"
        subtitle="Sistem Approval Absensi"
      />
     
      <main>
        {children}
      </main>
    </div>
  );
};

export default LayoutApprover;