import { MetadataRoute } from 'next';

export default function sitemap(): MetadataRoute.Sitemap {
  const baseUrl = 'https://greenlunar.com';
  const currentDate = new Date();

  const routes = [
    '',
    '/about',
    '/brands',
    '/team',
    '/contact',
    '/apps',
    '/apps/algoboost',
    '/apps/celebrationapp',
    '/services',
    '/services/consulting',
    '/services/custom-apps',
    '/services/b2b-stores',
  ];

  return routes.map((route) => ({
    url: `${baseUrl}${route}`,
    lastModified: currentDate,
    changeFrequency: route === '' ? 'weekly' : 'monthly',
    priority: route === '' ? 1 : route.split('/').length === 2 ? 0.8 : 0.6,
  }));
}
