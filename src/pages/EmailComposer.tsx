
import React, { useState } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Mail, Send } from 'lucide-react';
import { toast } from '@/hooks/use-toast';
import Header from '@/components/Header';

const EmailComposer = () => {
  const { userGroup } = useAuth();
  const [subject, setSubject] = useState('');
  const [content, setContent] = useState('');
  const [isSending, setIsSending] = useState(false);

  // Default signature with branding
  const signature = `
<br/><br/>
Best Regards,<br/>
Tanumoy Maity<br/>
Founder, EnderHOST<br/>
Email: <a href="mailto:mail@enderhost.in">mail@enderhost.in</a><br/>
Website: <a href="https://www.enderhost.in">www.enderhost.in</a>
`;

  const handleSend = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!subject.trim()) {
      toast({
        title: "Subject Required",
        description: "Please add a subject to your email",
        variant: "destructive",
      });
      return;
    }

    if (!content.trim()) {
      toast({
        title: "Content Required",
        description: "Please add content to your email",
        variant: "destructive",
      });
      return;
    }

    setIsSending(true);
    
    // Here you would normally send the email via API
    console.log("Sending email with subject:", subject);
    console.log("Content:", content + signature);
    
    // Simulate sending
    setTimeout(() => {
      setIsSending(false);
      toast({
        title: "Email Sent",
        description: "Your email has been sent successfully",
      });
      
      // Reset form
      setSubject('');
      setContent('');
    }, 1500);
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
          
          <form onSubmit={handleSend} className="space-y-4">
            <div>
              <label htmlFor="subject" className="block text-sm font-medium text-gray-200 mb-1">
                Subject
              </label>
              <Input
                id="subject"
                value={subject}
                onChange={(e) => setSubject(e.target.value)}
                placeholder="Enter email subject"
                className="bg-gray-800/50 text-white border-gray-700"
              />
            </div>
            
            <div>
              <label htmlFor="content" className="block text-sm font-medium text-gray-200 mb-1">
                Content
              </label>
              <Textarea
                id="content"
                value={content}
                onChange={(e) => setContent(e.target.value)}
                placeholder="Write your email content here..."
                className="min-h-[200px] bg-gray-800/50 text-white border-gray-700"
              />
            </div>
            
            <div className="border border-gray-700 rounded-md p-3 bg-gray-800/30">
              <h3 className="text-sm font-medium text-gray-300 mb-2">Signature Preview:</h3>
              <div className="text-xs text-gray-400" dangerouslySetInnerHTML={{ __html: signature }} />
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
        </div>
      </div>
    </div>
  );
};

export default EmailComposer;
