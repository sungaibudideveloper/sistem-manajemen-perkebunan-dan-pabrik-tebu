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
}

interface MandorIndexProps extends PageProps {
  title: string;
  user: User;
  routes: Routes;
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
          attendance_summary={initialData.attendance_summary}
          attendance_stats={initialData.attendance_stats}
          onSectionChange={handleSectionChange}
        />
      )}
      {activeSection === 'data-collection' && (
        <DataCollectionMandor 
          field_activities={initialData.field_activities}
          collection_stats={initialData.collection_stats}
          onSectionChange={handleSectionChange}
        />
      )}
    </LayoutMandor>
  );
};

export default MandorIndex;