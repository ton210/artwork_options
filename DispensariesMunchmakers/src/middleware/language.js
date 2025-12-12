const translator = require('../services/translator');

// Detect language from URL or cookies
function detectLanguage(req, res, next) {
  // Check for language in URL (e.g., /es/dispensaries/california)
  const urlParts = req.path.split('/').filter(p => p);
  const firstPart = urlParts[0];

  // Check if first part is a language code
  if (firstPart && translator.isSupported(firstPart)) {
    req.language = firstPart;
    req.languageName = translator.getLanguageName(firstPart);
    // Remove language from path for routing
    req.originalPath = req.path;
    req.path = '/' + urlParts.slice(1).join('/');
  } else {
    // Check cookie
    const cookieLang = req.cookies?.language;
    if (cookieLang && translator.isSupported(cookieLang)) {
      req.language = cookieLang;
      req.languageName = translator.getLanguageName(cookieLang);
    } else {
      // Default to English
      req.language = 'en';
      req.languageName = 'English';
    }
  }

  // Make translator available to routes
  req.translator = translator;

  // Helper function for views
  res.locals.__ = async (key, text) => {
    if (req.language === 'en') return text;
    return await translator.translate(key, text, req.language);
  };

  res.locals.currentLanguage = req.language;
  res.locals.currentLanguageName = req.languageName;
  res.locals.supportedLanguages = translator.getSupportedLanguages();

  next();
}

// Set language preference cookie
function setLanguagePreference(req, res, next) {
  if (req.language && req.language !== 'en') {
    res.cookie('language', req.language, {
      maxAge: 30 * 24 * 60 * 60 * 1000, // 30 days
      httpOnly: true
    });
  }
  next();
}

module.exports = {
  detectLanguage,
  setLanguagePreference
};
