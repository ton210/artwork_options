import Link from 'next/link';
import Button from '@/components/ui/Button';
import Container from '@/components/ui/Container';
import Section from '@/components/ui/Section';

export default function CTASection() {
  return (
    <Section background="dark">
      <Container>
        <div className="max-w-3xl mx-auto text-center">
          <h2 className="text-4xl md:text-5xl font-heading font-bold mb-6">
            Ready to Transform Your eCommerce Business?
          </h2>
          <p className="text-xl text-gray-300 mb-8">
            Let&apos;s discuss how Green Lunar can help you achieve your business goals
            with custom solutions and expert guidance.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <Link href="/contact">
              <Button variant="primary" size="lg">
                Get in Touch
              </Button>
            </Link>
            <Link href="/services">
              <Button variant="outline" size="lg" className="border-white text-white hover:bg-white hover:text-brand-dark">
                View Services
              </Button>
            </Link>
          </div>
        </div>
      </Container>
    </Section>
  );
}
