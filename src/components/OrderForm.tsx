
import React, { useState } from 'react';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/components/ui/use-toast";
import { Eye, EyeOff, Mail, Server, User, FileText } from "lucide-react";

const OrderForm: React.FC = () => {
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [formData, setFormData] = useState({
    orderId: '',
    serverName: '',
    email: '',
    password: '',
    customerName: ''
  });

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      // In a real implementation, this would call your API to store data
      // and send the email to the customer
      await new Promise(resolve => setTimeout(resolve, 1500)); // Simulate API call
      
      toast({
        title: "Order Confirmed!",
        description: "Server details have been sent to customer's email.",
        variant: "default",
      });

      // Reset form after successful submission
      setFormData({
        orderId: '',
        serverName: '',
        email: '',
        password: '',
        customerName: ''
      });
    } catch (error) {
      toast({
        title: "Submission Failed",
        description: "There was an error processing your request.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6 w-full max-w-md">
      <div className="space-y-4">
        <div className="space-y-2">
          <Label 
            htmlFor="orderId" 
            className="text-sm font-medium text-gray-200 flex items-center gap-2"
          >
            <FileText className="h-4 w-4 text-enderhost-purple" />
            Order ID
          </Label>
          <Input
            id="orderId"
            name="orderId"
            placeholder="Enter order ID"
            className="bg-enderhost-dark border-enderhost-purple/30 focus:border-enderhost-purple focus:ring-enderhost-purple/20"
            value={formData.orderId}
            onChange={handleChange}
            required
          />
        </div>

        <div className="space-y-2">
          <Label 
            htmlFor="serverName" 
            className="text-sm font-medium text-gray-200 flex items-center gap-2"
          >
            <Server className="h-4 w-4 text-enderhost-blue" />
            Server Name
          </Label>
          <Input
            id="serverName"
            name="serverName"
            placeholder="Enter server name"
            className="bg-enderhost-dark border-enderhost-purple/30 focus:border-enderhost-purple focus:ring-enderhost-purple/20"
            value={formData.serverName}
            onChange={handleChange}
            required
          />
        </div>

        <div className="space-y-2">
          <Label 
            htmlFor="email" 
            className="text-sm font-medium text-gray-200 flex items-center gap-2"
          >
            <Mail className="h-4 w-4 text-enderhost-purple" />
            Email ID
          </Label>
          <Input
            id="email"
            name="email"
            type="email"
            placeholder="customer@example.com"
            className="bg-enderhost-dark border-enderhost-purple/30 focus:border-enderhost-purple focus:ring-enderhost-purple/20"
            value={formData.email}
            onChange={handleChange}
            required
          />
        </div>

        <div className="space-y-2">
          <Label 
            htmlFor="password" 
            className="text-sm font-medium text-gray-200 flex items-center gap-2"
          >
            <div className="h-4 w-4 text-enderhost-blue">#</div>
            Password
          </Label>
          <div className="relative">
            <Input
              id="password"
              name="password"
              type={showPassword ? "text" : "password"}
              placeholder="Enter password"
              className="bg-enderhost-dark border-enderhost-purple/30 focus:border-enderhost-purple focus:ring-enderhost-purple/20 pr-10"
              value={formData.password}
              onChange={handleChange}
              required
            />
            <button
              type="button"
              className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-200"
              onClick={togglePasswordVisibility}
            >
              {showPassword ? (
                <EyeOff className="h-4 w-4" />
              ) : (
                <Eye className="h-4 w-4" />
              )}
            </button>
          </div>
        </div>

        <div className="space-y-2">
          <Label 
            htmlFor="customerName" 
            className="text-sm font-medium text-gray-200 flex items-center gap-2"
          >
            <User className="h-4 w-4 text-enderhost-purple" />
            Customer Name
          </Label>
          <Input
            id="customerName"
            name="customerName"
            placeholder="Enter customer name"
            className="bg-enderhost-dark border-enderhost-purple/30 focus:border-enderhost-purple focus:ring-enderhost-purple/20"
            value={formData.customerName}
            onChange={handleChange}
            required
          />
        </div>
      </div>

      <Button 
        type="submit" 
        disabled={isSubmitting}
        className="w-full bg-gradient-to-r from-enderhost-purple to-enderhost-blue hover:from-enderhost-purple/90 hover:to-enderhost-blue/90 transition-all"
      >
        {isSubmitting ? "Processing..." : "Submit Order"}
      </Button>
    </form>
  );
};

export default OrderForm;
