
import React from 'react';
import { useAuth } from '@/hooks/useAuth';
import { Button } from '@/components/ui/button';
import EnderLogo from '@/components/EnderLogo';
import { LogOut, User, UserCog, Home } from 'lucide-react';
import { Link, useLocation } from 'react-router-dom';

const Header = () => {
  const { username, logout } = useAuth();
  const location = useLocation();

  return (
    <header className="py-3 px-4 sm:px-6 bg-gray-900 shadow-md">
      <div className="container mx-auto flex justify-between items-center">
        <div className="flex items-center space-x-4">
          <EnderLogo width={40} height={40} />
          <h1 className="text-xl font-bold text-white hidden sm:block">EnderHOST Order System</h1>
        </div>
        
        <div className="flex items-center space-x-3">
          <nav className="hidden md:flex items-center mr-4 space-x-2">
            <Link to="/">
              <Button 
                variant={location.pathname === '/' ? 'default' : 'ghost'} 
                size="sm"
                className="text-white"
              >
                <Home className="h-4 w-4 mr-1" />
                Orders
              </Button>
            </Link>
            
            <Link to="/users">
              <Button 
                variant={location.pathname === '/users' ? 'default' : 'ghost'} 
                size="sm"
                className="text-white"
              >
                <UserCog className="h-4 w-4 mr-1" />
                Users
              </Button>
            </Link>
          </nav>
          
          <div className="text-white flex items-center">
            <User className="h-4 w-4 mr-2" />
            <span className="text-sm">{username}</span>
          </div>
          
          <Button 
            variant="outline" 
            size="sm" 
            onClick={logout} 
            className="text-white border-gray-600"
          >
            <LogOut className="h-4 w-4 mr-1" />
            <span className="hidden sm:inline">Logout</span>
          </Button>
        </div>
      </div>
      
      {/* Mobile Navigation */}
      <div className="md:hidden flex justify-center mt-2 space-x-2">
        <Link to="/" className="flex-1">
          <Button 
            variant={location.pathname === '/' ? 'default' : 'outline'} 
            size="sm"
            className="w-full text-white"
          >
            <Home className="h-4 w-4 mr-1" />
            Orders
          </Button>
        </Link>
        
        <Link to="/users" className="flex-1">
          <Button 
            variant={location.pathname === '/users' ? 'default' : 'outline'} 
            size="sm"
            className="w-full text-white"
          >
            <UserCog className="h-4 w-4 mr-1" />
            Users
          </Button>
        </Link>
      </div>
    </header>
  );
};

export default Header;
