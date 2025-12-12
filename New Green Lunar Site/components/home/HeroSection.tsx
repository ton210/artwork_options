import Link from 'next/link';
import Button from '@/components/ui/Button';
import Container from '@/components/ui/Container';

export default function HeroSection() {
  return (
    <section className="relative bg-gradient-to-br from-brand-dark via-brand-dark to-gray-900 text-white py-24 lg:py-32 overflow-hidden">
      {/* Background Pattern */}
      <div className="absolute inset-0 opacity-10">
        <div className="absolute top-20 left-10 w-72 h-72 bg-brand-green rounded-full filter blur-3xl"></div>
        <div className="absolute bottom-20 right-10 w-96 h-96 bg-brand-blue rounded-full filter blur-3xl"></div>
      </div>

      <Container className="relative z-10">
        <div className="max-w-4xl mx-auto text-center">
          <h1 className="text-5xl md:text-6xl lg:text-7xl font-heading font-bold mb-6 animate-fade-in">
            We Build & Operate{' '}
            <span className="text-brand-green">eCommerce Brands</span>
          </h1>
          <p className="text-xl md:text-2xl text-gray-300 mb-8 animate-slide-up">
            From Qstomize to MunchMakers to SequinSwag—we own successful eCommerce brands
            and help businesses build theirs with custom solutions and expert consulting
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center items-center animate-slide-up">
            <Link href="/services">
              <Button variant="primary" size="lg">
                Explore Our Services
              </Button>
            </Link>
            <Link href="/apps">
              <Button variant="secondary" size="lg">
                View Our Apps
              </Button>
            </Link>
          </div>
        </div>
      </Container>
    </section>
  );
}
