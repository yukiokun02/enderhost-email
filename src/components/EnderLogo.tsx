
import React from 'react';

const EnderLogo: React.FC = () => {
  return (
    <div className="flex items-center gap-2">
      <div className="relative w-10 h-10 flex items-center justify-center">
        <div className="absolute inset-0 bg-gradient-to-r from-enderhost-purple to-enderhost-blue opacity-50 blur-md rounded-lg"></div>
        <div className="relative z-10 text-white font-bold text-2xl">E</div>
      </div>
      <span className="font-bold text-2xl bg-clip-text text-transparent bg-gradient-to-r from-enderhost-purple to-enderhost-blue">
        EnderHOST
      </span>
    </div>
  );
};

export default EnderLogo;
