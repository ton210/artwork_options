import { Metadata } from 'next';
import { Linkedin } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { team } from '@/lib/data/team';

export const metadata: Metadata = {
  title: 'Our Team',
  description: 'Meet the talented professionals behind Green Lunar LLC, dedicated to delivering exceptional eCommerce solutions.',
};

export default function TeamPage() {
  const sortedTeam = [...team].sort((a, b) => a.order - b.order);

  return (
    <>
      <Section background="light">
        <Container>
          <div className="max-w-3xl mx-auto text-center mb-16">
            <h1 className="text-5xl md:text-6xl font-heading font-bold text-brand-dark mb-6">
              Meet Our Team
            </h1>
            <p className="text-xl text-gray-600">
              Dedicated professionals bringing expertise, creativity, and innovation to every project
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {sortedTeam.map((member) => (
              <Card key={member.id} className="text-center">
                <div className="mb-6">
                  {/* Photo placeholder - will show initials until actual photos are added */}
                  <div className="relative w-40 h-40 mx-auto mb-4 rounded-full overflow-hidden bg-gradient-to-br from-brand-green to-brand-blue flex items-center justify-center">
                    <div className="text-5xl font-bold text-white">
                      {member.name
                        .split(' ')
                        .map((n) => n[0])
                        .join('')}
                    </div>
                  </div>

                  <h2 className="text-3xl font-heading font-bold text-brand-dark mb-2">
                    {member.name}
                  </h2>
                  <p className="text-xl text-brand-green font-semibold mb-4">
                    {member.title}
                  </p>
                </div>

                <p className="text-gray-600 mb-6 leading-relaxed">{member.bio}</p>

                <a
                  href={member.linkedin}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center justify-center space-x-2 px-6 py-3 bg-brand-blue text-white rounded-lg hover:bg-brand-green hover:text-brand-dark transition-all duration-300 hover:shadow-lg font-semibold"
                >
                  <Linkedin size={20} />
                  <span>Connect on LinkedIn</span>
                </a>
              </Card>
            ))}
          </div>
        </Container>
      </Section>

      <Section background="dark">
        <Container>
          <div className="max-w-3xl mx-auto text-center">
            <h2 className="text-4xl font-heading font-bold mb-6">
              Join Our Team
            </h2>
            <p className="text-xl text-gray-300 mb-8">
              We&apos;re always looking for talented individuals who share our passion for eCommerce innovation
            </p>
            <a
              href="https://www.linkedin.com/company/green-lunar/jobs"
              target="_blank"
              rel="noopener noreferrer"
            >
              <Button variant="primary" size="lg">
                <Linkedin className="w-5 h-5 mr-2" />
                View Open Positions
              </Button>
            </a>
          </div>
        </Container>
      </Section>
    </>
  );
}
