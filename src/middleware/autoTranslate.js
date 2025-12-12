const translator = require('../services/translator');

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));
const pageCache = new Map();

async function translateKeyElements(html, targetLang, pageUrl) {
  if (!html || targetLang === 'en') return html;

  const cacheKey = `${pageUrl}:${targetLang}`;
  if (pageCache.has(cacheKey)) {
    return pageCache.get(cacheKey);
  }

  console.log(`[TRANSLATE] ${pageUrl} -> ${targetLang}`);

  try {
    // Use regex to find and replace key content (faster than cheerio)
    let translated = html;

    // Translate key phrases with caching
    const translations = [
      ['Top Dispensaries', 'top-dispensaries'],
      ['Find the best cannabis dispensaries', 'find-best'],
      ['Browse by State', 'browse-state'],
      ['United States', 'us'],
      ['Canada', 'canada'],
      ['dispensaries', 'dispensaries-word'],
      ['View Rankings', 'view-rankings'],
      ['About', 'about'],
      ['Contact', 'contact'],
      ['Popular States', 'popular-states'],
      ['Dispensary Guides', 'guides'],
      ['What Makes a Great Dispensary', 'great-disp'],
      ['First-Time Visitor Guide', 'first-time'],
      ['How to Choose a Dispensary', 'how-choose'],
      ['Dispensary Etiquette', 'etiquette'],
      ['All rights reserved', 'rights'],
      ['Cannabis Laws', 'cannabis-laws'],
      ['Browse by County', 'browse-county'],
      ['Best Dispensaries', 'best-disp']
    ];

    for (const [english, key] of translations) {
      const translatedText = await translator.translate(`${key}-${targetLang}`, english, targetLang);
      // Replace all occurrences
      const regex = new RegExp(english, 'g');
      translated = translated.replace(regex, translatedText);

      await delay(50); // Rate limit
    }

    pageCache.set(cacheKey, translated);
    console.log(`[TRANSLATE] Complete - cached`);
    return translated;

  } catch (error) {
    console.error('[TRANSLATE] Error:', error.message);
    return html;
  }
}

function autoTranslateMiddleware(req, res, next) {
  const originalRender = res.render.bind(res);

  res.render = function(view, options, callback) {
    const lang = req.language || 'en';

    if (lang === 'en' || !translator.isSupported(lang)) {
      return originalRender(view, options, callback);
    }

    if (req.path.startsWith('/admin') || req.path.startsWith('/api')) {
      return originalRender(view, options, callback);
    }

    originalRender(view, options, async (err, html) => {
      if (err) {
        if (callback) return callback(err);
        return next(err);
      }

      try {
        const translatedHTML = await translateKeyElements(html, lang, req.path);

        if (callback) {
          callback(null, translatedHTML);
        } else {
          res.send(translatedHTML);
        }
      } catch (error) {
        console.error('[TRANSLATE] Fatal:', error);
        if (callback) {
          callback(null, html);
        } else {
          res.send(html);
        }
      }
    });
  };

  next();
}

module.exports = {
  autoTranslateMiddleware,
  translateKeyElements
};
