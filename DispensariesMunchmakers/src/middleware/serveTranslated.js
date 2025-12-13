const db = require('../config/database');

// Middleware to serve pre-translated pages from cache
async function serveTranslatedPage(req, res, next) {
  const lang = req.language;

  // Only for non-English languages
  if (!lang || lang === 'en') {
    return next();
  }

  // Skip API and admin
  if (req.path.startsWith('/api') || req.path.startsWith('/admin')) {
    return next();
  }

  try {
    const pageKey = `page:${req.path}`;

    // Check if we have a pre-translated version
    const result = await db.query(
      'SELECT translated_text FROM translations WHERE content_key = $1 AND target_language = $2',
      [pageKey, lang]
    );

    if (result.rows.length > 0) {
      console.log(`[SERVE-TRANSLATED] Serving cached ${lang} version of ${req.path}`);
      return res.send(result.rows[0].translated_text);
    }

    // No cached version, continue to render normally
    console.log(`[SERVE-TRANSLATED] No cached ${lang} version for ${req.path}, rendering English`);
    next();

  } catch (error) {
    console.error('[SERVE-TRANSLATED] Error:', error);
    next();
  }
}

module.exports = { serveTranslatedPage };
