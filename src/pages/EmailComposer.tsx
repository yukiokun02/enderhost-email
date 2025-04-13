
import React, { useState } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Mail, Send } from 'lucide-react';
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
});

type EmailFormValues = z.infer<typeof emailFormSchema>;

const EmailComposer = () => {
  const { userGroup } = useAuth();
  const [isSending, setIsSending] = useState(false);

  // Plain text signature without icons
  const signature = `
<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #8A64FF; font-family: Arial, sans-serif;">
  <div style="font-size: 14px; line-height: 1.6;">
    <p style="margin: 0; font-weight: bold; color: #8A64FF;">Tanumoy Maity</p>
    <p style="margin: 0; color: #3B82F6;">Founder, EnderHOST</p>
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

  // Initialize form with validation
  const form = useForm<EmailFormValues>({
    resolver: zodResolver(emailFormSchema),
    defaultValues: {
      recipient: "",
      subject: "",
      content: "",
    },
  });

  const handleSend = async (values: EmailFormValues) => {
    setIsSending(true);
    
    // Log the email data for debugging
    console.log("Sending email to:", values.recipient);
    console.log("Subject:", values.subject);
    console.log("Content:", values.content + signature);
    
    try {
      // Send the email via our mail composer API
      const response = await axios.post('/api/email/send_email.php', {
        recipient: values.recipient,
        subject: values.subject,
        content: values.content,
        signature: signature
      });
      
      if (response.data.success) {
        toast({
          title: "Email Sent",
          description: `Your email to ${values.recipient} has been sent successfully`,
        });
        
        // Reset form
        form.reset();
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
                <h3 className="text-sm font-medium text-gray-300 mb-2">Signature Preview:</h3>
                <div className="text-xs text-gray-400 signature-preview" dangerouslySetInnerHTML={{ __html: signature }} />
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
