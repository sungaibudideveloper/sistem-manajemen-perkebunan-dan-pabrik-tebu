// resources\js\pages\index.tsx - FIXED VERSION

import React, { useState } from 'react';
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

// FIXED: Routes interface with index signature
interface Routes {
  logout: string;
  home: string;
  mandor_index: string;
  // API routes untuk absensi
  workers: string;
  attendance_today: string;
  process_checkin: string;
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
  const [activeSection, setActiveSection] = useState('dashboard');

  const handleSectionChange = (section: string) => {
    setActiveSection(section);
  };

  // Removed console.log to fix spam

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
            process_checkin: routes.process_checkin
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