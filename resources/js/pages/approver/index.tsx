// resources/js/pages/approver/index.tsx - FIXED
import React, { useState } from 'react';
import { PageProps } from '@inertiajs/core';
import LayoutApprover from '../../components/layout-approver';
import DashboardApprover from './dashboard-approver';
import AttendanceApproval from './attendance-approval';
import AttendanceHistory from './attendance-history';

// User interface
interface User {
  id: number;
  name: string;
  email: string;
  userid: string;
  companycode: string;
  company_name: string;
}

// Routes interface - FIXED: Remove attendance_detail
interface Routes {
  logout: string;
  home: string;
  approver_index: string;
  // Approval routes
  pending_attendance: string;
  approve_attendance: string;
  reject_attendance: string;
  attendance_history: string;
  mandors_pending: string;
  // Index signature for additional routes
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
  const [activeSection, setActiveSection] = useState('dashboard');

  const handleSectionChange = (section: string) => {
    setActiveSection(section);
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
          routes={routes}
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