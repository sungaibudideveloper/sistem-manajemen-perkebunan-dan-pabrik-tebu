import React, { useState } from 'react';
import { PageProps } from '@inertiajs/core';
import LayoutMandor from '../components/layout-mandor';
import DashboardMandor from './dashboard-mandor';
import AbsenMandor from './absen-mandor';
import DataCollectionMandor from './data-collection-mandor';

// TypeScript Interfaces
interface User {
  id: number;
  name: string;
  email: string;
}

interface Stats {
  total_workers: number;
  productivity: string;
  active_areas: number;
  monitoring: string;
}

interface AttendanceSummary {
  name: string;
  time: string;
  status: string;
  status_color: string;
  id: number;
  initials: string;
}

interface AttendanceStats {
  today_total: number;
  present: number;
  late: number;
  absent: number;
  percentage_present: number;
}

interface FieldActivity {
  type: string;
  location: string;
  time: string;
  status: string;
  icon: string;
}

interface CollectionStat {
  title: string;
  desc: string;
  stats: string;
  icon: string;
  gradient: string;
}

interface Routes {
  logout: string;
  home: string;
  mandor_index: string;
  // API routes untuk absensi
  workers: string;
  attendance_today: string;
  process_checkin: string;
}

interface MandorIndexProps extends PageProps {
  title: string;
  user: User;
  routes: Routes;
  csrf_token: string;
  initialData: {
    stats: Stats;
    attendance_summary: AttendanceSummary[];
    attendance_stats: AttendanceStats;
    field_activities: FieldActivity[];
    collection_stats: CollectionStat[];
  };
}

const MandorIndex: React.FC<MandorIndexProps> = ({ 
  user, 
  routes, 
  csrf_token,
  initialData 
}) => {
  const [activeSection, setActiveSection] = useState('dashboard');

  const handleSectionChange = (section: string) => {
    setActiveSection(section);
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
          stats={initialData.stats}
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
          field_activities={initialData.field_activities}
          collection_stats={initialData.collection_stats}
          routes={routes} // Kirim seluruh routes object
          csrf_token={csrf_token}
          onSectionChange={handleSectionChange}
        />
      )}
    </LayoutMandor>
  );
};

export default MandorIndex;