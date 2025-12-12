import Link from 'next/link';
import { Metadata } from 'next';
import { ExternalLink } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { apps } from '@/lib/data/apps';

export const metadata: Metadata = {
  title: 'Our Apps',
  description: 'Powerful eCommerce applications for BigCommerce and Shopify, trusted by thousands of merchants worldwide.',
};

export default function AppsPage() {
  return (
    <>
      <Section background="light">
        <Container>
          <div className="max-w-3xl mx-auto text-center mb-16">
            <h1 className="text-5xl md:text-6xl font-heading font-bold text-brand-dark mb-6">
              Our Custom Apps
            </h1>
            <p className="text-xl text-gray-600">
              Powerful applications designed to solve real business problems for eCommerce merchants
            </p>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
            {apps.map((app) => (
              <Card key={app.id} className="flex flex-col h-full">
                <div className="mb-6">
                  <h2 className="text-4xl font-heading font-bold text-brand-dark mb-3">
                    {app.name}
                  </h2>
                  <p className="text-xl text-brand-green italic mb-4">{app.tagline}</p>
                  <div className="flex flex-wrap gap-2 mb-4">
                    {app.platforms.map((platform) => (
                      <span
                        key={platform}
                        className="px-4 py-2 bg-brand-blue/10 text-brand-blue rounded-full font-semibold"
                      >
                        {platform}
                      </span>
                    ))}
                  </div>
                  <p className="text-gray-700 text-lg mb-6">{app.description}</p>
                </div>

                <div className="mb-6 flex-grow">
                  <h3 className="text-xl font-heading font-semibold text-brand-dark mb-4">
                    Key Features:
                  </h3>
                  <ul className="space-y-2">
                    {app.features.map((feature, index) => (
                      <li key={index} className="flex items-start text-gray-700">
                        <span className="text-brand-green mr-3 text-xl font-bold">✓</span>
                        {feature}
                      </li>
                    ))}
                  </ul>
                </div>

                <div className="flex flex-wrap gap-3 mt-auto pt-6 border-t">
                  <Link href={app.path}>
                    <Button variant="primary" size="lg">
                      Learn More
                    </Button>
                  </Link>
                  {app.website && (
                    <a
                      href={app.website}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex"
                    >
                      <Button variant="outline" size="lg">
                        <ExternalLink className="w-5 h-5 mr-2" />
                        Visit Website
                      </Button>
                    </a>
                  )}
                  {app.appStoreUrl && (
                    <a
                      href={app.appStoreUrl}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex"
                    >
                      <Button variant="secondary" size="lg">
                        <ExternalLink className="w-5 h-5 mr-2" />
                        View on Shopify
                      </Button>
                    </a>
                  )}
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
              Need a Custom App?
            </h2>
            <p className="text-xl text-gray-300 mb-8">
              We build custom applications tailored to your specific business needs
            </p>
            <Link href="/services/custom-apps">
              <Button variant="primary" size="lg">
                Learn About Custom Development
              </Button>
            </Link>
          </div>
        </Container>
      </Section>
    </>
  );
}
