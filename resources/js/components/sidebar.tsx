import React from 'react';
import { motion } from 'framer-motion';
import { router } from '@inertiajs/react';
import {
  FiGrid, FiCheckCircle, FiClipboard, FiArrowRight, FiLogOut
} from 'react-icons/fi';

interface SidebarProps {
  isOpen: boolean;
  onClose: () => void;
  activeSection: string;
  onSectionChange: (section: string) => void;
  routes: {
    logout: string;
    home: string;
    mandor_dashboard: string;
    mandor_field_data: string;
  };
}

const Sidebar: React.FC<SidebarProps> = ({ 
  isOpen, 
  onClose, 
  activeSection, 
  onSectionChange,
  routes 
}) => {
  const handleLogout = () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = routes.logout;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      const tokenInput = document.createElement('input');
      tokenInput.type = 'hidden';
      tokenInput.name = '_token';
      tokenInput.value = csrfToken;
      form.appendChild(tokenInput);
    }
    
    document.body.appendChild(form);
    form.submit();
  };

  const handleSectionChange = (sectionId: string) => {
    if (sectionId === 'field-data') {
      // Navigate to separate field-data page using Laravel route
      router.visit(routes.mandor_field_data);
    } else {
      // Handle internal sections
      onSectionChange(sectionId);
      onClose();
    }
  };

  return (
    <div className={`fixed inset-y-0 left-0 z-50 w-72 bg-black transform transition-transform duration-300 ${
      isOpen ? 'translate-x-0' : '-translate-x-full'
    }`}>
      <div className="h-full flex flex-col">
        <div className="p-6 border-b border-neutral-800">
          <div className="text-white flex items-center gap-3">
            <div className="p-2 rounded-lg">
              <img src="/tebu/public/img/logo-tebu.png" alt="Logo Tebu" className="w-8 h-8 object-contain" />
            </div>
            <span className="font-light tracking-wide">SB TEBU APPS</span>
          </div>
        </div>
        
        <nav className="flex-1 p-4 space-y-1">
          {[
            { id: 'dashboard', icon: FiGrid, label: 'Beranda' },
            { id: 'absensi', icon: FiCheckCircle, label: 'Absensi' },
            { id: 'field-data', icon: FiClipboard, label: 'Koleksi Data' },
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

        <div className="p-4 border-t border-neutral-800">
          <motion.button
            onClick={handleLogout}
            whileHover={{ x: 4 }}
            whileTap={{ scale: 0.98 }}
            className="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-white hover:bg-red-900/50 hover:text-red-100 transition-all"
          >
            <FiLogOut className="w-5 h-5" />
            <span className="font-medium">Logout</span>
          </motion.button>
        </div>
      </div>
    </div>
  );
};

export default Sidebar;