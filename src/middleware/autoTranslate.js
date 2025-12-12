const translator = require('../services/translator');

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));
const pageCache = new Map();

async function translateKeyElements(html, targetLang, pageUrl) {
  if (!html || targetLang === 'en') return html;

  const cacheKey = `${pageUrl}:${targetLang}`;
  if (pageCache.has(cacheKey)) {
    console.log(`[TRANSLATE] Using cache for ${pageUrl}`);
    return pageCache.get(cacheKey);
  }

  console.log(`[TRANSLATE] ${pageUrl} -> ${targetLang}`);

  try {
    let translated = html;

    // STEP 1: Rewrite all internal URLs to include language prefix
    console.log(`[TRANSLATE] Rewriting URLs for ${targetLang}`);

    // Rewrite href attributes for internal links
    translated = translated.replace(/href="\/(?!\/|http|#|es\/|fr\/|de\/|nl\/|pt\/)/g, `href="/${targetLang}/`);

    // Rewrite action attributes for forms
    translated = translated.replace(/action="\/(?!\/|http|es\/|fr\/|de\/|nl\/|pt\/)/g, `action="/${targetLang}/`);

    console.log(`[TRANSLATE] URLs rewritten`);

    // STEP 2: Translate key phrases
    const translations = [
      ['Top Dispensaries', 'top-dispensaries'],
      ['Find the best cannabis dispensaries', 'find-best'],
      ['Browse by State', 'browse-state'],
      ['United States', 'us'],
      ['Canada', 'canada'],
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

    let translatedCount = 0;
    for (const [english, key] of translations) {
      try {
        const translatedText = await translator.translate(`${key}-${targetLang}`, english, targetLang);
        if (translatedText && translatedText !== english) {
          const regex = new RegExp(english, 'gi');
          translated = translated.replace(regex, translatedText);
          translatedCount++;
        }
        await delay(50);
      } catch (err) {
        console.error(`[TRANSLATE] Failed to translate "${english}":`, err.message);
      }
    }

    console.log(`[TRANSLATE] Translated ${translatedCount} phrases`);

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

    console.log(`[RENDER] View: ${view}, Language: ${lang}, Path: ${req.path}`);

    if (lang === 'en' || !translator.isSupported(lang)) {
      console.log(`[RENDER] Skipping translation (English or unsupported)`);
      return originalRender(view, options, callback);
    }

    if (req.path.startsWith('/admin') || req.path.startsWith('/api')) {
      console.log(`[RENDER] Skipping translation (admin/api)`);
      return originalRender(view, options, callback);
    }

    console.log(`[RENDER] Will translate to ${lang}`);

    originalRender(view, options, async (err, html) => {
      if (err) {
        console.error('[RENDER] Error:', err);
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
