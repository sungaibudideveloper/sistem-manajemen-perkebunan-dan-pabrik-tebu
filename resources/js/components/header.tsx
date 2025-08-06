// resources/js/components/header.tsx - GENERIC VERSION

import React, { useState } from 'react';
import { useSpring, animated } from '@react-spring/web';
import { router } from '@inertiajs/react';
import {
  FiGrid, FiClock, FiWifi, FiWifiOff, FiBell, FiLogOut
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

interface HeaderProps {
  onMenuClick: () => void;
  user: User;
  isOnline: boolean;
  currentTime: Date;
  csrf_token: string;
  routes: {
    logout: string;
    [key: string]: string; // This allows any additional route
  };
  // NEW: Optional customization
  title?: string;
  subtitle?: string;
  theme?: 'mandor' | 'approver' | 'default';
}

const Header: React.FC<HeaderProps> = ({ 
  onMenuClick, 
  user, 
  isOnline, 
  currentTime,
  csrf_token,
  routes,
  title = "SB Tebu Apps",
  subtitle = "Sistem Koleksi Data Lapangan",
  theme = "default"
}) => {
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const { app } = usePage<SharedProps>().props;

  const scrollProgress = useSpring({
    from: { width: '0%' },
    to: { width: '100%' },
    config: { duration: 1000 }
  });

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

  // Generate user initials from name
  const getUserInitials = (name: string): string => {
    return name ? name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2) : 'UN';
  };

  // Simple colors - SAMA UNTUK SEMUA
  const themeColors = {
    avatarBg: 'bg-blue-600',
    gradientFrom: 'from-neutral-400',
    gradientTo: 'to-neutral-600'
  };

  return (
    <header className="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-neutral-200">
      <div className="relative">
        <animated.div 
          style={scrollProgress}
          className={`absolute top-0 left-0 h-0.5 bg-gradient-to-r ${themeColors.gradientFrom} ${themeColors.gradientTo}`}
        />
        
        <div className="flex items-center justify-between h-16 px-6">
          <div className="flex items-center gap-4">
            <button
              onClick={onMenuClick}
              className="p-2 hover:bg-neutral-100 rounded-lg transition-colors"
            >
              <FiGrid className="w-5 h-5" />
            </button>
            
            <div className="hidden md:flex items-center gap-3">
              <div className="p-2 rounded-lg">
                <img src={app.logo_url} alt="Logo Tebu" className="w-8 h-8 object-contain" />
              </div>
              <div>
                <h1 className="text-sm font-medium">{title}</h1>
                <p className="text-xs text-neutral-500">{subtitle}</p>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-3">
            {/* Status Badge */}
            <div className={`flex items-center gap-1.5 px-3 py-1 rounded-full border text-sm ${
              isOnline 
                ? "text-green-700 border-green-200 bg-green-50" 
                : "text-red-700 border-red-200 bg-red-50"
            }`}>
              {isOnline ? <FiWifi className="w-3 h-3" /> : <FiWifiOff className="w-3 h-3" />}
              <span className="hidden sm:inline">{isOnline ? 'Terhubung' : 'Terputus'}</span>
            </div>

            {/* Time */}
            <div className="hidden md:flex items-center gap-2 text-neutral-600">
              <FiClock className="w-4 h-4" />
              <span className="text-sm font-mono">
                {currentTime.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
              </span>
            </div>

            {/* Notifications */}
            <button className="relative p-2 hover:bg-neutral-100 rounded-lg transition-colors">
              <FiBell className="w-5 h-5" />
              <span className="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full" />
            </button>

            {/* User Info */}
            <div className="flex items-center gap-3">
              {/* User Info - Show full name on desktop */}
              <div className="text-right">
                <p className="text-sm font-semibold text-neutral-900">
                  {user?.userid} - {user?.name}
                </p>
                <p className="text-xs text-neutral-500">
                  {user?.company_name || user?.companycode}
                </p>
              </div>
              
              {/* Avatar with theme color */}
              <div className={`w-8 h-8 ${themeColors.avatarBg} rounded-full flex items-center justify-center text-white text-sm font-medium`}>
                {getUserInitials(user?.name || '')}
              </div>
              
              {/* Logout Button */}
              <button
                onClick={handleLogout}
                disabled={isLoggingOut}
                className={`p-2 hover:bg-red-50 rounded-lg transition-colors ${
                  isLoggingOut 
                    ? 'text-neutral-400 cursor-not-allowed' 
                    : 'text-neutral-600 hover:text-red-600'
                }`}
                title={isLoggingOut ? 'Logging out...' : 'Logout'}
              >
                <FiLogOut className={`w-5 h-5 ${isLoggingOut ? 'animate-spin' : ''}`} />
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;