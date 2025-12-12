import Link from 'next/link';
import Image from 'next/image';
import { Linkedin } from 'lucide-react';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { team } from '@/lib/data/team';

export default function TeamPreview() {
  // Sort by order
  const sortedTeam = [...team].sort((a, b) => a.order - b.order);

  return (
    <Section background="light">
      <Container>
        <div className="text-center mb-12">
          <h2 className="text-4xl md:text-5xl font-heading font-bold text-brand-dark mb-4">
            Meet Our Expert Team
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            Talented professionals dedicated to your success
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
          {sortedTeam.map((member) => (
            <Card key={member.id} className="text-center">
              <div className="mb-4">
                <div className="relative w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden bg-brand-light">
                  {/* Placeholder - will be replaced with actual photos */}
                  <div className="w-full h-full flex items-center justify-center text-4xl font-bold text-brand-green">
                    {member.name
                      .split(' ')
                      .map((n) => n[0])
                      .join('')}
                  </div>
                </div>
                <h3 className="text-2xl font-heading font-bold text-brand-dark mb-1">
                  {member.name}
                </h3>
                <p className="text-brand-green font-semibold mb-3">
                  {member.title}
                </p>
                <p className="text-gray-600 text-sm mb-4 line-clamp-3">
                  {member.bio}
                </p>
              </div>
              <a
                href={member.linkedin}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center justify-center space-x-2 text-brand-blue hover:text-brand-green transition-colors"
              >
                <Linkedin size={20} />
                <span className="font-semibold">LinkedIn</span>
              </a>
            </Card>
          ))}
        </div>

        <div className="text-center">
          <Link href="/team">
            <Button variant="outline" size="lg">
              View Full Team
            </Button>
          </Link>
        </div>
      </Container>
    </Section>
  );
}
