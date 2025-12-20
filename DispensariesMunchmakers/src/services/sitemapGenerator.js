const db = require('../config/database');

class SitemapGenerator {
  constructor(baseUrl) {
    this.baseUrl = baseUrl || 'https://bestdispensaries.munchmakers.com';
    this.languages = ['en', 'es', 'fr', 'de', 'nl', 'pt']; // All supported languages
  }

  escapeXml(str) {
    if (!str) return '';
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&apos;');
  }

  formatDate(date) {
    return new Date(date).toISOString().split('T')[0];
  }

  createUrl(loc, lastmod, changefreq = 'weekly', priority = '0.5') {
    return `
  <url>
    <loc>${this.escapeXml(this.baseUrl + loc)}</loc>
    ${lastmod ? `<lastmod>${this.formatDate(lastmod)}</lastmod>` : ''}
    <changefreq>${changefreq}</changefreq>
    <priority>${priority}</priority>
  </url>`;
  }

  async generateSitemapIndex() {
    const xml = `<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc>${this.baseUrl}/sitemap-main.xml</loc>
    <lastmod>${this.formatDate(new Date())}</lastmod>
  </sitemap>
  <sitemap>
    <loc>${this.baseUrl}/sitemap-states.xml</loc>
    <lastmod>${this.formatDate(new Date())}</lastmod>
  </sitemap>
  <sitemap>
    <loc>${this.baseUrl}/sitemap-counties.xml</loc>
    <lastmod>${this.formatDate(new Date())}</lastmod>
  </sitemap>
  <sitemap>
    <loc>${this.baseUrl}/sitemap-dispensaries.xml</loc>
    <lastmod>${this.formatDate(new Date())}</lastmod>
  </sitemap>
  <sitemap>
    <loc>${this.baseUrl}/sitemap-brands.xml</loc>
    <lastmod>${this.formatDate(new Date())}</lastmod>
  </sitemap>
  <sitemap>
    <loc>${this.baseUrl}/sitemap-tags.xml</loc>
    <lastmod>${this.formatDate(new Date())}</lastmod>
  </sitemap>
  <sitemap>
    <loc>${this.baseUrl}/sitemap-cities.xml</loc>
    <lastmod>${this.formatDate(new Date())}</lastmod>
  </sitemap>
</sitemapindex>`;

    return xml;
  }

  async generateMainSitemap() {
    let urls = [];

    // Homepage in all languages - highest priority
    for (const lang of this.languages) {
      const url = lang === 'en' ? '/' : `/${lang}/`;
      urls.push(this.createUrl(url, new Date(), 'daily', '1.0'));
    }

    // Main pages in all languages
    const mainPages = [
      { path: '/brands', changefreq: 'daily', priority: '0.9' },
      { path: '/contact', changefreq: 'monthly', priority: '0.3' },
      { path: '/claim', changefreq: 'monthly', priority: '0.4' },
      { path: '/privacy', changefreq: 'yearly', priority: '0.2' },
      { path: '/terms', changefreq: 'yearly', priority: '0.2' }
    ];

    for (const page of mainPages) {
      for (const lang of this.languages) {
        const url = lang === 'en' ? page.path : `/${lang}${page.path}`;
        urls.push(this.createUrl(url, new Date(), page.changefreq, page.priority));
      }
    }

    return this.wrapUrlset(urls.join(''));
  }

  async generateStatesSitemap() {
    const states = await db.query(
      'SELECT slug, name FROM states ORDER BY name'
    );

    const urls = [];
    for (const state of states.rows) {
      // Add state page in all languages
      for (const lang of this.languages) {
        const url = lang === 'en' ? `/dispensaries/${state.slug}` : `/${lang}/dispensaries/${state.slug}`;
        urls.push(this.createUrl(url, new Date(), 'daily', '0.9'));
      }
    }

    return this.wrapUrlset(urls.join(''));
  }

  async generateCountiesSitemap() {
    const counties = await db.query(`
      SELECT c.slug, s.slug as state_slug
      FROM counties c
      JOIN states s ON s.id = c.state_id
      ORDER BY s.name, c.name
    `);

    const urls = [];
    for (const county of counties.rows) {
      // Add county page in all languages
      for (const lang of this.languages) {
        const url = lang === 'en' ? `/dispensaries/${county.state_slug}/${county.slug}` : `/${lang}/dispensaries/${county.state_slug}/${county.slug}`;
        urls.push(this.createUrl(url, new Date(), 'weekly', '0.7'));
      }
    }

    return this.wrapUrlset(urls.join(''));
  }

  async generateDispensariesSitemap() {
    const dispensaries = await db.query(`
      SELECT d.slug, d.updated_at, d.google_rating, d.google_review_count
      FROM dispensaries d
      WHERE d.is_active = true
      ORDER BY d.google_review_count DESC, d.google_rating DESC
    `);

    const urls = [];
    for (const dispensary of dispensaries.rows) {
      // Higher priority for highly rated dispensaries
      let priority = '0.6';
      if (dispensary.google_rating >= 4.5 && dispensary.google_review_count > 500) {
        priority = '0.8';
      } else if (dispensary.google_rating >= 4.0) {
        priority = '0.7';
      }

      // Add dispensary page in all languages
      for (const lang of this.languages) {
        const url = lang === 'en' ? `/dispensary/${dispensary.slug}` : `/${lang}/dispensary/${dispensary.slug}`;
        urls.push(this.createUrl(url, dispensary.updated_at, 'weekly', priority));
      }
    }

    return this.wrapUrlset(urls.join(''));
  }

  async generateBrandsSitemap() {
    const brands = await db.query(`
      SELECT b.slug, b.updated_at, b.location_count
      FROM brands b
      WHERE b.location_count > 0
      ORDER BY b.location_count DESC
    `);

    const urls = [];
    for (const brand of brands.rows) {
      // Higher priority for franchises
      const priority = brand.location_count > 3 ? '0.8' : '0.6';

      // Add brand page in all languages
      for (const lang of this.languages) {
        const url = lang === 'en' ? `/brands/${brand.slug}` : `/${lang}/brands/${brand.slug}`;
        urls.push(this.createUrl(url, brand.updated_at, 'weekly', priority));
      }
    }

    return this.wrapUrlset(urls.join(''));
  }

  async generateTagsSitemap() {
    // Minimum dispensaries required (must match MIN_DISPENSARIES_FOR_TAG_PAGE in routes/dispensaries.js)
    const MIN_DISPENSARIES = 3;

    // Query for valid state/tag combinations that have enough dispensaries
    const validCombinations = await db.query(`
      SELECT s.slug as state_slug, dt.tag, COUNT(DISTINCT d.id) as count
      FROM dispensary_tags dt
      JOIN dispensaries d ON dt.dispensary_id = d.id
      JOIN counties c ON d.county_id = c.id
      JOIN states s ON c.state_id = s.id
      WHERE d.is_active = true
      GROUP BY s.slug, dt.tag
      HAVING COUNT(DISTINCT d.id) >= $1
      ORDER BY s.slug, dt.tag
    `, [MIN_DISPENSARIES]);

    const urls = [];

    // Generate URLs only for valid state/tag combinations
    for (const combo of validCombinations.rows) {
      for (const lang of this.languages) {
        const url = lang === 'en'
          ? `/dispensaries/${combo.state_slug}/best-${combo.tag}`
          : `/${lang}/dispensaries/${combo.state_slug}/best-${combo.tag}`;
        urls.push(this.createUrl(url, new Date(), 'weekly', '0.6'));
      }
    }

    return this.wrapUrlset(urls.join(''));
  }

  async generateCitiesSitemap() {
    // Get all cities with 3+ dispensaries
    const cities = await db.query(`
      SELECT d.city, s.slug as state_slug, COUNT(*) as cnt
      FROM dispensaries d
      JOIN counties c ON d.county_id = c.id
      JOIN states s ON c.state_id = s.id
      WHERE d.city IS NOT NULL AND d.city <> '' AND d.is_active = true
      GROUP BY d.city, s.slug
      HAVING COUNT(*) >= 3
      ORDER BY COUNT(*) DESC
    `);

    const urls = [];
    for (const city of cities.rows) {
      const citySlug = city.city.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
      // Higher priority for cities with more dispensaries
      const priority = city.cnt >= 20 ? '0.8' : city.cnt >= 10 ? '0.7' : '0.6';

      // Add city page in all languages
      for (const lang of this.languages) {
        const url = lang === 'en' ? `/dispensaries/${city.state_slug}/city/${citySlug}` : `/${lang}/dispensaries/${city.state_slug}/city/${citySlug}`;
        urls.push(this.createUrl(url, new Date(), 'weekly', priority));
      }
    }

    return this.wrapUrlset(urls.join(''));
  }

  wrapUrlset(urls) {
    return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
${urls}
</urlset>`;
  }

  async generateHtmlSitemap() {
    // Get all data
    const [states, brands, pages] = await Promise.all([
      db.query('SELECT slug, name FROM states ORDER BY name'),
      db.query(`
        SELECT b.slug, b.name, COUNT(d.id) as location_count
        FROM brands b
        LEFT JOIN dispensaries d ON d.brand_id = b.id AND d.is_active = true
        GROUP BY b.id, b.slug, b.name
        HAVING COUNT(d.id) > 0
        ORDER BY COUNT(d.id) DESC
      `),
      Promise.resolve([
        { path: '/', name: 'Home' },
        { path: '/brands', name: 'Top Brands' },
        { path: '/contact', name: 'Contact Us' },
        { path: '/claim', name: 'Claim Your Listing' }
      ])
    ]);

    return {
      states: states.rows,
      brands: brands.rows,
      pages
    };
  }
}

module.exports = SitemapGenerator;
 
