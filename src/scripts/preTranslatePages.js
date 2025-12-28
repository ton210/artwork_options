require('dotenv').config();
const axios = require('axios');
const db = require('../config/database');
const translator = require('../services/translator');

const BASE_URL = process.env.BASE_URL || 'https://bestdispensaries.munchmakers.com';
const LANGUAGES = ['es', 'fr', 'de', 'nl', 'pt'];

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function getPagesTоTranslate() {
  // Get all state/province pages
  const states = await db.query('SELECT slug FROM states ORDER BY name');

  const pages = [
    '/', // Homepage
    ...states.rows.map(s => `/dispensaries/${s.slug}`),
    '/guides/what-makes-great-dispensary',
    '/guides/first-time-visitor',
    '/guides/how-to-choose',
    '/guides/dispensary-etiquette'
  ];

  return pages;
}

async function translateAndCachePage(url, targetLang) {
  try {
    console.log(`Fetching: ${BASE_URL}${url}`);

    // Fetch the English page
    const response = await axios.get(`${BASE_URL}${url}`, {
      timeout: 30000,
      headers: {
        'User-Agent': 'PreTranslation Bot'
      }
    });

    let html = response.data;

    // Translate key phrases
    const translations = [
      'Top Dispensaries',
      'Find the best cannabis dispensaries',
      'Browse by State',
      'United States',
      'Canada',
      'View Rankings',
      'dispensaries',
      'Popular States',
      'Dispensary Guides',
      'All rights reserved'
    ];

    console.log(`Translating to ${targetLang}...`);

    for (const text of translations) {
      const key = `${url}:${text}:${targetLang}`;
      const translated = await translator.translate(key, text, targetLang);

      if (translated && translated !== text) {
        const regex = new RegExp(text, 'gi');
        html = html.replace(regex, translated);
      }

      await delay(100); // Rate limit
    }

    // Rewrite URLs to include language prefix (exclude static assets and language switcher links)
    // Excludes: //, http, #, language prefixes with slash (es/, fr/), bare language codes (/es", /fr"), and root path (/")
    html = html.replace(
      /href="\/(?!\/|http|#|"|es\/|fr\/|de\/|nl\/|pt\/|es"|fr"|de"|nl"|pt")(?!.*\.[a-z0-9]{2,5}")/gi,
      `href="/${targetLang}/`
    );

    // Save to database
    await db.query(
      `INSERT INTO translations (content_key, content_type, target_language, source_text, translated_text)
       VALUES ($1, $2, $3, $4, $5)
       ON CONFLICT (content_key, target_language)
       DO UPDATE SET translated_text = EXCLUDED.translated_text, updated_at = NOW()`,
      [`page:${url}`, 'full-page', targetLang, 'cached', html]
    );

    console.log(`✓ Cached ${targetLang} version of ${url}\n`);
    return true;

  } catch (error) {
    console.error(`❌ Error translating ${url} to ${targetLang}:`, error.message);
    return false;
  }
}

async function preTranslateAll() {
  console.log('🌐 Starting background pre-translation...\n');

  const pages = await getPagesTоTranslate();
  console.log(`Found ${pages.length} pages to translate into ${LANGUAGES.length} languages`);
  console.log(`Total translations needed: ${pages.length * LANGUAGES.length}\n`);

  let completed = 0;
  let failed = 0;

  for (const page of pages) {
    for (const lang of LANGUAGES) {
      const success = await translateAndCachePage(page, lang);

      if (success) {
        completed++;
      } else {
        failed++;
      }

      // Rate limit between pages
      await delay(500);
    }
  }

  console.log(`\n✅ Pre-translation complete!`);
  console.log(`   Completed: ${completed}`);
  console.log(`   Failed: ${failed}`);

  await db.pool.end();
}

if (require.main === module) {
  preTranslateAll()
    .then(() => process.exit(0))
    .catch(err => {
      console.error('Fatal error:', err);
      process.exit(1);
    });
}

module.exports = { preTranslateAll };
