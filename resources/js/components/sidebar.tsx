// resources/js/components/sidebar.tsx - GENERIC VERSION

import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { router } from '@inertiajs/react';
import {
  FiLogOut, FiHome, FiX, FiArrowRight
} from 'react-icons/fi';
import { usePage } from '@inertiajs/react';

interface User {
  id: number;
  name: string;
  email: string;
  userid: string;
  companycode: string;
  company_name: string;
}

interface SharedProps {
  app: {
    name: string;
    url: string;
    logo_url: string;
  };
  [key: string]: any;
}

interface MenuItem {
  id: string;
  icon: React.ComponentType<{ className?: string }>;
  label: string;
  description?: string;
}

interface SidebarProps {
  isOpen: boolean;
  onClose: () => void;
  activeSection: string;
  onSectionChange: (section: string) => void;
  user: User;
  csrf_token: string;
  routes: {
    logout: string;
    [key: string]: string; // This allows any additional route
  };
  // NEW: Customization props
  menuItems: MenuItem[];
  title?: string;
  subtitle?: string;
  theme?: 'mandor' | 'approver' | 'default';
  roleLabel?: string;
}

const Sidebar: React.FC<SidebarProps> = ({ 
  isOpen, 
  onClose, 
  activeSection, 
  onSectionChange,
  user,
  csrf_token,
  routes,
  menuItems,
  title = "SB TEBU APPS",
  subtitle = "Sistem Koleksi Data",
  theme = "default",
  roleLabel
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

  // Simple colors - SAMA UNTUK SEMUA
  const themeColors = {
    sidebarBg: 'bg-black',
    headerBorder: 'border-neutral-800',
    footerBorder: 'border-neutral-800',
    activeItemBg: 'bg-white',
    activeItemText: 'text-black',
    inactiveItemText: 'text-white',
    hoverItemBg: 'hover:bg-neutral-800',
    avatarBg: 'bg-white',
    avatarText: 'text-black',
    userInfoText: 'text-white'
  };

  return (
    <>
      {/* Mobile backdrop */}
      {isOpen && (
        <div 
          className="fixed inset-0 bg-black/50 z-40 md:hidden"
          onClick={onClose}
        />
      )}

      {/* Sidebar */}
      <div className={`
        fixed top-0 left-0 z-50 h-full w-64 ${themeColors.sidebarBg} transform transition-transform duration-300 ease-in-out
        ${isOpen ? 'translate-x-0' : '-translate-x-full'}
        md:translate-x-0 md:static md:shadow-none shadow-xl
      `}>
        <div className="h-full flex flex-col">
          {/* Header */}
          <div className={`p-6 border-b ${themeColors.headerBorder} relative`}>
            {/* Close button for mobile */}
            <button
              onClick={onClose}
              className={`absolute top-4 right-4 p-2 rounded-lg ${themeColors.hoverItemBg} transition-colors md:hidden`}
            >
              <FiX className={`w-4 h-4 ${themeColors.inactiveItemText}`} />
            </button>

            <div className={`${themeColors.inactiveItemText} flex items-center gap-3`}>
              <div className="p-2 rounded-lg">
                <img src={app.logo_url} alt="Logo Tebu" className="w-8 h-8 object-contain" />
              </div>
              <div>
                <span className="font-light tracking-wide">{title}</span>
                {subtitle && (
                  <p className="text-xs opacity-75 mt-1">{subtitle}</p>
                )}
              </div>
            </div>
          </div>
          
          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-1">
            {menuItems.map((item) => (
              <motion.button
                key={item.id}
                whileHover={{ x: 4 }}
                whileTap={{ scale: 0.98 }}
                onClick={() => handleSectionChange(item.id)}
                className={`w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all ${
                  activeSection === item.id
                    ? `${themeColors.activeItemBg} ${themeColors.activeItemText}`
                    : `${themeColors.inactiveItemText} ${themeColors.hoverItemBg}`
                }`}
              >
                <item.icon className="w-5 h-5 flex-shrink-0" />
                <div className="flex-1 text-left">
                  <div className="font-medium text-sm">{item.label}</div>
                  {item.description && (
                    <div className="text-xs opacity-75 mt-0.5">{item.description}</div>
                  )}
                </div>
                {activeSection === item.id && (
                  <FiArrowRight className="w-4 h-4 ml-auto" />
                )}
              </motion.button>
            ))}
          </nav>

          {/* User Info Section */}
          <div className={`p-4 border-t ${themeColors.footerBorder}`}>
            <div className="bg-white/10 rounded-xl p-4 mb-4">
              <div className="flex items-center gap-3">
                <div className="flex-1 min-w-0">
                  <p className={`${themeColors.userInfoText} font-medium text-sm truncate`}>
                    {user?.userid} - {user?.name}
                  </p>
                  <div className={`flex items-center gap-1 ${themeColors.userInfoText} opacity-75 text-xs mt-1`}>
                    <FiHome className="w-3 h-3 flex-shrink-0" />
                    <span className="truncate">{user?.company_name || user?.companycode}</span>
                  </div>
                  {roleLabel && (
                    <div className={`${themeColors.userInfoText} opacity-75 text-xs mt-1`}>
                      {roleLabel}
                    </div>
                  )}
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
                  : `${themeColors.inactiveItemText} hover:bg-red-900/50 hover:text-red-100`
              }`}
            >
              <FiLogOut className={`w-5 h-5 ${isLoggingOut ? 'animate-spin' : ''}`} />
              <span className="font-medium">{isLoggingOut ? 'Logging out...' : 'Logout'}</span>
            </motion.button>
          </div>
        </div>
      </div>
    </>
  );
};

export default Sidebar;