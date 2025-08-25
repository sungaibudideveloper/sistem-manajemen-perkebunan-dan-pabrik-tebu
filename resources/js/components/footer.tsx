import React from 'react';
import { motion } from 'framer-motion';
import {
  FiHome, FiCamera, FiClipboard
} from 'react-icons/fi';

interface User {
  id: number;
  name: string;
  email: string;
  userid: string;
  companycode: string;
  company_name: string;
}

interface MenuItem {
  id: string;
  icon: React.ComponentType<{ className?: string }>;
  label: string;
  description?: string;
}

interface FooterProps {
  user: User;
  activeSection: string;
  onSectionChange: (section: string) => void;
  menuItems?: MenuItem[];
  variant?: 'mobile' | 'desktop' | 'both';
  theme?: 'mandor' | 'approver' | 'default';
}

const Footer: React.FC<FooterProps> = ({ 
  activeSection, 
  onSectionChange,
  variant = 'mobile',
  theme = 'default'
}) => {
  const footerMenuItems = [
    {
      id: 'absensi',
      icon: FiCamera,
      label: 'Absensi',
      isHero: false
    },
    {
      id: 'dashboard',
      icon: FiHome,
      label: '',
      isHero: true
    },
    {
      id: 'data-collection',
      icon: FiClipboard,
      label: 'Koleksi Data',
      isHero: false
    }
  ];

  const heroItem = footerMenuItems.find(item => item.isHero);
  const regularItems = footerMenuItems.filter(item => !item.isHero);

  if (variant === 'desktop') {
    return null;
  }

  const handleItemClick = (itemId: string) => {
    onSectionChange(itemId);
  };

  return (
    <>
      <div className={`
        fixed bottom-0 left-0 right-0 z-40 
        ${variant === 'mobile' ? 'block md:hidden' : 'block'}
      `}>
        {/* Hero Button - Positioned above footer */}
        {heroItem && (
          <div 
            className="absolute z-50" 
            style={{ 
                left: '50%', 
                transform: 'translateX(-50%)', 
                top: '-30px' 
            }}
            >
            <motion.button
              onClick={() => handleItemClick(heroItem.id)}
              className="relative flex flex-col items-center cursor-pointer"
              whileTap={{ scale: 0.92 }}
              whileHover={{ scale: 1.05 }}
              transition={{ type: "spring", stiffness: 400, damping: 25 }}
            >
              {/* Hero Icon Container */}
              <motion.div
                className="relative z-10 mb-1 flex items-center justify-center"
                animate={{
                  scale: activeSection === heroItem.id ? 1.15 : 1,
                  rotate: activeSection === heroItem.id ? [0, -5, 5, 0] : 0
                }}
                transition={{
                  scale: { type: "spring", stiffness: 300, damping: 20 },
                  rotate: { duration: 0.5, ease: "easeInOut" }
                }}
              >
                <div className="flex items-center justify-center rounded-full bg-gray-40 p-2">
                  <div className="flex items-center justify-center rounded-full bg-black w-16 h-16">
                    <heroItem.icon className="w-6 h-6 text-white" />
                  </div>
                </div>
              </motion.div>

              {/* Hero Button Shadow */}
              <motion.div
                className={`
                  w-16 h-3 rounded-full
                  transition-all duration-300
                  ${activeSection === heroItem.id 
                    ? 'bg-black/10 shadow-md' 
                    : 'bg-gray-300/50'
                  }
                `}
                animate={{
                  scale: activeSection === heroItem.id ? 1.1 : 1,
                  opacity: activeSection === heroItem.id ? 1 : 0.7
                }}
                transition={{ type: "spring", stiffness: 300, damping: 20 }}
              />
            </motion.button>
          </div>
        )}

        {/* Footer Container */}
        <div className="relative bg-white shadow-[0_-10px_40px_rgba(0,0,0,0.1)]">
          <div className="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent" />
          
          <div className="flex items-end justify-center px-4 pt-6 pb-4">
            {/* Left Item */}
            <div className="flex-1 flex justify-center">
              {regularItems[0] && (
                <FooterMenuItem 
                  item={regularItems[0]} 
                  isActive={activeSection === regularItems[0].id}
                  onClick={() => handleItemClick(regularItems[0].id)}
                />
              )}
            </div>

            {/* Center Space for Hero Button */}
            <div className="flex-1 flex justify-center">
              <div className="w-16 h-12" />
            </div>

            {/* Right Item */}
            <div className="flex-1 flex justify-center">
              {regularItems[1] && (
                <FooterMenuItem 
                  item={regularItems[1]} 
                  isActive={activeSection === regularItems[1].id}
                  onClick={() => handleItemClick(regularItems[1].id)}
                />
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Spacer */}
      <div className={`h-24 ${variant === 'mobile' ? 'block md:hidden' : 'block'}`} />
    </>
  );
};

// Separate component for regular footer menu items
interface FooterMenuItemProps {
  item: {
    id: string;
    icon: React.ComponentType<{ className?: string }>;
    label: string;
  };
  isActive: boolean;
  onClick: () => void;
}

const FooterMenuItem: React.FC<FooterMenuItemProps> = ({ item, isActive, onClick }) => {
  return (
    <motion.button
      onClick={onClick}
      className="relative flex flex-col items-center justify-center py-1.5 px-3 rounded-xl transition-all duration-300 group cursor-pointer"
      whileTap={{ scale: 0.95 }}
      transition={{ type: "spring", stiffness: 400, damping: 30 }}
    >
      {isActive && (
        <motion.div
          initial={{ opacity: 0, scale: 0.8 }}
          animate={{ opacity: 1, scale: 1 }}
          className="absolute inset-0 rounded-xl bg-gray-100"
        />
      )}
      
      <motion.div
        className={`relative z-10 transition-colors duration-300 ${
          isActive ? 'text-black' : 'text-gray-500'
        }`}
        animate={{
          scale: isActive ? 1.1 : 1,
          y: isActive ? -1 : 0
        }}
        transition={{ type: "spring", stiffness: 300, damping: 20 }}
      >
        <item.icon 
          className="w-5 h-5 mb-0.5" 
        />
      </motion.div>
      
      <span 
        className={`
          relative z-10 text-[10px] font-medium tracking-wide
          transition-all duration-300
          ${isActive 
            ? 'text-black opacity-100' 
            : 'text-gray-500 opacity-70 group-hover:opacity-90'
          }
        `}
      >
        {item.label}
      </span>
      
      <motion.div
        className={`
          absolute -bottom-0.5 w-1 h-1 rounded-full
          transition-all duration-300
          ${isActive 
            ? 'bg-black opacity-100' 
            : 'bg-transparent opacity-0'
          }
        `}
        animate={{
          scale: isActive ? [1, 1.5, 1] : 0
        }}
        transition={{
          duration: 0.6,
          ease: "easeInOut"
        }}
      />
    </motion.button>
  );
};

export default Footer;