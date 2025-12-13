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
    const languages = ['en', 'es', 'fr', 'de', 'nl', 'pt']; // All supported languages

    let xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
`;

    // Add homepage in all languages
    for (const lang of languages) {
      const url = lang === 'en' ? '/' : `/${lang}/`;
      xml += `  <url>
    <loc>${baseUrl}${url}</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
  </url>
`;
    }

    // Add states
    const states = await State.findAll();
    console.log(`Adding ${states.length} states in ${languages.length} languages...`);

    for (const state of states) {
      // Add state page in all languages
      for (const lang of languages) {
        const url = lang === 'en' ? `/dispensaries/${state.slug}` : `/${lang}/dispensaries/${state.slug}`;
        xml += `  <url>
    <loc>${baseUrl}${url}</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
  </url>
`;
      }
    }

    // Add counties
    const counties = await db.query(`
      SELECT c.slug as county_slug, s.slug as state_slug
      FROM counties c
      JOIN states s ON c.state_id = s.id
    `);

    console.log(`Adding ${counties.rows.length} counties in ${languages.length} languages...`);

    for (const county of counties.rows) {
      // Add county page in all languages
      for (const lang of languages) {
        const url = lang === 'en' ? `/dispensaries/${county.state_slug}/${county.county_slug}` : `/${lang}/dispensaries/${county.state_slug}/${county.county_slug}`;
        xml += `  <url>
    <loc>${baseUrl}${url}</loc>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
    <lastmod>${new Date().toISOString().split('T')[0]}</lastmod>
  </url>
`;
      }
    }

    // Add dispensaries
    const dispensaries = await db.query(`
      SELECT slug, updated_at
      FROM dispensaries
      WHERE is_active = true
      ORDER BY updated_at DESC
    `);

    console.log(`Adding ${dispensaries.rows.length} dispensaries in ${languages.length} languages...`);

    for (const dispensary of dispensaries.rows) {
      const lastMod = new Date(dispensary.updated_at).toISOString().split('T')[0];
      // Add dispensary page in all languages
      for (const lang of languages) {
        const url = lang === 'en' ? `/dispensary/${dispensary.slug}` : `/${lang}/dispensary/${dispensary.slug}`;
        xml += `  <url>
    <loc>${baseUrl}${url}</loc>
    <lastmod>${lastMod}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
`;
      }
    }

    xml += '</urlset>';

    // Write to file
    const sitemapPath = path.join(__dirname, '../public/sitemap.xml');
    fs.writeFileSync(sitemapPath, xml);

    const totalUrls = (states.length + counties.rows.length + dispensaries.rows.length + 1) * languages.length;
    console.log('✓ Sitemap generated successfully!');
    console.log(`  Location: ${sitemapPath}`);
    console.log(`  Total URLs: ${totalUrls} (${languages.length} languages)`);
    console.log(`  Languages: ${languages.join(', ')}`);

    process.exit(0);
  } catch (error) {
    console.error('Error generating sitemap:', error);
    process.exit(1);
  }
}

generateSitemap();
