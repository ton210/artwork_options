const db = require('../config/database');

class SitemapGenerator {
  constructor(baseUrl) {
    this.baseUrl = baseUrl || 'https://bestdispensaries.munchmakers.com';
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
</sitemapindex>`;

    return xml;
  }

  async generateMainSitemap() {
    let urls = [];

    // Homepage - highest priority
    urls.push(this.createUrl('/', new Date(), 'daily', '1.0'));

    // Main pages
    urls.push(this.createUrl('/brands', new Date(), 'daily', '0.9'));
    urls.push(this.createUrl('/contact', new Date(), 'monthly', '0.3'));
    urls.push(this.createUrl('/claim', new Date(), 'monthly', '0.4'));
    urls.push(this.createUrl('/privacy', new Date(), 'yearly', '0.2'));
    urls.push(this.createUrl('/terms', new Date(), 'yearly', '0.2'));

    return this.wrapUrlset(urls.join(''));
  }

  async generateStatesSitemap() {
    const states = await db.query(
      'SELECT slug, name FROM states ORDER BY name'
    );

    const urls = states.rows.map(state =>
      this.createUrl(
        `/dispensaries/${state.slug}`,
        new Date(),
        'daily',
        '0.9'
      )
    );

    return this.wrapUrlset(urls.join(''));
  }

  async generateCountiesSitemap() {
    const counties = await db.query(`
      SELECT c.slug, s.slug as state_slug
      FROM counties c
      JOIN states s ON s.id = c.state_id
      ORDER BY s.name, c.name
    `);

    const urls = counties.rows.map(county =>
      this.createUrl(
        `/dispensaries/${county.state_slug}/${county.slug}`,
        new Date(),
        'weekly',
        '0.7'
      )
    );

    return this.wrapUrlset(urls.join(''));
  }

  async generateDispensariesSitemap() {
    const dispensaries = await db.query(`
      SELECT d.slug, d.updated_at, d.google_rating, d.google_review_count
      FROM dispensaries d
      WHERE d.is_active = true
      ORDER BY d.google_review_count DESC, d.google_rating DESC
    `);

    const urls = dispensaries.rows.map(dispensary => {
      // Higher priority for highly rated dispensaries
      let priority = '0.6';
      if (dispensary.google_rating >= 4.5 && dispensary.google_review_count > 500) {
        priority = '0.8';
      } else if (dispensary.google_rating >= 4.0) {
        priority = '0.7';
      }

      return this.createUrl(
        `/dispensary/${dispensary.slug}`,
        dispensary.updated_at,
        'weekly',
        priority
      );
    });

    return this.wrapUrlset(urls.join(''));
  }

  async generateBrandsSitemap() {
    const brands = await db.query(`
      SELECT b.slug, b.updated_at, b.location_count
      FROM brands b
      WHERE b.location_count > 0
      ORDER BY b.location_count DESC
    `);

    const urls = brands.rows.map(brand => {
      // Higher priority for franchises
      const priority = brand.location_count > 3 ? '0.8' : '0.6';

      return this.createUrl(
        `/brands/${brand.slug}`,
        brand.updated_at,
        'weekly',
        priority
      );
    });

    return this.wrapUrlset(urls.join(''));
  }

  async generateTagsSitemap() {
    // All valid tag slugs
    const tags = [
      'edibles', 'flower', 'vapes', 'concentrates', 'pre-rolls',
      'tinctures', 'topicals', 'delivery', 'curbside-pickup',
      'recreational', 'medical', 'online-ordering'
    ];

    // Get all states
    const states = await db.query('SELECT slug FROM states ORDER BY name');

    const urls = [];

    // Generate URLs for each state + tag combination
    for (const state of states.rows) {
      for (const tag of tags) {
        urls.push(this.createUrl(
          `/dispensaries/${state.slug}/best-${tag}`,
          new Date(),
          'weekly',
          '0.6'
        ));
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
