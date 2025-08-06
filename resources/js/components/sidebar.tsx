// resources\js\components\sidebar.tsx
import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { router } from '@inertiajs/react';
import {
  FiGrid, FiCheckCircle, FiClipboard, FiArrowRight, FiLogOut, FiHome
} from 'react-icons/fi';
import { usePage } from '@inertiajs/react';

interface User {
  id: number;
  name: string;
  email: string;
  userid: string; // Mandor code like M002
  companycode: string;
  company_name: string; // From JOIN with company table
}

interface SharedProps {
  app: {
    name: string;
    url: string;
    logo_url: string;
  };
  [key: string]: any;
}

interface ExtendedRoutes {
  logout: string;
  home: string;
  mandor_index: string;
  workers: string;
  attendance_today: string;
  process_checkin: string;
}

interface SidebarProps {
  isOpen: boolean;
  onClose: () => void;
  activeSection: string;
  onSectionChange: (section: string) => void;
  user: User;
  csrf_token: string;
  routes: ExtendedRoutes;
}

const Sidebar: React.FC<SidebarProps> = ({ 
  isOpen, 
  onClose, 
  activeSection, 
  onSectionChange,
  user,
  csrf_token,
  routes 
}) => {
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const { app } = usePage<SharedProps>().props;

  const handleLogout = () => {
    if (isLoggingOut) return;
    
    setIsLoggingOut(true);
    
    router.post(routes.logout, {
      _token: csrf_token
    }, {
      preserveState: false,
      preserveScroll: false,
      replace: true,
      onError: () => {
        setIsLoggingOut(false);
      }
    });
  };

  const handleSectionChange = (sectionId: string) => {
    onClose(); // Always close sidebar first
    onSectionChange(sectionId); // Handle internal SPA navigation
  };

  // Generate user initials from name
  const getUserInitials = (name: string): string => {
    return name ? name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2) : 'UN';
  };

  return (
    <div className={`fixed inset-y-0 left-0 z-50 w-72 bg-black transform transition-transform duration-300 ${
      isOpen ? 'translate-x-0' : '-translate-x-full'
    }`}>
      <div className="h-full flex flex-col">
        {/* Header */}
        <div className="p-6 border-b border-neutral-800">
          <div className="text-white flex items-center gap-3">
            <div className="p-2 rounded-lg">
              <img src={app.logo_url} alt="Logo Tebu" className="w-8 h-8 object-contain" />
            </div>
            <span className="font-light tracking-wide">SB TEBU APPS</span>
          </div>
        </div>
        
        {/* Navigation */}
        <nav className="flex-1 p-4 space-y-1">
          {[
            { id: 'dashboard', icon: FiGrid, label: 'Beranda' },
            { id: 'absensi', icon: FiCheckCircle, label: 'Absensi' },
            { id: 'data-collection', icon: FiClipboard, label: 'Koleksi Data' },
          ].map((item) => (
            <motion.button
              key={item.id}
              whileHover={{ x: 4 }}
              whileTap={{ scale: 0.98 }}
              onClick={() => handleSectionChange(item.id)}
              className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${
                activeSection === item.id
                  ? "bg-white text-black"
                  : "text-white hover:bg-neutral-800 hover:text-white"
              }`}
            >
              <item.icon className="w-5 h-5" />
              <span className="font-medium">{item.label}</span>
              {activeSection === item.id && (
                <FiArrowRight className="w-4 h-4 ml-auto" />
              )}
            </motion.button>
          ))}
        </nav>

        {/* FIXED: Mandor Info Section */}
        <div className="p-4 border-t border-neutral-800">
          <div className="bg-white-900 rounded-xl p-4 mb-4">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-white rounded-full flex items-center justify-center text-black text-sm font-medium">
                {getUserInitials(user?.name || '')}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-white font-medium text-sm truncate">
                  {user?.userid} - {user?.name}
                </p>
                <div className="flex items-center gap-1 text-white text-xs mt-1">
                  <FiHome className="w-3 h-3 flex-shrink-0" />
                  <span className="truncate">{user?.company_name || user?.companycode}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Logout Button */}
          <motion.button
            onClick={handleLogout}
            disabled={isLoggingOut}
            whileHover={{ x: 4 }}
            whileTap={{ scale: 0.98 }}
            className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${
              isLoggingOut 
                ? 'text-neutral-400 cursor-not-allowed' 
                : 'text-white hover:bg-red-900/50 hover:text-red-100'
            }`}
          >
            <FiLogOut className={`w-5 h-5 ${isLoggingOut ? 'animate-spin' : ''}`} />
            <span className="font-medium">{isLoggingOut ? 'Logging out...' : 'Logout'}</span>
          </motion.button>
        </div>
      </div>
    </div>
  );
};

export default Sidebar;