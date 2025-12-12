import Link from 'next/link';
import { Metadata } from 'next';
import { ExternalLink, Zap } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Button from '@/components/ui/Button';
import { apps } from '@/lib/data/apps';

export const metadata: Metadata = {
  title: 'Algoboost.io - Marketing Pixel Integration',
  description: 'Seamless pixel integration for over 44 marketing pixels across BigCommerce and Shopify platforms.',
};

export default function AlgoboostPage() {
  const app = apps.find(a => a.id === 'algoboost')!;

  return (
    <>
      <Section background="light">
        <Container>
          <div className="max-w-4xl mx-auto">
            <div className="flex items-center justify-center mb-8">
              <div className="p-6 bg-brand-green/10 rounded-full">
                <Zap className="w-16 h-16 text-brand-green" />
              </div>
            </div>

            <h1 className="text-5xl md:text-6xl font-heading font-bold text-brand-dark mb-4 text-center">
              {app.name}
            </h1>
            <p className="text-2xl text-brand-green italic mb-8 text-center">
              {app.tagline}
            </p>

            <div className="flex flex-wrap justify-center gap-3 mb-12">
              {app.platforms.map((platform) => (
                <span
                  key={platform}
                  className="px-6 py-3 bg-brand-blue/10 text-brand-blue rounded-full text-lg font-semibold"
                >
                  {platform}
                </span>
              ))}
            </div>

            <div className="bg-white rounded-xl p-8 shadow-lg mb-12">
              <p className="text-xl text-gray-700 mb-8 leading-relaxed">
                {app.description}
              </p>

              <h2 className="text-3xl font-heading font-bold text-brand-dark mb-6">
                Powerful Features
              </h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {app.features.map((feature, index) => (
                  <div key={index} className="flex items-start">
                    <span className="text-brand-green mr-3 text-2xl font-bold">✓</span>
                    <span className="text-gray-700 text-lg">{feature}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="bg-gradient-to-br from-brand-blue to-brand-green text-white rounded-xl p-8 mb-12">
              <h2 className="text-3xl font-heading font-bold mb-4">
                44+ Marketing Pixels Supported
              </h2>
              <p className="text-lg mb-6 opacity-95">
                Algoboost.io supports all major marketing platforms including Facebook, Google Ads,
                TikTok, Pinterest, Snapchat, and many more. Manage all your pixels from one
                powerful dashboard.
              </p>
            </div>

            <div className="flex flex-wrap justify-center gap-4">
              {app.website && (
                <a
                  href={app.website}
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <Button variant="primary" size="lg">
                    <ExternalLink className="w-5 h-5 mr-2" />
                    Visit Algoboost.io
                  </Button>
                </a>
              )}
              <Link href="/contact">
                <Button variant="outline" size="lg">
                  Get Support
                </Button>
              </Link>
            </div>
          </div>
        </Container>
      </Section>
    </>
  );
}
