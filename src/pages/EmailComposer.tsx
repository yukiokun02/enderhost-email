import React, { useState } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Mail, Send, Edit } from 'lucide-react';
import { toast } from '@/hooks/use-toast';
import Header from '@/components/Header';
import axios from 'axios';
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import * as z from "zod";

// Email form validation schema
const emailFormSchema = z.object({
  recipient: z.string().email("Please enter a valid email address"),
  subject: z.string().min(1, "Subject is required"),
  content: z.string().min(1, "Email content is required"),
  signature: z.string().optional(),
});

type EmailFormValues = z.infer<typeof emailFormSchema>;

const EmailComposer = () => {
  const { userGroup } = useAuth();
  const [isSending, setIsSending] = useState(false);
  const [editingSignature, setEditingSignature] = useState(false);

  // Default signature with EnderHOST branding
  const defaultSignature = `
<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #8A64FF; font-family: Arial, sans-serif;">
  <div style="font-size: 14px; line-height: 1.6;">
    <p style="margin: 0; font-weight: bold; color: #8A64FF;">EnderHOST</p>
    <p style="margin: 0; color: #3B82F6;">Support Team</p>
    <div style="margin-top: 8px;">
      <p style="margin: 0;">
        <a href="mailto:mail@enderhost.in" style="color: #3B82F6; text-decoration: none;">mail@enderhost.in</a>
      </p>
      <p style="margin: 0;">
        <a href="https://www.enderhost.in" style="color: #3B82F6; text-decoration: none;">www.enderhost.in</a>
      </p>
    </div>
  </div>
</div>
`;

  // Email template with branded header and footer
  const getEmailTemplate = (content, signature) => {
    return `
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
  <!-- Header -->
  <div style="background: linear-gradient(90deg, #8A64FF, #3B82F6); padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
    <h1 style="color: white; margin: 0; font-size: 24px;">EnderHOST</h1>
    <p style="color: white; margin: 5px 0 0 0; font-size: 16px;">Premium Hosting Solutions</p>
  </div>
  
  <!-- Content -->
  <div style="padding: 20px; background-color: #ffffff; border-left: 1px solid #eaeaea; border-right: 1px solid #eaeaea;">
    ${content}
  </div>
  
  <!-- Footer -->
  <div style="background-color: #1E1E2E; padding: 15px; border-radius: 0 0 8px 8px; text-align: center;">
    ${signature}
    <p style="color: #9ca3af; font-size: 12px; margin-top: 10px;">© ${new Date().getFullYear()} EnderHOST. All rights reserved.</p>
  </div>
</div>
`;
  };

  // Initialize form with validation
  const form = useForm<EmailFormValues>({
    resolver: zodResolver(emailFormSchema),
    defaultValues: {
      recipient: "",
      subject: "",
      content: "",
      signature: defaultSignature,
    },
  });

  const handleSend = async (values: EmailFormValues) => {
    setIsSending(true);
    
    // Prepare the email content with template
    const emailContent = getEmailTemplate(values.content, values.signature || defaultSignature);
    
    try {
      // Send the email via our mail composer API
      const response = await axios.post('/api/email/send_email.php', {
        recipient: values.recipient,
        subject: values.subject,
        content: emailContent,
        // We don't need to send signature separately anymore as it's included in the content
        signature: ""
      });
      
      if (response.data.success) {
        toast({
          title: "Email Sent",
          description: `Your email to ${values.recipient} has been sent successfully`,
        });
        
        // Reset form but keep the signature
        const currentSignature = form.getValues("signature");
        form.reset({
          recipient: "",
          subject: "",
          content: "",
          signature: currentSignature,
        });
      } else {
        throw new Error(response.data.error || 'Failed to send email');
      }
    } catch (error) {
      console.error('Email sending error:', error);
      toast({
        title: "Error Sending Email",
        description: error instanceof Error ? error.message : "There was a problem sending your email. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsSending(false);
    }
  };

  return (
    <div className="min-h-screen bg-black">
      <Header />
      <div className="container mx-auto pt-24 pb-10 px-4">
        <div className="glass-card p-6 max-w-3xl mx-auto">
          <h1 className="text-2xl font-bold mb-6 flex items-center gap-2 text-white">
            <Mail className="h-6 w-6 text-enderhost-purple" />
            Email Composer
          </h1>
          
          <Form {...form}>
            <form onSubmit={form.handleSubmit(handleSend)} className="space-y-4">
              <FormField
                control={form.control}
                name="recipient"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-gray-200">Recipient Email</FormLabel>
                    <FormControl>
                      <Input
                        placeholder="recipient@example.com"
                        className="bg-gray-800/50 text-white border-gray-700"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <FormField
                control={form.control}
                name="subject"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-gray-200">Subject</FormLabel>
                    <FormControl>
                      <Input
                        placeholder="Enter email subject"
                        className="bg-gray-800/50 text-white border-gray-700"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              
              <FormField
                control={form.control}
                name="content"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-gray-200">Content</FormLabel>
                    <FormControl>
                      <Textarea
                        placeholder="Write your email content here..."
                        className="min-h-[200px] bg-gray-800/50 text-white border-gray-700 whitespace-pre-wrap"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage />
                    <p className="text-xs text-gray-400">Use Enter key to create paragraphs - they will be preserved in the sent email.</p>
                  </FormItem>
                )}
              />
              
              <div className="border border-gray-700 rounded-md p-3 bg-gray-800/30">
                <div className="flex justify-between items-center mb-2">
                  <h3 className="text-sm font-medium text-gray-300">Signature:</h3>
                  <Button 
                    type="button" 
                    variant="ghost" 
                    size="sm"
                    onClick={() => setEditingSignature(!editingSignature)}
                    className="text-xs text-enderhost-purple hover:text-enderhost-blue"
                  >
                    <Edit className="h-3 w-3 mr-1" />
                    {editingSignature ? "Preview" : "Edit"}
                  </Button>
                </div>
                
                {editingSignature ? (
                  <FormField
                    control={form.control}
                    name="signature"
                    render={({ field }) => (
                      <FormItem>
                        <FormControl>
                          <Textarea
                            placeholder="Edit your signature HTML..."
                            className="min-h-[100px] bg-gray-800/50 text-white border-gray-700 text-xs font-mono"
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                ) : (
                  <div className="text-xs text-gray-400 signature-preview border border-gray-700 p-2 rounded bg-gray-800/20" 
                    dangerouslySetInnerHTML={{ __html: form.watch("signature") || defaultSignature }} 
                  />
                )}
              </div>
              
              <div className="pt-2">
                <Button 
                  type="submit" 
                  disabled={isSending}
                  className="w-full bg-enderhost-purple hover:bg-enderhost-blue transition-colors"
                >
                  {isSending ? (
                    <>Sending Email...</>
                  ) : (
                    <>
                      <Send className="mr-2 h-4 w-4" />
                      Send Email
                    </>
                  )}
                </Button>
              </div>
            </form>
          </Form>
        </div>
      </div>
    </div>
  );
};

export default EmailComposer;
