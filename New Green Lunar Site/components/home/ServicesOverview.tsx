import Link from 'next/link';
import { Store, TrendingUp, Code2 } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import { services } from '@/lib/data/services';

const iconMap = {
  Store: Store,
  TrendingUp: TrendingUp,
  Code2: Code2,
};

export default function ServicesOverview() {
  return (
    <Section background="light">
      <Container>
        <div className="text-center mb-12">
          <h2 className="text-4xl md:text-5xl font-heading font-bold text-brand-dark mb-4">
            Our Services
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Comprehensive solutions to help your business thrive in the digital economy
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {services.map((service) => {
            const Icon = iconMap[service.icon as keyof typeof iconMap];
            return (
              <Card key={service.id} className="text-center">
                <div className="flex justify-center mb-4">
                  <div className="p-4 bg-brand-green/10 rounded-full">
                    <Icon className="w-12 h-12 text-brand-green" />
                  </div>
                </div>
                <h3 className="text-2xl font-heading font-bold text-brand-dark mb-3">
                  {service.title}
                </h3>
                <p className="text-gray-600 mb-6">{service.shortDescription}</p>
                <Link
                  href={service.path}
                  className="text-brand-blue font-semibold hover:text-brand-green transition-colors inline-flex items-center"
                >
                  Learn More →
                </Link>
              </Card>
            );
          })}
        </div>
      </Container>
    </Section>
  );
}
