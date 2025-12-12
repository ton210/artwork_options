const express = require('express');
const router = express.Router();

// Language routes - detect language from URL and rewrite path
router.all('/:lang(es|fr|de|nl|pt)', (req, res, next) => {
  req.language = req.params.lang;
  req.url = '/';
  req.path = '/';
  console.log(`[LANG-ROUTE] Detected ${req.language} - rewriting to /`);
  next(); // Continue to normal middleware and routes
});

router.all('/:lang(es|fr|de|nl|pt)/*', (req, res, next) => {
  req.language = req.params.lang;
  const restOfPath = req.params[0];
  req.url = '/' + restOfPath + (req.url.includes('?') ? req.url.substring(req.url.indexOf('?')) : '');
  req.path = '/' + restOfPath;
  console.log(`[LANG-ROUTE] Detected ${req.language} - rewriting /${restOfPath}`);
  next(); // Continue to normal middleware and routes
});

module.exports = router;
