import Link from 'next/link';
import { Metadata } from 'next';
import { TrendingUp } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Button from '@/components/ui/Button';
import { services } from '@/lib/data/services';

export const metadata: Metadata = {
  title: 'Marketing Consulting',
  description: 'Strategic eCommerce marketing consulting to grow your online business through data-driven strategies and optimization.',
};

export default function ConsultingPage() {
  const service = services.find(s => s.id === 'consulting')!;

  return (
    <>
      <Section background="light">
        <Container>
          <div className="max-w-4xl mx-auto">
            <div className="flex items-center justify-center mb-8">
              <div className="p-6 bg-brand-green/10 rounded-full">
                <TrendingUp className="w-16 h-16 text-brand-green" />
              </div>
            </div>
            <h1 className="text-5xl md:text-6xl font-heading font-bold text-brand-dark mb-6 text-center">
              {service.title}
            </h1>
            <p className="text-xl text-gray-600 mb-12 text-center">
              {service.shortDescription}
            </p>

            <div className="prose prose-lg max-w-none mb-12">
              <p className="text-gray-700 whitespace-pre-line">{service.fullDescription}</p>
            </div>

            <div className="bg-white rounded-xl p-8 shadow-lg mb-12">
              <h2 className="text-3xl font-heading font-bold text-brand-dark mb-6">
                Our Consulting Services Include
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {service.features.map((feature, index) => (
                  <div key={index} className="flex items-start">
                    <span className="text-brand-green mr-3 text-xl">✓</span>
                    <span className="text-gray-700">{feature}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="text-center">
              <Link href="/contact">
                <Button variant="primary" size="lg">
                  Schedule a Consultation
                </Button>
              </Link>
            </div>
          </div>
        </Container>
      </Section>
    </>
  );
}
