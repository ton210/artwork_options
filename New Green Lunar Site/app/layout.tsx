import type { Metadata } from 'next';
import { Inter, Poppins } from 'next/font/google';
import './globals.css';
import Header from '@/components/layout/Header';
import Footer from '@/components/layout/Footer';
import { COMPANY_INFO } from '@/lib/utils/constants';

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
  display: 'swap',
});

const poppins = Poppins({
  weight: ['600', '700', '800'],
  subsets: ['latin'],
  variable: '--font-poppins',
  display: 'swap',
});

export const metadata: Metadata = {
  title: {
    default: `${COMPANY_INFO.name} | ${COMPANY_INFO.tagline}`,
    template: `%s | ${COMPANY_INFO.name}`,
  },
  description: COMPANY_INFO.description,
  keywords: [
    'B2B eCommerce',
    'Custom Shopify Apps',
    'BigCommerce Development',
    'eCommerce Consulting',
    'Marketing Consulting',
    'Algoboost',
    'CelebrationApp',
    'eCommerce Solutions',
  ],
  authors: [{ name: COMPANY_INFO.name }],
  creator: COMPANY_INFO.name,
  publisher: COMPANY_INFO.name,
  metadataBase: new URL('https://greenlunar.com'),
  openGraph: {
    type: 'website',
    locale: 'en_US',
    url: 'https://greenlunar.com',
    siteName: COMPANY_INFO.name,
    title: COMPANY_INFO.name,
    description: COMPANY_INFO.description,
    images: [
      {
        url: '/images/logo/green-lunar-logo.png',
        width: 1200,
        height: 630,
        alt: COMPANY_INFO.name,
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: COMPANY_INFO.name,
    description: COMPANY_INFO.description,
    images: ['/images/logo/green-lunar-logo.png'],
  },
  robots: {
    index: true,
    follow: true,
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className={`${inter.variable} ${poppins.variable}`}>
      <body className="font-sans antialiased">
        <Header />
        <main className="min-h-screen">{children}</main>
        <Footer />
      </body>
    </html>
  );
}
