const express = require('express');
const router = express.Router();
const { detectLanguage, setLanguagePreference } = require('../middleware/language');

// Apply language detection middleware to all language routes
router.use('/:lang(es|fr|de|nl|pt)*', detectLanguage, setLanguagePreference);

// Language homepage
router.get('/:lang(es|fr|de|nl|pt)', async (req, res, next) => {
  // Rewrite the path and forward to index route
  req.url = '/';
  req.path = '/';
  next('route'); // Skip to next route handler (index routes)
});

// Language-prefixed pages - strip language and forward to actual route
router.all('/:lang(es|fr|de|nl|pt)/*', (req, res, next) => {
  const lang = req.params.lang;
  const restOfPath = req.params[0];

  // Rewrite URL to remove language prefix
  req.url = '/' + restOfPath + (req.url.includes('?') ? req.url.substring(req.url.indexOf('?')) : '');
  req.path = '/' + restOfPath;

  // Language is already set by detectLanguage middleware
  next('route'); // Forward to actual route handlers
});

module.exports = router;
