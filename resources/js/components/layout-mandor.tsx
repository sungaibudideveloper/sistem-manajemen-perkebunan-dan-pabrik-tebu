// resources/js/components/layout-mandor.tsx - UPDATED with Footer

import React, { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import Header from './header'; // Generic Header
import Sidebar from './sidebar'; // Generic Sidebar
import Footer from './footer'; // NEW: Generic Footer
import {
  FiGrid, FiCheckCircle, FiClipboard
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

interface ExtendedRoutes {
  logout: string;
  home: string;
  mandor_index: string;
  workers: string;
  attendance_today: string;
  process_checkin: string;
  [key: string]: string; // This allows any additional route
}

interface LayoutMandorProps {
  user: User;
  routes: ExtendedRoutes;
  csrf_token?: string;
  activeSection: string;
  onSectionChange: (section: string) => void;
  children: React.ReactNode;
}

const LayoutMandor: React.FC<LayoutMandorProps> = ({
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

  // Define menu items specific to Mandor (used by Sidebar)
  const mandorMenuItems = [
    {
      id: 'dashboard',
      icon: FiGrid,
      label: 'Beranda',
      description: 'Ringkasan aktivitas'
    },
    {
      id: 'absensi',
      icon: FiCheckCircle,
      label: 'Absensi',
      description: 'Pencatatan kehadiran'
    },
    {
      id: 'data-collection',
      icon: FiClipboard,
      label: 'Koleksi Data',
      description: 'Input hasil kerja'
    }
  ];

  return (
    <div 
      className="min-h-screen bg-white"
      style={{
        position: 'relative',
        overflow: 'hidden',
        width: '100vw',
        maxWidth: '100%'
      }}
    >
      {/* Sidebar - Desktop Navigation */}
      <Sidebar
        isOpen={sidebarOpen}
        onClose={() => setSidebarOpen(false)}
        activeSection={activeSection}
        onSectionChange={onSectionChange}
        user={user}
        csrf_token={csrf_token}
        routes={routes}
        menuItems={mandorMenuItems}
        title="SB TEBU APPS"
        subtitle="Sistem Koleksi Data Lapangan"
        roleLabel="Mandor"
      />
      
      {/* Header */}
      <Header
        onMenuClick={() => setSidebarOpen(true)}
        user={user}
        isOnline={isOnline}
        currentTime={currentTime}
        csrf_token={csrf_token}
        routes={routes}
        title="SB Tebu Apps"
        subtitle="Sistem Koleksi Data Lapangan"
      />
      
      {/* Main Content */}
      <main className="md:ml-64 min-h-screen bg-gray-50">
        <div className="pb-20 md:pb-0">
          {children}
        </div>
      </main>
      
      {/* Footer - Mobile Navigation */}
      <Footer
        user={user}
        activeSection={activeSection}
        onSectionChange={onSectionChange}
        variant="mobile"
        theme="mandor"
      />
    </div>
  );
};

export default LayoutMandor;