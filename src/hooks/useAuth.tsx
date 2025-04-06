
import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { useNavigate } from 'react-router-dom';

interface AuthContextType {
  isAuthenticated: boolean;
  username: string | null;
  login: (username: string) => void;
  logout: () => void;
  checkAuth: () => Promise<boolean>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [username, setUsername] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const navigate = useNavigate();

  const login = (user: string) => {
    setIsAuthenticated(true);
    setUsername(user);
  };

  const logout = async () => {
    try {
      await fetch('/api/auth/logout.php', {
        method: 'POST',
        credentials: 'include',
      });
      setIsAuthenticated(false);
      setUsername(null);
      navigate('/login');
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  const checkAuth = async (): Promise<boolean> => {
    try {
      const response = await fetch('/api/auth/check_session.php', {
        credentials: 'include',
      });
      const data = await response.json();
      
      if (data.status === 'success' && data.authenticated) {
        setIsAuthenticated(true);
        setUsername(data.username);
        return true;
      } else {
        setIsAuthenticated(false);
        setUsername(null);
        return false;
      }
    } catch (error) {
      console.error('Auth check error:', error);
      setIsAuthenticated(false);
      setUsername(null);
      return false;
    }
  };

  useEffect(() => {
    const initAuth = async () => {
      setIsLoading(true);
      await checkAuth();
      setIsLoading(false);
    };

    initAuth();
  }, []);

  return (
    <AuthContext.Provider value={{ isAuthenticated, username, login, logout, checkAuth }}>
      {!isLoading && children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
