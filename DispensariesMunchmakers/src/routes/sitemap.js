const express = require('express');
const router = express.Router();
const SitemapGenerator = require('../services/sitemapGenerator');

// Sitemap index
router.get('/sitemap.xml', async (req, res) => {
  try {
    const generator = new SitemapGenerator(process.env.BASE_URL);
    const xml = await generator.generateSitemapIndex();

    res.header('Content-Type', 'application/xml');
    res.send(xml);
  } catch (error) {
    console.error('Error generating sitemap index:', error);
    res.status(500).send('Error generating sitemap');
  }
});

// Main pages sitemap
router.get('/sitemap-main.xml', async (req, res) => {
  try {
    const generator = new SitemapGenerator(process.env.BASE_URL);
    const xml = await generator.generateMainSitemap();

    res.header('Content-Type', 'application/xml');
    res.send(xml);
  } catch (error) {
    console.error('Error generating main sitemap:', error);
    res.status(500).send('Error generating sitemap');
  }
});

// States sitemap
router.get('/sitemap-states.xml', async (req, res) => {
  try {
    const generator = new SitemapGenerator(process.env.BASE_URL);
    const xml = await generator.generateStatesSitemap();

    res.header('Content-Type', 'application/xml');
    res.send(xml);
  } catch (error) {
    console.error('Error generating states sitemap:', error);
    res.status(500).send('Error generating sitemap');
  }
});

// Counties sitemap
router.get('/sitemap-counties.xml', async (req, res) => {
  try {
    const generator = new SitemapGenerator(process.env.BASE_URL);
    const xml = await generator.generateCountiesSitemap();

    res.header('Content-Type', 'application/xml');
    res.send(xml);
  } catch (error) {
    console.error('Error generating counties sitemap:', error);
    res.status(500).send('Error generating sitemap');
  }
});

// Dispensaries sitemap
router.get('/sitemap-dispensaries.xml', async (req, res) => {
  try {
    const generator = new SitemapGenerator(process.env.BASE_URL);
    const xml = await generator.generateDispensariesSitemap();

    res.header('Content-Type', 'application/xml');
    res.send(xml);
  } catch (error) {
    console.error('Error generating dispensaries sitemap:', error);
    res.status(500).send('Error generating sitemap');
  }
});

// Brands sitemap
router.get('/sitemap-brands.xml', async (req, res) => {
  try {
    const generator = new SitemapGenerator(process.env.BASE_URL);
    const xml = await generator.generateBrandsSitemap();

    res.header('Content-Type', 'application/xml');
    res.send(xml);
  } catch (error) {
    console.error('Error generating brands sitemap:', error);
    res.status(500).send('Error generating sitemap');
  }
});

// Tags sitemap (best-edibles, best-flower, etc. for each state)
router.get('/sitemap-tags.xml', async (req, res) => {
  try {
    const generator = new SitemapGenerator(process.env.BASE_URL);
    const xml = await generator.generateTagsSitemap();

    res.header('Content-Type', 'application/xml');
    res.send(xml);
  } catch (error) {
    console.error('Error generating tags sitemap:', error);
    res.status(500).send('Error generating sitemap');
  }
});

// HTML sitemap page (for users)
router.get('/sitemap', async (req, res) => {
  try {
    const generator = new SitemapGenerator(process.env.BASE_URL);
    const data = await generator.generateHtmlSitemap();

    res.render('pages/sitemap', {
      title: 'Sitemap - Top Dispensaries 2026',
      baseUrl: process.env.BASE_URL || 'http://localhost:3000',
      ...data
    });
  } catch (error) {
    console.error('Error loading sitemap page:', error);
    res.status(500).send('Error loading sitemap');
  }
});

module.exports = router;
