import Link from 'next/link';
import { ExternalLink } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { brands } from '@/lib/data/brands';

export default function BrandsShowcase() {
  return (
    <Section background="white">
      <Container>
        <div className="text-center mb-12">
          <h2 className="text-4xl md:text-5xl font-heading font-bold text-brand-dark mb-4">
            Our Brands
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Successful eCommerce brands we own and operate, serving customers worldwide
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {brands.map((brand) => (
            <Card key={brand.id} className="flex flex-col">
              <div className="mb-4">
                <h3 className="text-3xl font-heading font-bold text-brand-dark mb-3">
                  {brand.name}
                </h3>
                {brand.established && (
                  <p className="text-sm text-brand-green font-semibold mb-2">
                    Established {brand.established}
                  </p>
                )}
                <p className="text-gray-600 mb-4">{brand.description}</p>
                {brand.stats && (
                  <p className="text-sm text-brand-blue font-semibold">
                    {brand.stats}
                  </p>
                )}
              </div>

              <div className="mt-auto pt-4">
                <a
                  href={brand.website}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex"
                >
                  <Button variant="primary" className="w-full">
                    <ExternalLink className="w-4 h-4 mr-2" />
                    Visit {brand.name}
                  </Button>
                </a>
              </div>
            </Card>
          ))}
        </div>
      </Container>
    </Section>
  );
}
