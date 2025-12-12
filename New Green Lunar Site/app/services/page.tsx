import Link from 'next/link';
import { Metadata } from 'next';
import { Store, TrendingUp, Code2 } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { services } from '@/lib/data/services';

export const metadata: Metadata = {
  title: 'Our Services',
  description: 'Comprehensive B2B eCommerce solutions including custom stores, marketing consulting, and app development.',
};

const iconMap = {
  Store: Store,
  TrendingUp: TrendingUp,
  Code2: Code2,
};

export default function ServicesPage() {
  return (
    <>
      <Section background="light">
        <Container>
          <div className="max-w-3xl mx-auto text-center mb-16">
            <h1 className="text-5xl md:text-6xl font-heading font-bold text-brand-dark mb-6">
              Our Services
            </h1>
            <p className="text-xl text-gray-600">
              Comprehensive solutions designed to help your B2B eCommerce business thrive in the digital economy
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {services.map((service) => {
              const Icon = iconMap[service.icon as keyof typeof iconMap];
              return (
                <Card key={service.id}>
                  <div className="flex justify-center mb-4">
                    <div className="p-4 bg-brand-green/10 rounded-full">
                      <Icon className="w-12 h-12 text-brand-green" />
                    </div>
                  </div>
                  <h2 className="text-2xl font-heading font-bold text-brand-dark mb-3">
                    {service.title}
                  </h2>
                  <p className="text-gray-600 mb-4 whitespace-pre-line">
                    {service.fullDescription}
                  </p>
                  <div className="mb-6">
                    <h3 className="font-heading font-semibold text-brand-dark mb-2">
                      Key Features:
                    </h3>
                    <ul className="space-y-2 text-sm text-gray-600">
                      {service.features.map((feature, index) => (
                        <li key={index} className="flex items-start">
                          <span className="text-brand-green mr-2">✓</span>
                          {feature}
                        </li>
                      ))}
                    </ul>
                  </div>
                  <Link href={service.path}>
                    <Button variant="primary" className="w-full">
                      Learn More
                    </Button>
                  </Link>
                </Card>
              );
            })}
          </div>
        </Container>
      </Section>

      <Section background="dark">
        <Container>
          <div className="max-w-3xl mx-auto text-center">
            <h2 className="text-4xl font-heading font-bold mb-6">
              Ready to Get Started?
            </h2>
            <p className="text-xl text-gray-300 mb-8">
              Let&apos;s discuss how we can help your business succeed
            </p>
            <Link href="/contact">
              <Button variant="primary" size="lg">
                Contact Us
              </Button>
            </Link>
          </div>
        </Container>
      </Section>
    </>
  );
}
