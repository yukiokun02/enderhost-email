
import React from 'react';
import OrderForm from '@/components/OrderForm';
import EnderLogo from '@/components/EnderLogo';

const Index = () => {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">
      <div className="glass-card p-6 sm:p-8 w-full max-w-md mx-auto animate-pulse-border">
        <div className="flex flex-col items-center space-y-6">
          <EnderLogo />
          
          <div className="text-center space-y-2">
            <h1 className="text-2xl font-bold text-white">Minecraft Server Order</h1>
            <p className="text-gray-400 text-sm">
              Complete the form to send server details to your customer
            </p>
          </div>
          
          <OrderForm />
          
          <div className="text-xs text-gray-500 text-center pt-4">
            <p>All credentials will be emailed to the customer</p>
            <p>Order includes 30 days of hosting from creation date</p>
            <a 
              href="https://www.enderhost.in" 
              target="_blank" 
              rel="noopener noreferrer"
              className="text-enderhost-blue hover:text-enderhost-purple transition-colors"
            >
              www.enderhost.in
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Index;
