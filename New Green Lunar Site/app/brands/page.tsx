import Link from 'next/link';
import { Metadata } from 'next';
import { ExternalLink, ShoppingBag } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { brands } from '@/lib/data/brands';

export const metadata: Metadata = {
  title: 'Our Brands',
  description: 'Explore the successful eCommerce brands owned and operated by Green Lunar LLC - MunchMakers and SequinSwag.',
};

export default function BrandsPage() {
  return (
    <>
      <Section background="light">
        <Container>
          <div className="max-w-3xl mx-auto text-center mb-16">
            <h1 className="text-5xl md:text-6xl font-heading font-bold text-brand-dark mb-6">
              Our Brands
            </h1>
            <p className="text-xl text-gray-600">
              We don&apos;t just build eCommerce solutions for others—we own and operate successful brands
              that serve customers worldwide
            </p>
          </div>

          <div className="space-y-12">
            {brands.map((brand) => (
              <Card key={brand.id} className="overflow-hidden">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                  <div className="lg:col-span-2">
                    <div className="flex items-start gap-4 mb-4">
                      <div className="p-3 bg-brand-green/10 rounded-full">
                        <ShoppingBag className="w-8 h-8 text-brand-green" />
                      </div>
                      <div>
                        <h2 className="text-4xl font-heading font-bold text-brand-dark mb-2">
                          {brand.name}
                        </h2>
                        {brand.established && (
                          <p className="text-brand-green font-semibold">
                            Established {brand.established}
                          </p>
                        )}
                      </div>
                    </div>

                    <p className="text-xl text-gray-700 mb-4 whitespace-pre-line">
                      {brand.fullDescription}
                    </p>

                    {brand.stats && (
                      <div className="bg-brand-blue/10 rounded-lg p-4 mb-6">
                        <p className="text-brand-blue font-semibold">
                          📊 {brand.stats}
                        </p>
                      </div>
                    )}
                  </div>

                  <div className="flex flex-col justify-center space-y-4">
                    <a
                      href={brand.website}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="block"
                    >
                      <Button variant="primary" size="lg" className="w-full">
                        <ExternalLink className="w-5 h-5 mr-2" />
                        Visit {brand.name}
                      </Button>
                    </a>
                    <div className="text-center text-sm text-gray-500">
                      <a
                        href={brand.website}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="hover:text-brand-green transition-colors"
                      >
                        {brand.website}
                      </a>
                    </div>
                  </div>
                </div>
              </Card>
            ))}
          </div>
        </Container>
      </Section>

      <Section background="dark">
        <Container>
          <div className="max-w-3xl mx-auto text-center">
            <h2 className="text-4xl font-heading font-bold mb-6">
              Want Us to Build Your Brand?
            </h2>
            <p className="text-xl text-gray-300 mb-8">
              We use the same expertise that made our brands successful to help you build yours
            </p>
            <Link href="/services">
              <Button variant="primary" size="lg">
                Explore Our Services
              </Button>
            </Link>
          </div>
        </Container>
      </Section>
    </>
  );
}
