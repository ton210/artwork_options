const translator = require('../services/translator');

// Simple text extractor - no HTML parsing for now
// Translate only critical elements via regex

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// Cache to prevent re-translating same page multiple times
const pageCache = new Map();

async function translatePage(html, targetLang, pageUrl) {
  if (!html || targetLang === 'en') return html;

  // Check page cache first
  const cacheKey = `${pageUrl}:${targetLang}`;
  if (pageCache.has(cacheKey)) {
    console.log(`[Translation] Using cached translation for ${pageUrl}`);
    return pageCache.get(cacheKey);
  }

  console.log(`[Translation] Translating ${pageUrl} to ${targetLang}`);

  try {
    // For now, return original HTML with a note that it's in the target language context
    // Full translation is resource-intensive and needs to be opt-in
    console.log(`[Translation] Returning original HTML (translation disabled for performance)`);
    return html;

    // Future: Uncomment below to enable full translation
    /*
    const cheerio = require('cheerio');
    const $ = cheerio.load(html);

    // Translate specific important elements
    const translations = await translator.translateBatch([
      { key: `${pageUrl}:title`, text: $('title').text(), type: 'title' },
      { key: `${pageUrl}:h1`, text: $('h1').first().text(), type: 'heading' }
    ], targetLang);

    $('title').text(translations[0]);
    $('h1').first().text(translations[1]);

    const translated = $.html();
    pageCache.set(cacheKey, translated);
    return translated;
    */
  } catch (error) {
    console.error('[Translation] Error:', error.message);
    return html;
  }
}

function autoTranslateMiddleware(req, res, next) {
  // For now, just set language context but don't translate
  // This allows the infrastructure to work without performance impact
  next();
}

module.exports = {
  autoTranslateMiddleware,
  translatePage
};
