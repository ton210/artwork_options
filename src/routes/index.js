const express = require('express');
const router = express.Router();
const { State } = require('../models/State');
const db = require('../config/database');

// Homepage
router.get('/', async (req, res) => {
  try {
    // Get all states with dispensary counts
    const states = await db.query(`
      SELECT s.*, COUNT(DISTINCT d.id) as dispensary_count
      FROM states s
      LEFT JOIN counties c ON s.id = c.state_id
      LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
      GROUP BY s.id
      ORDER BY s.name ASC
    `);

    // Get overall stats
    const stats = await db.query(`
      SELECT
        COUNT(DISTINCT d.id) as total_dispensaries,
        COUNT(DISTINCT c.id) as total_counties,
        COUNT(DISTINCT s.id) as total_states,
        AVG(d.google_rating) as avg_rating
      FROM states s
      LEFT JOIN counties c ON s.id = c.state_id
      LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
    `);

    res.render('home', {
      title: 'Top Dispensaries 2026 - Find the Best Cannabis Dispensaries',
      states: states.rows,
      stats: stats.rows[0],
      meta: {
        description: 'Discover the top-rated cannabis dispensaries across 24+ legal states. User-voted rankings based on reviews, ratings, and community feedback.',
        keywords: 'cannabis dispensary, marijuana dispensary, weed dispensary, top dispensaries, dispensary rankings'
      }
    });
  } catch (error) {
    console.error('Error loading homepage:', error);
    res.status(500).send('Error loading homepage');
  }
});

// Robots.txt
router.get('/robots.txt', (req, res) => {
  res.type('text/plain');
  res.send(`User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/

Sitemap: ${req.protocol}://${req.get('host')}/sitemap.xml
`);
});

// Sitemap
router.get('/sitemap.xml', async (req, res) => {
  try {
    const baseUrl = `${req.protocol}://${req.get('host')}`;

    let xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>${baseUrl}/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
`;

    // Add states
    const states = await State.findAll();
    for (const state of states) {
      xml += `  <url>
    <loc>${baseUrl}/dispensaries/${state.slug}</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
`;
    }

    // Add counties
    const counties = await db.query(`
      SELECT c.slug as county_slug, s.slug as state_slug
      FROM counties c
      JOIN states s ON c.state_id = s.id
    `);

    for (const county of counties.rows) {
      xml += `  <url>
    <loc>${baseUrl}/dispensaries/${county.state_slug}/${county.county_slug}</loc>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>
`;
    }

    // Add dispensaries
    const dispensaries = await db.query(`
      SELECT slug, updated_at
      FROM dispensaries
      WHERE is_active = true
      ORDER BY updated_at DESC
    `);

    for (const dispensary of dispensaries.rows) {
      const lastMod = new Date(dispensary.updated_at).toISOString().split('T')[0];
      xml += `  <url>
    <loc>${baseUrl}/dispensary/${dispensary.slug}</loc>
    <lastmod>${lastMod}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
`;
    }

    xml += '</urlset>';

    res.header('Content-Type', 'application/xml');
    res.send(xml);
  } catch (error) {
    console.error('Error generating sitemap:', error);
    res.status(500).send('Error generating sitemap');
  }
});

module.exports = router;
