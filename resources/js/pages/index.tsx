// resources/js/pages/index.tsx - FIXED VERSION with Persistent State
import React, { useState, useEffect } from 'react';
import { PageProps } from '@inertiajs/core';
import LayoutMandor from '../components/layout-mandor';
import DashboardMandor from './dashboard-mandor';
import AbsenMandor from './absen-mandor';
import DataCollectionMandor from './data-collection-mandor';

// FIXED: Complete User interface
interface User {
  id: number;
  name: string;
  email: string;
  userid: string;
  companycode: string;
  company_name: string;
}

// FIXED: Routes interface with index signature - ADD missing routes
interface Routes {
  logout: string;
  home: string;
  mandor_index: string;
  // API routes untuk absensi - ADDED missing routes
  workers: string;
  attendance_today: string;
  process_checkin: string;
  update_photo: string;
  rejected_attendance: string;
  // Field Collection routes
  lkh_ready: string;
  materials_available: string;
  lkh_vehicle_info: string;
  lkh_assign: string;
  // Material management routes
  materials_save_returns: string;
  material_confirm_pickup: string;
  // Complete all LKH route
  complete_all_lkh: string;
  // Sync routes
  sync_offline_data: string;
  // Index signature for additional routes
  [key: string]: string;
}

interface MandorIndexProps extends PageProps {
  title: string;
  user: User;
  routes: Routes;
  csrf_token: string;
}

const MandorIndex: React.FC<MandorIndexProps> = ({
  user,
  routes,
  csrf_token
}) => {
  // UPDATED: Initialize state from localStorage or URL hash, fallback to 'dashboard'
  const getInitialSection = (): string => {
    // Method 1: Check URL hash first
    if (window.location.hash) {
      const hashSection = window.location.hash.substring(1);
      if (['dashboard', 'absensi', 'data-collection'].includes(hashSection)) {
        return hashSection;
      }
    }
    
    // Method 2: Check localStorage as fallback
    const savedSection = localStorage.getItem('mandor_active_section');
    if (savedSection && ['dashboard', 'absensi', 'data-collection'].includes(savedSection)) {
      return savedSection;
    }
    
    // Method 3: Default fallback
    return 'dashboard';
  };

  const [activeSection, setActiveSection] = useState<string>(getInitialSection);

  // UPDATED: Enhanced section change handler with persistence
  const handleSectionChange = (section: string) => {
    setActiveSection(section);
    
    // Save to localStorage
    localStorage.setItem('mandor_active_section', section);
    
    // Update URL hash without triggering page reload
    window.history.replaceState(null, '', `#${section}`);
  };

  // ADDED: Listen for browser back/forward navigation
  useEffect(() => {
    const handleHashChange = () => {
      const hashSection = window.location.hash.substring(1);
      if (['dashboard', 'absensi', 'data-collection'].includes(hashSection)) {
        setActiveSection(hashSection);
        localStorage.setItem('mandor_active_section', hashSection);
      }
    };

    // Set initial hash if not present
    if (!window.location.hash && activeSection) {
      window.history.replaceState(null, '', `#${activeSection}`);
    }

    // Listen for hash changes (back/forward buttons)
    window.addEventListener('hashchange', handleHashChange);
    
    return () => {
      window.removeEventListener('hashchange', handleHashChange);
    };
  }, [activeSection]);

  // ADDED: Clear localStorage on logout (optional)
  const handleLogout = () => {
    localStorage.removeItem('mandor_active_section');
    // Then proceed with normal logout
    window.location.href = routes.logout;
  };

  return (
    <LayoutMandor
      user={user}
      routes={routes}
      csrf_token={csrf_token}
      activeSection={activeSection}
      onSectionChange={handleSectionChange}
    >
      {activeSection === 'dashboard' && (
        <DashboardMandor
          onSectionChange={handleSectionChange}
        />
      )}

      {activeSection === 'absensi' && (
        <AbsenMandor
          routes={{
            workers: routes.workers,
            attendance_today: routes.attendance_today,
            process_checkin: routes.process_checkin,
            update_photo: routes.update_photo,
            rejected_attendance: routes.rejected_attendance
          }}
          csrf_token={csrf_token}
          onSectionChange={handleSectionChange}
        />
      )}

      {activeSection === 'data-collection' && (
        <DataCollectionMandor
          routes={routes}
          csrf_token={csrf_token}
          onSectionChange={handleSectionChange}
        />
      )}
    </LayoutMandor>
  );
};

export default MandorIndex;