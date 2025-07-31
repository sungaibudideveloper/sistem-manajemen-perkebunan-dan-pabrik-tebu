import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { router } from '@inertiajs/react';

interface SplashScreenProps {
  baseUrl?: string;
}

const SplashScreen: React.FC<SplashScreenProps> = ({ baseUrl }) => {
  const [showLoading, setShowLoading] = useState(true);

  // Fungsi untuk mendapatkan URL yang benar
  const getDashboardUrl = () => {
    if (baseUrl) {
      return `${baseUrl}/mandor/dashboard`;
    }
    
    // Fallback: deteksi otomatis dari window.location
    const currentPath = window.location.pathname;
    const basePath = currentPath.includes('/tebu/public') 
      ? '/tebu/public' 
      : '';
    
    return `${basePath}/mandor/dashboard`;
  };

  useEffect(() => {
    const timer = setTimeout(() => {
      setShowLoading(false);
      // Redirect ke dashboard mandor setelah animasi selesai
      router.visit(getDashboardUrl());
    }, 10000);

    return () => {
      clearTimeout(timer);
    };
  }, []);

  if (!showLoading) {
    return null;
  }

  return (
    <div className="min-h-screen bg-white flex items-center justify-center relative overflow-hidden">
      {/* Animated Background Elements */}
      <div className="absolute inset-0">
        {/* Floating Circles */}
        <motion.div
          animate={{ 
            x: [0, 100, 0],
            y: [0, -50, 0],
            scale: [1, 1.2, 1]
          }}
          transition={{ 
            duration: 8, 
            repeat: Infinity, 
            ease: "easeInOut" 
          }}
          className="absolute top-20 left-20 w-32 h-32 bg-gray-100 rounded-full opacity-30"
        />
        
        <motion.div
          animate={{ 
            x: [0, -80, 0],
            y: [0, 80, 0],
            scale: [1, 0.8, 1]
          }}
          transition={{ 
            duration: 6, 
            repeat: Infinity, 
            ease: "easeInOut",
            delay: 1
          }}
          className="absolute top-1/3 right-20 w-24 h-24 bg-gray-200 rounded-full opacity-25"
        />
        
        <motion.div
          animate={{ 
            x: [0, 60, 0],
            y: [0, -100, 0],
            scale: [1, 1.5, 1]
          }}
          transition={{ 
            duration: 10, 
            repeat: Infinity, 
            ease: "easeInOut",
            delay: 2
          }}
          className="absolute bottom-1/4 left-1/3 w-20 h-20 bg-gray-150 rounded-full opacity-20"
        />
        
        {/* Geometric Shapes */}
        <motion.div
          animate={{ 
            rotate: [0, 360],
            scale: [1, 1.1, 1]
          }}
          transition={{ 
            duration: 12, 
            repeat: Infinity, 
            ease: "linear" 
          }}
          className="absolute top-1/2 left-10 w-16 h-16 border-2 border-gray-300 opacity-20"
          style={{ transform: 'rotate(45deg)' }}
        />
        
        <motion.div
          animate={{ 
            rotate: [360, 0],
            x: [0, 50, 0]
          }}
          transition={{ 
            duration: 15, 
            repeat: Infinity, 
            ease: "easeInOut",
            delay: 3
          }}
          className="absolute bottom-20 right-1/4 w-12 h-12 border-2 border-gray-400 rounded-full opacity-15"
        />
        
        {/* Grid Pattern */}
        <motion.div
          animate={{ 
            opacity: [0.1, 0.3, 0.1]
          }}
          transition={{ 
            duration: 4, 
            repeat: Infinity, 
            ease: "easeInOut" 
          }}
          className="absolute inset-0 opacity-10"
          style={{
            backgroundImage: `linear-gradient(rgba(0,0,0,0.05) 1px, transparent 1px),
                            linear-gradient(90deg, rgba(0,0,0,0.05) 1px, transparent 1px)`,
            backgroundSize: '50px 50px'
          }}
        />
        
        {/* Subtle Lines */}
        <motion.div
          animate={{ 
            scaleX: [0, 1, 0],
            opacity: [0, 0.3, 0]
          }}
          transition={{ 
            duration: 3, 
            repeat: Infinity, 
            ease: "easeInOut",
            delay: 5
          }}
          className="absolute top-1/4 left-0 w-full h-px bg-gray-300"
        />
        
        <motion.div
          animate={{ 
            scaleY: [0, 1, 0],
            opacity: [0, 0.2, 0]
          }}
          transition={{ 
            duration: 4, 
            repeat: Infinity, 
            ease: "easeInOut",
            delay: 7
          }}
          className="absolute top-0 right-1/3 w-px h-full bg-gray-300"
        />
      </div>
      
      {/* Main Loading Content */}
      <div className="text-center relative z-10">
        {/* Plant Logo */}
        <motion.div
          initial={{ scale: 0 }}
          animate={{ scale: 1 }}
          transition={{ delay: 0.5, duration: 1, ease: "easeOut" }}
          className="mb-8"
        >
          <svg width="80" height="80" viewBox="0 0 80 80" className="mx-auto">
            {/* Stem */}
            <motion.line
              x1="40" y1="70" x2="40" y2="35"
              stroke="black" strokeWidth="3" strokeLinecap="round"
              initial={{ pathLength: 0 }}
              animate={{ pathLength: 1 }}
              transition={{ delay: 1, duration: 1 }}
            />
            
            {/* Left Leaf */}
            <motion.path
              d="M40 45 Q25 35 15 40 Q20 50 40 45"
              fill="none" stroke="black" strokeWidth="2" strokeLinecap="round"
              initial={{ pathLength: 0, opacity: 0 }}
              animate={{ pathLength: 1, opacity: 1 }}
              transition={{ delay: 1.5, duration: 0.8 }}
            />
            <motion.path
              d="M40 45 Q25 35 15 40 Q20 50 40 45"
              fill="rgba(0,0,0,0.1)"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 2, duration: 0.5 }}
            />
            
            {/* Right Leaf */}
            <motion.path
              d="M40 45 Q55 35 65 40 Q60 50 40 45"
              fill="none" stroke="black" strokeWidth="2" strokeLinecap="round"
              initial={{ pathLength: 0, opacity: 0 }}
              animate={{ pathLength: 1, opacity: 1 }}
              transition={{ delay: 1.8, duration: 0.8 }}
            />
            <motion.path
              d="M40 45 Q55 35 65 40 Q60 50 40 45"
              fill="rgba(0,0,0,0.1)"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 2.3, duration: 0.5 }}
            />
            
            {/* Small root lines */}
            <motion.line
              x1="40" y1="70" x2="35" y2="75"
              stroke="black" strokeWidth="1" strokeLinecap="round"
              initial={{ pathLength: 0 }}
              animate={{ pathLength: 1 }}
              transition={{ delay: 0.8, duration: 0.5 }}
            />
            <motion.line
              x1="40" y1="70" x2="45" y2="75"
              stroke="black" strokeWidth="1" strokeLinecap="round"
              initial={{ pathLength: 0 }}
              animate={{ pathLength: 1 }}
              transition={{ delay: 0.8, duration: 0.5 }}
            />
          </svg>
        </motion.div>
        
        <motion.h1
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ delay: 1 }}
          className="text-4xl font-bold text-black mb-4"
        >
          SB Tebu Apps
        </motion.h1>
        
        <motion.p
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ delay: 2 }}
          className="text-xl text-gray-600 mb-2"
        >
          New Technology
        </motion.p>
        
        <motion.p
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ delay: 3 }}
          className="text-xl text-gray-600 mb-8"
        >
          PWA Progressive Web App
        </motion.p>
        
        <motion.div
          initial={{ width: 0 }}
          animate={{ width: "100%" }}
          transition={{ delay: 4, duration: 4 }}
          className="h-2 bg-black rounded-full max-w-xs mx-auto"
        />
        
        <motion.p
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 8 }}
          className="text-sm text-gray-500 mt-4"
        >
          Created by Sungaibudi IT Team
        </motion.p>
      </div>
    </div>
  );
};

export default SplashScreen;