const express = require('express');
const router = express.Router();

// Language route handlers - redirect to English version with language cookie set
router.get('/:lang(es|fr|de|nl|pt)', (req, res) => {
  const { lang } = req.params;

  // Set language cookie
  res.cookie('language', lang, {
    maxAge: 30 * 24 * 60 * 60 * 1000, // 30 days
    httpOnly: true
  });

  // Redirect to homepage
  res.redirect('/');
});

router.get('/:lang(es|fr|de|nl|pt)/*', (req, res) => {
  const { lang } = req.params;
  const path = req.params[0];

  // Set language cookie
  res.cookie('language', lang, {
    maxAge: 30 * 24 * 60 * 60 * 1000,
    httpOnly: true
  });

  // Redirect to English version
  res.redirect('/' + path);
});

module.exports = router;
