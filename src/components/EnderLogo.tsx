
import React from 'react';
import { useLocation } from 'react-router-dom';

interface EnderLogoProps {
  width?: number;
  height?: number;
}

const EnderLogo: React.FC<EnderLogoProps> = ({ width = 40, height = 40 }) => {
  const location = useLocation();
  const isLoginPage = location.pathname === '/login';
  
  return (
    <div className={`flex items-center gap-2 px-3 py-1.5 ${isLoginPage ? '' : 'bg-gray-800/80 rounded-full transition-all duration-300 hover:bg-gray-700/80'} animate-fade-in`}>
      <img 
        src="/lovable-uploads/98b15cac-cd24-4c6d-956b-5e305cdcfcfa.png" 
        alt="EnderHOST Logo" 
        className="h-auto"
        style={{ width: `${width}px`, height: `${height}px` }}
      />
      <span className="font-bold text-2xl bg-clip-text text-transparent bg-gradient-to-r from-enderhost-purple to-enderhost-blue">
        EnderHOST
      </span>
    </div>
  );
};

export default EnderLogo;
