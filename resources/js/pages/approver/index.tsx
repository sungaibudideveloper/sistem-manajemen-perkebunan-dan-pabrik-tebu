// resources/js/pages/approver/index.tsx - UPDATED for new route structure
import React, { useState, useEffect } from 'react';
import { PageProps } from '@inertiajs/core';
import LayoutApprover from '../../components/layout-approver';
import DashboardApprover from './dashboard-approver';
import AttendanceApproval from './attendance-approval';
import AttendanceHistory from './attendance-history';

interface User {
  id: number;
  name: string;
  email: string;
  userid: string;
  companycode: string;
  company_name: string;
}

interface Routes {
  logout: string;
  home: string;
  approver_index: string;
  dashboard_stats: string;
  pending_attendance: string;
  approve_attendance: string;
  reject_attendance: string;
  attendance_history: string;
  mandors_pending: string;
  [key: string]: string;
}

interface ApproverIndexProps extends PageProps {
  title: string;
  user: User;
  routes: Routes;
  csrf_token: string;
}

const ApproverIndex: React.FC<ApproverIndexProps> = ({
  user,
  routes,
  csrf_token
}) => {
  const getInitialSection = (): string => {
    if (window.location.hash) {
      const hashSection = window.location.hash.substring(1);
      if (['dashboard', 'approval', 'history'].includes(hashSection)) {
        return hashSection;
      }
    }
    
    const savedSection = localStorage.getItem('approver_active_section');
    if (savedSection && ['dashboard', 'approval', 'history'].includes(savedSection)) {
      return savedSection;
    }
    
    return 'dashboard';
  };

  const [activeSection, setActiveSection] = useState<string>(getInitialSection);

  const handleSectionChange = (section: string) => {
    setActiveSection(section);
    localStorage.setItem('approver_active_section', section);
    window.history.replaceState(null, '', `#${section}`);
  };

  useEffect(() => {
    const handleHashChange = () => {
      const hashSection = window.location.hash.substring(1);
      if (['dashboard', 'approval', 'history'].includes(hashSection)) {
        setActiveSection(hashSection);
        localStorage.setItem('approver_active_section', hashSection);
      }
    };

    if (!window.location.hash && activeSection) {
      window.history.replaceState(null, '', `#${activeSection}`);
    }

    window.addEventListener('hashchange', handleHashChange);
    
    return () => {
      window.removeEventListener('hashchange', handleHashChange);
    };
  }, [activeSection]);

  const handleLogout = () => {
    localStorage.removeItem('approver_active_section');
    window.location.href = routes.logout;
  };

  return (
    <LayoutApprover
      user={user}
      routes={routes}
      csrf_token={csrf_token}
      activeSection={activeSection}
      onSectionChange={handleSectionChange}
    >
      {activeSection === 'dashboard' && (
        <DashboardApprover
          onSectionChange={handleSectionChange}
          routes={{
            dashboard_stats: routes.dashboard_stats
          }}
          csrf_token={csrf_token}
        />
      )}
      
      {activeSection === 'approval' && (
        <AttendanceApproval
          routes={{
            pending_attendance: routes.pending_attendance,
            mandors_pending: routes.mandors_pending,
            approve_attendance: routes.approve_attendance,
            reject_attendance: routes.reject_attendance
          }}
          csrf_token={csrf_token}
          onSectionChange={handleSectionChange}
        />
      )}
      
      {activeSection === 'history' && (
        <AttendanceHistory
          routes={{
            attendance_history: routes.attendance_history
          }}
          csrf_token={csrf_token}
          onSectionChange={handleSectionChange}
        />
      )}
    </LayoutApprover>
  );
};

export default ApproverIndex;