
import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { useNavigate } from 'react-router-dom';

interface AuthContextType {
  isAuthenticated: boolean;
  username: string | null;
  userGroup: string | null;
  login: (username: string, userGroup: string) => void;
  logout: () => void;
  checkAuth: () => Promise<boolean>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [username, setUsername] = useState<string | null>(null);
  const [userGroup, setUserGroup] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const navigate = useNavigate();

  const login = (user: string, group: string) => {
    setIsAuthenticated(true);
    setUsername(user);
    setUserGroup(group);
  };

  const logout = async () => {
    try {
      await fetch('/api/auth/logout.php', {
        method: 'POST',
        credentials: 'include',
      });
      setIsAuthenticated(false);
      setUsername(null);
      setUserGroup(null);
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
        setUserGroup(data.userGroup);
        return true;
      } else {
        setIsAuthenticated(false);
        setUsername(null);
        setUserGroup(null);
        return false;
      }
    } catch (error) {
      console.error('Auth check error:', error);
      setIsAuthenticated(false);
      setUsername(null);
      setUserGroup(null);
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
    <AuthContext.Provider value={{ isAuthenticated, username, userGroup, login, logout, checkAuth }}>
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
