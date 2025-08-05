// resources\js\components\loading-spinner.tsx
import React from 'react';

interface LoadingSpinnerProps {
  size?: 'sm' | 'md' | 'lg';
  text?: string;
  color?: 'blue' | 'gray' | 'black' | 'green' | 'orange';
  center?: boolean;
}

const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({ 
  size = 'md', 
  text, 
  color = 'blue',
  center = true 
}) => {
  // Size variants
  const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-8 w-8', 
    lg: 'h-12 w-12'
  };

  // Color variants
  const colorClasses = {
    blue: 'border-gray-300 border-t-blue-500',
    gray: 'border-gray-300 border-t-gray-500',
    black: 'border-gray-300 border-t-black',
    green: 'border-gray-300 border-t-green-500',
    orange: 'border-gray-300 border-t-orange-500'
  };

  const containerClass = center ? 'flex flex-col items-center justify-center' : 'flex items-center gap-2';
  const textMargin = center ? 'mt-3' : 'ml-2';

  return (
    <div className={containerClass}>
      <div 
        className={`rounded-full border-2 ${sizeClasses[size]} ${colorClasses[color]} animate-spin`}
      />
      {text && (
        <p className={`text-gray-500 text-sm ${textMargin}`}>
          {text}
        </p>
      )}
    </div>
  );
};

// Preset components untuk use case umum
export const LoadingCard: React.FC<{ text?: string }> = ({ text = "Memuat data..." }) => (
  <div className="p-8 text-center">
    <LoadingSpinner text={text} />
  </div>
);

export const LoadingInline: React.FC<{ text?: string; color?: 'blue' | 'gray' | 'black' | 'green' | 'orange' }> = ({ 
  text, 
  color = 'blue' 
}) => (
  <LoadingSpinner size="sm" text={text} color={color} center={false} />
);

export const LoadingOverlay: React.FC<{ text?: string }> = ({ text = "Memuat..." }) => (
  <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div className="bg-white rounded-xl p-6 text-center max-w-sm mx-4">
      <LoadingSpinner color="black" />
      <p className="text-black font-medium mt-4">{text}</p>
    </div>
  </div>
);

export default LoadingSpinner;