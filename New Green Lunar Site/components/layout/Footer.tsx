import Link from 'next/link';
import { Linkedin } from 'lucide-react';
import { COMPANY_INFO, NAV_LINKS } from '@/lib/utils/constants';

export default function Footer() {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-brand-dark text-white">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Company Info */}
          <div>
            <h3 className="text-xl font-heading font-bold mb-4">{COMPANY_INFO.name}</h3>
            <p className="text-brand-green italic mb-4">{COMPANY_INFO.tagline}</p>
            <p className="text-gray-300 text-sm mb-4">{COMPANY_INFO.description}</p>
            <a
              href={COMPANY_INFO.linkedIn}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center space-x-2 text-brand-green hover:text-white transition-colors"
            >
              <Linkedin size={20} />
              <span>Follow us on LinkedIn</span>
            </a>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-xl font-heading font-bold mb-4">Quick Links</h3>
            <nav className="flex flex-col space-y-2">
              {NAV_LINKS.map((link) => (
                <Link
                  key={link.href}
                  href={link.href}
                  className="text-gray-300 hover:text-brand-green transition-colors"
                >
                  {link.label}
                </Link>
              ))}
            </nav>
          </div>

          {/* Contact */}
          <div>
            <h3 className="text-xl font-heading font-bold mb-4">Get in Touch</h3>
            <div className="space-y-2 text-gray-300">
              <p>
                <a
                  href={`mailto:${COMPANY_INFO.email}`}
                  className="hover:text-brand-green transition-colors"
                >
                  {COMPANY_INFO.email}
                </a>
              </p>
              <p className="text-sm mt-4">
                Ready to elevate your eCommerce business?
                <br />
                <Link
                  href="/contact"
                  className="text-brand-green hover:text-white transition-colors font-semibold"
                >
                  Let&apos;s talk →
                </Link>
              </p>
            </div>
          </div>
        </div>

        {/* Copyright */}
        <div className="mt-8 pt-8 border-t border-gray-700 text-center text-gray-400 text-sm">
          <p>
            © {currentYear} {COMPANY_INFO.name}. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
}
