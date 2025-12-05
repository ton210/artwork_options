#!/usr/bin/env node
require('dotenv').config();
const fs = require('fs');
const path = require('path');
const db = require('../config/database');
const { State } = require('../models/State');

async function generateSitemap() {
  console.log('Generating sitemap...');

  try {
    const baseUrl = process.env.BASE_URL || 'https://dispensaries.munchmakers.com';

    let xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>${baseUrl}/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
  </url>
`;

    // Add states
    const states = await State.findAll();
    console.log(`Adding ${states.length} states...`);

    for (const state of states) {
      xml += `  <url>
    <loc>${baseUrl}/dispensaries/${state.slug}</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
  </url>
`;
    }

    // Add counties
    const counties = await db.query(`
      SELECT c.slug as county_slug, s.slug as state_slug
      FROM counties c
      JOIN states s ON c.state_id = s.id
    `);

    console.log(`Adding ${counties.rows.length} counties...`);

    for (const county of counties.rows) {
      xml += `  <url>
    <loc>${baseUrl}/dispensaries/${county.state_slug}/${county.county_slug}</loc>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
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

    console.log(`Adding ${dispensaries.rows.length} dispensaries...`);

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

    // Write to file
    const sitemapPath = path.join(__dirname, '../public/sitemap.xml');
    fs.writeFileSync(sitemapPath, xml);

    console.log('✓ Sitemap generated successfully!');
    console.log(`  Location: ${sitemapPath}`);
    console.log(`  Total URLs: ${states.length + counties.rows.length + dispensaries.rows.length + 1}`);

    process.exit(0);
  } catch (error) {
    console.error('Error generating sitemap:', error);
    process.exit(1);
  }
}

generateSitemap();
