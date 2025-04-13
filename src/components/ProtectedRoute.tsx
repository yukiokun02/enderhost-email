
import { useEffect, useState } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '@/hooks/useAuth';
import { useToast } from '@/hooks/use-toast';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

const ProtectedRoute = ({ children }: ProtectedRouteProps) => {
  const { isAuthenticated, checkAuth } = useAuth();
  const [isChecking, setIsChecking] = useState(true);
  const [isAuthorized, setIsAuthorized] = useState(false);
  const location = useLocation();
  const { toast } = useToast();

  useEffect(() => {
    const verifyAuth = async () => {
      setIsChecking(true);
      try {
        const authenticated = await checkAuth();
        setIsAuthorized(authenticated);
        
        if (!authenticated) {
          toast({
            variant: "destructive",
            title: "Authentication Error",
            description: "Your session has expired. Please log in again.",
          });
        }
      } catch (error) {
        console.error("Auth verification error:", error);
        setIsAuthorized(false);
      } finally {
        setIsChecking(false);
      }
    };

    verifyAuth();
  }, [checkAuth, toast]);

  if (isChecking) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-pulse text-enderhost-purple text-xl">Loading...</div>
      </div>
    );
  }

  if (!isAuthorized) {
    // Redirect to login if not authenticated
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return <>{children}</>;
};

export default ProtectedRoute;
