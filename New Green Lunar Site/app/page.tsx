import HeroSection from '@/components/home/HeroSection';
import BrandsShowcase from '@/components/home/BrandsShowcase';
import ServicesOverview from '@/components/home/ServicesOverview';
import AppsShowcase from '@/components/home/AppsShowcase';
import TeamPreview from '@/components/home/TeamPreview';
import CTASection from '@/components/home/CTASection';

export default function Home() {
  return (
    <>
      <HeroSection />
      <BrandsShowcase />
      <ServicesOverview />
      <AppsShowcase />
      <TeamPreview />
      <CTASection />
    </>
  );
}
