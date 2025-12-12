import Link from 'next/link';
import { Metadata } from 'next';
import { Rocket, Target, Users } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { COMPANY_INFO } from '@/lib/utils/constants';

export const metadata: Metadata = {
  title: 'About Us',
  description: 'Learn about Green Lunar LLC - our mission, values, and commitment to eCommerce excellence.',
};

export default function AboutPage() {
  return (
    <>
      <Section background="light">
        <Container>
          <div className="max-w-4xl mx-auto">
            <h1 className="text-5xl md:text-6xl font-heading font-bold text-brand-dark mb-6 text-center">
              About {COMPANY_INFO.name}
            </h1>
            <p className="text-2xl text-brand-green italic mb-12 text-center">
              {COMPANY_INFO.tagline}
            </p>

            <div className="prose prose-lg max-w-none mb-16">
              <p className="text-xl text-gray-700 leading-relaxed mb-6">
                Green Lunar LLC is a specialized B2B eCommerce solutions provider dedicated to helping
                businesses thrive in the digital economy. We combine technical expertise, creative
                innovation, and strategic thinking to deliver exceptional results for our clients.
              </p>
              <p className="text-lg text-gray-600 leading-relaxed mb-6">
                Our journey in eCommerce has taught us that success requires more than just technology—it
                requires partnership, understanding, and a commitment to excellence. That&apos;s why we approach
                every project as a collaborative expedition, working closely with our clients to understand
                their unique challenges and craft solutions that drive real business growth.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
              <Card className="text-center">
                <div className="flex justify-center mb-4">
                  <div className="p-4 bg-brand-green/10 rounded-full">
                    <Target className="w-12 h-12 text-brand-green" />
                  </div>
                </div>
                <h3 className="text-2xl font-heading font-bold text-brand-dark mb-3">
                  Our Mission
                </h3>
                <p className="text-gray-600">
                  Empower businesses with innovative eCommerce solutions that drive growth, efficiency,
                  and customer satisfaction.
                </p>
              </Card>

              <Card className="text-center">
                <div className="flex justify-center mb-4">
                  <div className="p-4 bg-brand-green/10 rounded-full">
                    <Rocket className="w-12 h-12 text-brand-green" />
                  </div>
                </div>
                <h3 className="text-2xl font-heading font-bold text-brand-dark mb-3">
                  Our Vision
                </h3>
                <p className="text-gray-600">
                  To be the trusted partner for B2B eCommerce businesses seeking exceptional custom
                  solutions and expert guidance.
                </p>
              </Card>

              <Card className="text-center">
                <div className="flex justify-center mb-4">
                  <div className="p-4 bg-brand-green/10 rounded-full">
                    <Users className="w-12 h-12 text-brand-green" />
                  </div>
                </div>
                <h3 className="text-2xl font-heading font-bold text-brand-dark mb-3">
                  Our Values
                </h3>
                <p className="text-gray-600">
                  Excellence, innovation, collaboration, and a relentless focus on delivering value
                  to our clients.
                </p>
              </Card>
            </div>

            <div className="bg-brand-dark text-white rounded-xl p-8 mb-12">
              <h2 className="text-3xl font-heading font-bold mb-4">What Sets Us Apart</h2>
              <ul className="space-y-4 text-lg">
                <li className="flex items-start">
                  <span className="text-brand-green mr-3 text-xl">✓</span>
                  <span>Deep expertise in B2B eCommerce on Shopify and BigCommerce platforms</span>
                </li>
                <li className="flex items-start">
                  <span className="text-brand-green mr-3 text-xl">✓</span>
                  <span>Proven track record with successful apps used by thousands of merchants</span>
                </li>
                <li className="flex items-start">
                  <span className="text-brand-green mr-3 text-xl">✓</span>
                  <span>End-to-end solutions from strategy to implementation and support</span>
                </li>
                <li className="flex items-start">
                  <span className="text-brand-green mr-3 text-xl">✓</span>
                  <span>Dedicated team of experts committed to your success</span>
                </li>
              </ul>
            </div>

            <div className="text-center">
              <Link href="/team">
                <Button variant="primary" size="lg" className="mr-4">
                  Meet Our Team
                </Button>
              </Link>
              <Link href="/contact">
                <Button variant="outline" size="lg">
                  Get in Touch
                </Button>
              </Link>
            </div>
          </div>
        </Container>
      </Section>
    </>
  );
}
