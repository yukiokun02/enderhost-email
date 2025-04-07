
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
import { UserPlus, Trash2, Key, User, Shield } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { 
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table';

interface User {
  id: number;
  username: string;
  user_group?: string;
  created_at: string;
}

const newUserSchema = z.object({
  username: z.string().min(3, {
    message: "Username must be at least 3 characters.",
  }),
  password: z.string().min(6, {
    message: "Password must be at least 6 characters.",
  }),
  user_group: z.enum(['admin', 'staff']),
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
  const { username: currentUsername, userGroup } = useAuth();

  const newUserForm = useForm<z.infer<typeof newUserSchema>>({
    resolver: zodResolver(newUserSchema),
    defaultValues: {
      username: "",
      password: "",
      user_group: "staff",
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
          user_group: values.user_group,
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

  // User group badge color
  const getUserGroupBadgeVariant = (group: string) => {
    return group === 'admin' ? 'default' : 'secondary';
  };

  return (
    <div className="min-h-screen flex flex-col bg-cover bg-center animate-fade-in"
      style={{ backgroundImage: 'url("/lovable-uploads/6dfb7bae-3215-4242-a7ae-2d890cf83cf4.png")' }}>
      <Header />
      
      <div className="container mx-auto p-4 md:p-6 flex-grow mt-16">
        <div className="glass-card p-6 mb-6 animate-scale-in">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-bold text-white">User Management</h2>
            
            {userGroup === 'admin' && (
              <div className="flex justify-end">
                <Sheet>
                  <SheetTrigger asChild>
                    <Button className="bg-enderhost-purple hover:bg-enderhost-blue transition-colors duration-300">
                      <UserPlus className="h-4 w-4 mr-2" />
                      Add User
                    </Button>
                  </SheetTrigger>
                  <SheetContent className="animate-slide-in-right">
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
                          
                          <FormField
                            control={newUserForm.control}
                            name="user_group"
                            render={({ field }) => (
                              <FormItem>
                                <FormLabel>User Group</FormLabel>
                                <Select 
                                  onValueChange={field.onChange} 
                                  defaultValue={field.value}
                                >
                                  <FormControl>
                                    <SelectTrigger>
                                      <SelectValue placeholder="Select user group" />
                                    </SelectTrigger>
                                  </FormControl>
                                  <SelectContent>
                                    <SelectItem value="admin">Admin</SelectItem>
                                    <SelectItem value="staff">Staff</SelectItem>
                                  </SelectContent>
                                </Select>
                                <FormMessage />
                              </FormItem>
                            )}
                          />
                          
                          <Button type="submit" className="w-full transition-all duration-300" disabled={isLoading}>
                            {isLoading ? "Creating..." : "Create User"}
                          </Button>
                        </form>
                      </Form>
                    </div>
                  </SheetContent>
                </Sheet>
              </div>
            )}
          </div>
          
          <div className="overflow-x-auto animate-fade-in">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="text-white font-medium">Username</TableHead>
                  <TableHead className="text-white font-medium">Group</TableHead>
                  <TableHead className="text-white font-medium">Created</TableHead>
                  <TableHead className="text-white font-medium">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {users.map((user) => (
                  <TableRow key={user.id} className="hover:bg-gray-800/20 transition-colors duration-200">
                    <TableCell className="text-white">
                      <div className="flex items-center">
                        <User className="h-4 w-4 mr-2 text-gray-400" />
                        {user.username} {user.username === currentUsername && <span className="ml-2 text-xs text-gray-400">(you)</span>}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant={getUserGroupBadgeVariant(user.user_group || 'staff')}>
                        {user.user_group || 'staff'}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-white">
                      {new Date(user.created_at).toLocaleString()}
                    </TableCell>
                    <TableCell className="text-white">
                      <div className="flex space-x-2">
                        <Sheet>
                          <SheetTrigger asChild>
                            <Button 
                              variant="outline" 
                              size="sm"
                              onClick={() => setSelectedUser(user)}
                              className="transition-all duration-300 hover:bg-enderhost-purple"
                            >
                              <Key className="h-4 w-4 mr-1" />
                              <span className="hidden sm:inline">Password</span>
                            </Button>
                          </SheetTrigger>
                          <SheetContent className="animate-slide-in-right">
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
                                      
                                      <Button type="submit" className="w-full transition-all duration-300" disabled={isLoading}>
                                        {isLoading ? "Changing..." : "Change Password"}
                                      </Button>
                                    </form>
                                  </Form>
                                </div>
                              </>
                            )}
                          </SheetContent>
                        </Sheet>
                        
                        {userGroup === 'admin' && user.username !== currentUsername && (
                          <Button 
                            variant="destructive" 
                            size="sm"
                            onClick={() => onDeleteUser(user.id)}
                            className="transition-all duration-300"
                          >
                            <Trash2 className="h-4 w-4 mr-1" />
                            <span className="hidden sm:inline">Delete</span>
                          </Button>
                        )}
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
                
                {users.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={4} className="text-center text-gray-400">
                      No users found
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UserManagement;
