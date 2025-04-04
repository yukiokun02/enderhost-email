
import React from 'react';

const EnderLogo: React.FC = () => {
  return (
    <div className="flex items-center gap-2">
      <img 
        src="/lovable-uploads/98b15cac-cd24-4c6d-956b-5e305cdcfcfa.png" 
        alt="EnderHOST Logo" 
        className="h-12 w-auto"
      />
      <span className="font-bold text-2xl bg-clip-text text-transparent bg-gradient-to-r from-enderhost-purple to-enderhost-blue">
        EnderHOST
      </span>
    </div>
  );
};

export default EnderLogo;
