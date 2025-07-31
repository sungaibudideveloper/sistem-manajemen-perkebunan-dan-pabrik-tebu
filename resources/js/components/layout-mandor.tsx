import React, { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import Header from './header';
import Sidebar from './sidebar';

interface User {
  id: number;
  name: string;
  email: string;
}

interface SharedProps {
  csrf_token: string;
  [key: string]: any;
}

interface LayoutMandorProps {
  user: User;
  routes: {
    logout: string;
    home: string;
    mandor_dashboard: string;
    mandor_field_data: string;
  };
  activeSection: string;
  onSectionChange: (section: string) => void;
  children: React.ReactNode;
}

const LayoutMandor: React.FC<LayoutMandorProps> = ({
  user,
  routes,
  activeSection,
  onSectionChange,
  children
}) => {
  const [isOnline, setIsOnline] = useState(navigator.onLine);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [currentTime, setCurrentTime] = useState(new Date());
  
  // Get csrf_token from shared props
  const { csrf_token } = usePage<SharedProps>().props;

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

  // Backdrop for mobile sidebar
  const Backdrop = () => (
    sidebarOpen && (
      <div 
        className="fixed inset-0 bg-black/50 z-40 md:hidden"
        onClick={() => setSidebarOpen(false)}
      />
    )
  );

  return (
    <div className="min-h-screen bg-white">
      <Backdrop />
      
      <Sidebar
        isOpen={sidebarOpen}
        onClose={() => setSidebarOpen(false)}
        activeSection={activeSection}
        onSectionChange={onSectionChange}
        csrf_token={csrf_token}
        routes={routes}
      />
      
      <Header
        onMenuClick={() => setSidebarOpen(true)}
        user={user}
        isOnline={isOnline}
        currentTime={currentTime}
        csrf_token={csrf_token}
        routes={routes}
      />
      
      <main>
        {children}
      </main>
    </div>
  );
};

export default LayoutMandor;