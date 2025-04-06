
import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useToast } from '@/hooks/use-toast';
import Header from '@/components/Header';
import { useAuth } from '@/hooks/useAuth';
import { UserPlus, Trash2, Key } from 'lucide-react';

interface User {
  id: number;
  username: string;
  created_at: string;
}

const newUserSchema = z.object({
  username: z.string().min(3, {
    message: "Username must be at least 3 characters.",
  }),
  password: z.string().min(6, {
    message: "Password must be at least 6 characters.",
  }),
});

const passwordSchema = z.object({
  new_password: z.string().min(6, {
    message: "Password must be at least 6 characters.",
  }),
});

const UserManagement = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const { toast } = useToast();
  const { username: currentUsername } = useAuth();

  const newUserForm = useForm<z.infer<typeof newUserSchema>>({
    resolver: zodResolver(newUserSchema),
    defaultValues: {
      username: "",
      password: "",
    },
  });

  const passwordForm = useForm<z.infer<typeof passwordSchema>>({
    resolver: zodResolver(passwordSchema),
    defaultValues: {
      new_password: "",
    },
  });

  const fetchUsers = async () => {
    try {
      const response = await fetch('/api/auth/manage_user.php');
      const data = await response.json();
      
      if (data.status === 'success') {
        setUsers(data.users);
      } else {
        toast({
          variant: "destructive",
          title: "Error",
          description: data.message || "Failed to fetch users",
        });
      }
    } catch (error) {
      console.error('Error fetching users:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: "Failed to fetch users. Please try again.",
      });
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  const onCreateUser = async (values: z.infer<typeof newUserSchema>) => {
    setIsLoading(true);
    
    try {
      const response = await fetch('/api/auth/manage_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'create',
          username: values.username,
          password: values.password,
        }),
      });
      
      const data = await response.json();
      
      if (data.status === 'success') {
        toast({
          title: "Success",
          description: "User created successfully",
        });
        fetchUsers();
        newUserForm.reset();
      } else {
        toast({
          variant: "destructive",
          title: "Error",
          description: data.message || "Failed to create user",
        });
      }
    } catch (error) {
      console.error('Error creating user:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: "An unexpected error occurred. Please try again.",
      });
    } finally {
      setIsLoading(false);
    }
  };

  const onDeleteUser = async (userId: number) => {
    if (!confirm('Are you sure you want to delete this user?')) {
      return;
    }
    
    try {
      const response = await fetch('/api/auth/manage_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'delete',
          user_id: userId,
        }),
      });
      
      const data = await response.json();
      
      if (data.status === 'success') {
        toast({
          title: "Success",
          description: "User deleted successfully",
        });
        fetchUsers();
      } else {
        toast({
          variant: "destructive",
          title: "Error",
          description: data.message || "Failed to delete user",
        });
      }
    } catch (error) {
      console.error('Error deleting user:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: "An unexpected error occurred. Please try again.",
      });
    }
  };

  const onChangePassword = async (values: z.infer<typeof passwordSchema>) => {
    if (!selectedUser) return;
    
    setIsLoading(true);
    
    try {
      const response = await fetch('/api/auth/manage_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'change_password',
          user_id: selectedUser.id,
          new_password: values.new_password,
        }),
      });
      
      const data = await response.json();
      
      if (data.status === 'success') {
        toast({
          title: "Success",
          description: `Password for ${selectedUser.username} changed successfully`,
        });
        passwordForm.reset();
        setSelectedUser(null);
      } else {
        toast({
          variant: "destructive",
          title: "Error",
          description: data.message || "Failed to change password",
        });
      }
    } catch (error) {
      console.error('Error changing password:', error);
      toast({
        variant: "destructive",
        title: "Error",
        description: "An unexpected error occurred. Please try again.",
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex flex-col bg-cover bg-center"
      style={{ backgroundImage: 'url("/lovable-uploads/6dfb7bae-3215-4242-a7ae-2d890cf83cf4.png")' }}>
      <Header />
      
      <div className="container mx-auto p-4 md:p-6 flex-grow">
        <div className="glass-card p-6 mb-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-bold text-white">User Management</h2>
            
            <Sheet>
              <SheetTrigger asChild>
                <Button className="bg-enderhost-purple hover:bg-enderhost-blue">
                  <UserPlus className="h-4 w-4 mr-2" />
                  Add User
                </Button>
              </SheetTrigger>
              <SheetContent>
                <SheetHeader>
                  <SheetTitle>Create New User</SheetTitle>
                </SheetHeader>
                
                <div className="py-4">
                  <Form {...newUserForm}>
                    <form onSubmit={newUserForm.handleSubmit(onCreateUser)} className="space-y-4">
                      <FormField
                        control={newUserForm.control}
                        name="username"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Username</FormLabel>
                            <FormControl>
                              <Input placeholder="Enter username" {...field} />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      
                      <FormField
                        control={newUserForm.control}
                        name="password"
                        render={({ field }) => (
                          <FormItem>
                            <FormLabel>Password</FormLabel>
                            <FormControl>
                              <Input type="password" placeholder="Enter password" {...field} />
                            </FormControl>
                            <FormMessage />
                          </FormItem>
                        )}
                      />
                      
                      <Button type="submit" className="w-full" disabled={isLoading}>
                        {isLoading ? "Creating..." : "Create User"}
                      </Button>
                    </form>
                  </Form>
                </div>
              </SheetContent>
            </Sheet>
          </div>
          
          <div className="overflow-x-auto">
            <table className="w-full border-collapse">
              <thead>
                <tr>
                  <th className="border-b border-gray-700 py-2 px-4 text-left text-white font-medium">Username</th>
                  <th className="border-b border-gray-700 py-2 px-4 text-left text-white font-medium">Created</th>
                  <th className="border-b border-gray-700 py-2 px-4 text-left text-white font-medium">Actions</th>
                </tr>
              </thead>
              <tbody>
                {users.map((user) => (
                  <tr key={user.id} className="hover:bg-gray-800/20">
                    <td className="border-b border-gray-700 py-2 px-4 text-white">
                      {user.username} {user.username === currentUsername && <span className="text-xs text-gray-400">(you)</span>}
                    </td>
                    <td className="border-b border-gray-700 py-2 px-4 text-white">
                      {new Date(user.created_at).toLocaleString()}
                    </td>
                    <td className="border-b border-gray-700 py-2 px-4 text-white">
                      <div className="flex space-x-2">
                        <Sheet>
                          <SheetTrigger asChild>
                            <Button 
                              variant="outline" 
                              size="sm"
                              onClick={() => setSelectedUser(user)}
                            >
                              <Key className="h-4 w-4 mr-1" />
                              <span className="hidden sm:inline">Password</span>
                            </Button>
                          </SheetTrigger>
                          <SheetContent>
                            {selectedUser && (
                              <>
                                <SheetHeader>
                                  <SheetTitle>Change Password for {selectedUser.username}</SheetTitle>
                                </SheetHeader>
                                
                                <div className="py-4">
                                  <Form {...passwordForm}>
                                    <form onSubmit={passwordForm.handleSubmit(onChangePassword)} className="space-y-4">
                                      <FormField
                                        control={passwordForm.control}
                                        name="new_password"
                                        render={({ field }) => (
                                          <FormItem>
                                            <FormLabel>New Password</FormLabel>
                                            <FormControl>
                                              <Input type="password" placeholder="Enter new password" {...field} />
                                            </FormControl>
                                            <FormMessage />
                                          </FormItem>
                                        )}
                                      />
                                      
                                      <Button type="submit" className="w-full" disabled={isLoading}>
                                        {isLoading ? "Changing..." : "Change Password"}
                                      </Button>
                                    </form>
                                  </Form>
                                </div>
                              </>
                            )}
                          </SheetContent>
                        </Sheet>
                        
                        {user.username !== currentUsername && (
                          <Button 
                            variant="destructive" 
                            size="sm"
                            onClick={() => onDeleteUser(user.id)}
                          >
                            <Trash2 className="h-4 w-4 mr-1" />
                            <span className="hidden sm:inline">Delete</span>
                          </Button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
                
                {users.length === 0 && (
                  <tr>
                    <td colSpan={3} className="border-b border-gray-700 py-4 px-4 text-center text-gray-400">
                      No users found
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UserManagement;
