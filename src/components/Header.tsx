
import React from 'react';
import { useAuth } from '@/hooks/useAuth';
import { Button } from '@/components/ui/button';
import EnderLogo from '@/components/EnderLogo';
import { LogOut, User, UserCog, Home } from 'lucide-react';
import { Link, useLocation } from 'react-router-dom';

const Header = () => {
  const { username, logout, userGroup } = useAuth();
  const location = useLocation();

  return (
    <header className="fixed top-0 left-0 right-0 py-3 px-4 sm:px-6 z-50 backdrop-blur-sm bg-black/30 animate-fade-in">
      <div className="container mx-auto flex justify-between items-center">
        <EnderLogo width={40} height={40} />
        
        <div className="text-white flex items-center space-x-2">
          <div className="bg-gray-800/80 px-3 py-1.5 rounded-full flex items-center transition-all duration-300 hover:bg-gray-700/80">
            <User className="h-4 w-4 mr-2 text-enderhost-purple" />
            <span className="text-sm">{username}</span>
            {userGroup && (
              <span className="ml-2 text-xs px-2 py-0.5 rounded-full bg-enderhost-purple text-white">
                {userGroup}
              </span>
            )}
          </div>
        </div>
      </div>
      
      {/* Floating Action Buttons */}
      <div className="fixed bottom-6 right-6 flex flex-col space-y-4">
        {userGroup === 'admin' && (
          <Link to="/users">
            <Button 
              variant={location.pathname === '/users' ? 'default' : 'outline'} 
              size="icon"
              className={`rounded-full shadow-lg transition-all duration-300 ${
                location.pathname === '/users' 
                  ? 'bg-enderhost-purple hover:bg-enderhost-blue' 
                  : 'bg-gray-800/80 hover:bg-enderhost-purple'
              }`}
            >
              <UserCog className="h-5 w-5" />
              <span className="sr-only">Users</span>
            </Button>
          </Link>
        )}
        
        <Link to="/">
          <Button 
            variant={location.pathname === '/' ? 'default' : 'outline'} 
            size="icon"
            className={`rounded-full shadow-lg transition-all duration-300 ${
              location.pathname === '/' 
                ? 'bg-enderhost-purple hover:bg-enderhost-blue' 
                : 'bg-gray-800/80 hover:bg-enderhost-purple'
            }`}
          >
            <Home className="h-5 w-5" />
            <span className="sr-only">Orders</span>
          </Button>
        </Link>
        
        <Button 
          variant="outline" 
          size="icon" 
          onClick={logout} 
          className="rounded-full bg-red-600/80 hover:bg-red-700 text-white shadow-lg transition-all duration-300"
        >
          <LogOut className="h-5 w-5" />
          <span className="sr-only">Logout</span>
        </Button>
      </div>
    </header>
  );
};

export default Header;
