import { Metadata } from 'next';
import { Mail, Linkedin } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import ContactForm from '@/components/contact/ContactForm';
import { COMPANY_INFO } from '@/lib/utils/constants';

export const metadata: Metadata = {
  title: 'Contact Us',
  description: 'Get in touch with Green Lunar LLC to discuss your eCommerce project, app development, or consulting needs.',
};

export default function ContactPage() {
  return (
    <>
      <Section background="light">
        <Container>
          <div className="max-w-3xl mx-auto text-center mb-16">
            <h1 className="text-5xl md:text-6xl font-heading font-bold text-brand-dark mb-6">
              Let&apos;s Start Your eCommerce Expedition
            </h1>
            <p className="text-xl text-gray-600">
              Ready to elevate your business? Get in touch with our team to discuss your needs
            </p>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {/* Contact Form */}
            <Card>
              <h2 className="text-2xl font-heading font-bold text-brand-dark mb-6">
                Send Us a Message
              </h2>
              <ContactForm />
            </Card>

            {/* Contact Information */}
            <div className="space-y-6">
              <Card>
                <div className="flex items-start space-x-4">
                  <div className="p-3 bg-brand-green/10 rounded-full">
                    <Mail className="w-6 h-6 text-brand-green" />
                  </div>
                  <div>
                    <h3 className="text-lg font-heading font-semibold text-brand-dark mb-2">
                      Email Us
                    </h3>
                    <a
                      href={`mailto:${COMPANY_INFO.email}`}
                      className="text-brand-blue hover:text-brand-green transition-colors"
                    >
                      {COMPANY_INFO.email}
                    </a>
                  </div>
                </div>
              </Card>

              <Card>
                <div className="flex items-start space-x-4">
                  <div className="p-3 bg-brand-green/10 rounded-full">
                    <Linkedin className="w-6 h-6 text-brand-green" />
                  </div>
                  <div>
                    <h3 className="text-lg font-heading font-semibold text-brand-dark mb-2">
                      Connect on LinkedIn
                    </h3>
                    <a
                      href={COMPANY_INFO.linkedIn}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-brand-blue hover:text-brand-green transition-colors"
                    >
                      Follow Green Lunar
                    </a>
                  </div>
                </div>
              </Card>

              <Card className="bg-gradient-to-br from-brand-green to-brand-blue text-brand-dark">
                <h3 className="text-2xl font-heading font-bold mb-3">
                  Ready to Work Together?
                </h3>
                <p className="text-lg mb-4">
                  Whether you need a custom B2B store, marketing consulting, or a custom app,
                  we&apos;re here to help your business succeed.
                </p>
                <p className="text-lg font-semibold">
                  Let&apos;s make your eCommerce vision a reality.
                </p>
              </Card>
            </div>
          </div>
        </Container>
      </Section>
    </>
  );
}
