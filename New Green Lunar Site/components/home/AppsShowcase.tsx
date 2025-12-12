import Link from 'next/link';
import { ExternalLink } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { apps } from '@/lib/data/apps';

export default function AppsShowcase() {
  return (
    <Section background="white">
      <Container>
        <div className="text-center mb-12">
          <h2 className="text-4xl md:text-5xl font-heading font-bold text-brand-dark mb-4">
            Our Custom Apps
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Powerful applications trusted by thousands of eCommerce businesses worldwide
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {apps.map((app) => (
            <Card key={app.id} className="flex flex-col">
              <div className="mb-4">
                <h3 className="text-3xl font-heading font-bold text-brand-dark mb-2">
                  {app.name}
                </h3>
                <p className="text-lg text-brand-green italic mb-3">{app.tagline}</p>
                <div className="flex flex-wrap gap-2 mb-4">
                  {app.platforms.map((platform) => (
                    <span
                      key={platform}
                      className="px-3 py-1 bg-brand-blue/10 text-brand-blue rounded-full text-sm font-semibold"
                    >
                      {platform}
                    </span>
                  ))}
                </div>
                <p className="text-gray-600 mb-4">{app.description}</p>
              </div>

              <div className="mb-4 flex-grow">
                <h4 className="font-heading font-semibold text-brand-dark mb-2">
                  Key Features:
                </h4>
                <ul className="space-y-1 text-sm text-gray-600">
                  {app.features.slice(0, 4).map((feature, index) => (
                    <li key={index} className="flex items-start">
                      <span className="text-brand-green mr-2">✓</span>
                      {feature}
                    </li>
                  ))}
                </ul>
              </div>

              <div className="flex flex-wrap gap-3 mt-auto">
                <Link href={app.path}>
                  <Button variant="primary">Learn More</Button>
                </Link>
                {app.website && (
                  <a
                    href={app.website}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex"
                  >
                    <Button variant="outline">
                      <ExternalLink className="w-4 h-4 mr-2" />
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
                    <Button variant="outline">
                      <ExternalLink className="w-4 h-4 mr-2" />
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
  );
}
