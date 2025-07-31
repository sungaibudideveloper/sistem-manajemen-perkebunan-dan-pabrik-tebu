import React, { useState } from 'react';
import { useSpring, animated } from '@react-spring/web';
import { router } from '@inertiajs/react';
import {
  FiGrid, FiClock, FiWifi, FiWifiOff, FiBell, FiLogOut
} from 'react-icons/fi';

interface User {
  id: number;
  name: string;
  email: string;
}

interface HeaderProps {
  onMenuClick: () => void;
  user: User;
  isOnline: boolean;
  currentTime: Date;
  csrf_token: string; // Add this
  routes: {
    logout: string;
    home: string;
    mandor_dashboard: string;
    mandor_field_data: string;
  };
}

const Header: React.FC<HeaderProps> = ({ 
  onMenuClick, 
  user, 
  isOnline, 
  currentTime,
  csrf_token,
  routes 
}) => {
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  const scrollProgress = useSpring({
    from: { width: '0%' },
    to: { width: '100%' },
    config: { duration: 1000 }
  });

  const handleLogout = () => {
    if (isLoggingOut) return;
    
    setIsLoggingOut(true);
    
    // POST dengan CSRF token
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

  return (
    <header className="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-neutral-200">
      <div className="relative">
        <animated.div 
          style={scrollProgress}
          className="absolute top-0 left-0 h-0.5 bg-gradient-to-r from-neutral-400 to-neutral-600"
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
                <img src="/tebu/public/img/logo-tebu.png" alt="Logo Tebu" className="w-8 h-8 object-contain" />
              </div>
              <div>
                <h1 className="text-sm font-medium">SB Tebu Apps</h1>
                <p className="text-xs text-neutral-500">Sistem Koleksi Data Lapangan</p>
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

            {/* User & Logout */}
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 bg-neutral-200 rounded-full flex items-center justify-center text-neutral-700 text-sm font-medium">
                {user?.name ? user.name.split(' ').map((n: string) => n[0]).join('').toUpperCase() : 'UN'}
              </div>
              
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