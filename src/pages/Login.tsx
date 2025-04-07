
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useToast } from '@/hooks/use-toast';
import { useAuth } from '@/hooks/useAuth';
import EnderLogo from '@/components/EnderLogo';

const formSchema = z.object({
  username: z.string().min(3, {
    message: "Username must be at least 3 characters.",
  }),
  password: z.string().min(6, {
    message: "Password must be at least 6 characters.",
  }),
});

const Login = () => {
  const [isLoading, setIsLoading] = useState(false);
  const navigate = useNavigate();
  const { toast } = useToast();
  const { login } = useAuth();

  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      username: "",
      password: "",
    },
  });

  const onSubmit = async (values: z.infer<typeof formSchema>) => {
    setIsLoading(true);
    
    try {
      const response = await fetch('/api/auth/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(values),
      });
      
      const data = await response.json();
      
      if (data.status === 'success') {
        // Update the auth context with the user information
        login(data.username, data.userGroup || 'staff');
        
        toast({
          title: "Login successful",
          description: "Welcome back!",
        });
        
        // Redirect to the dashboard
        navigate('/');
      } else {
        toast({
          variant: "destructive",
          title: "Login failed",
          description: data.message || "Invalid username or password",
        });
      }
    } catch (error) {
      console.error('Login error:', error);
      toast({
        variant: "destructive",
        title: "Login error",
        description: "An unexpected error occurred. Please try again.",
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div 
      className="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 bg-cover bg-center"
      style={{ backgroundImage: 'url("/lovable-uploads/6dfb7bae-3215-4242-a7ae-2d890cf83cf4.png")' }}
    >
      <div className="glass-card p-6 sm:p-8 w-full max-w-md mx-auto relative">
        <div className="flex flex-col items-center space-y-6 relative z-10">
          <EnderLogo width={60} height={60} />
          
          <div className="text-center space-y-2">
            <h1 className="text-2xl font-bold text-white">Staff Login</h1>
            <p className="text-gray-400 text-sm">
              Access the EnderHOST Order Management System
            </p>
          </div>
          
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4 w-full">
              <FormField
                control={form.control}
                name="username"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-white">Username</FormLabel>
                    <FormControl>
                      <Input 
                        placeholder="Enter your username" 
                        {...field} 
                        className="bg-opacity-20 bg-black text-white border-gray-600 focus:border-enderhost-purple"
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <FormField
                control={form.control}
                name="password"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-white">Password</FormLabel>
                    <FormControl>
                      <Input 
                        type="password" 
                        placeholder="Enter your password" 
                        {...field} 
                        className="bg-opacity-20 bg-black text-white border-gray-600 focus:border-enderhost-purple"
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <Button 
                type="submit" 
                className="w-full bg-enderhost-purple hover:bg-enderhost-blue transition-colors"
                disabled={isLoading}
              >
                {isLoading ? "Logging in..." : "Login"}
              </Button>
            </form>
          </Form>
          
          <div className="text-xs text-gray-500 text-center pt-4">
            <p>EnderHOST Staff Only</p>
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
        
        {/* Add a separate decorative border that doesn't interfere with clicks */}
        <div className="absolute inset-0 rounded-lg animate-pulse-border pointer-events-none"></div>
      </div>
    </div>
  );
};

export default Login;
