import { Service } from '../types';

export const services: Service[] = [
  {
    id: 'b2b-stores',
    title: 'Custom B2B eCommerce Stores',
    shortDescription: 'Tailored eCommerce solutions designed specifically for B2B businesses with complex pricing, bulk ordering, and customer management needs.',
    fullDescription: `We specialize in creating custom B2B eCommerce stores that handle the unique challenges of business-to-business commerce. Our solutions support complex pricing structures, bulk ordering, customer-specific pricing, custom checkout flows, and seamless integration with your existing systems.

Whether you're on BigCommerce, Shopify Plus, or need a completely custom solution, we build stores that streamline your B2B sales process and delight your wholesale customers.`,
    icon: 'Store',
    features: [
      'Custom pricing tiers and customer-specific pricing',
      'Bulk ordering and quote management',
      'Advanced user roles and permissions',
      'Integration with ERP and inventory systems',
      'Custom checkout and payment workflows',
      'Multi-location and multi-currency support',
    ],
    path: '/services/b2b-stores',
  },
  {
    id: 'consulting',
    title: 'Marketing Consulting',
    shortDescription: 'Strategic eCommerce marketing consulting to grow your online business through data-driven strategies and optimization.',
    fullDescription: `Our marketing consulting services help eCommerce businesses maximize their growth potential. We combine deep eCommerce expertise with proven marketing strategies to drive traffic, increase conversions, and boost revenue.

From developing comprehensive marketing strategies to optimizing your existing campaigns, we provide actionable insights and hands-on support to achieve your business goals.`,
    icon: 'TrendingUp',
    features: [
      'eCommerce strategy development',
      'Conversion rate optimization (CRO)',
      'Customer acquisition and retention strategies',
      'Marketing analytics and attribution',
      'SEO and content marketing',
      'Paid advertising optimization',
    ],
    path: '/services/consulting',
  },
  {
    id: 'custom-apps',
    title: 'Custom App Development',
    shortDescription: 'Build powerful custom applications for BigCommerce and Shopify that extend functionality and drive business growth.',
    fullDescription: `We develop custom applications that solve real business problems for eCommerce merchants. Our apps like Algoboost.io and CelebrationApp.Pro are trusted by thousands of businesses worldwide.

Whether you need a custom integration, a unique feature, or a completely new app for the Shopify or BigCommerce app stores, our team has the expertise to bring your vision to life.`,
    icon: 'Code2',
    features: [
      'Custom Shopify and BigCommerce apps',
      'Platform integrations and API development',
      'Unique features tailored to your business',
      'App store publication and marketing',
      'Ongoing maintenance and support',
      'Scalable architecture for growth',
    ],
    path: '/services/custom-apps',
  },
];
