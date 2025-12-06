const express = require('express');
const router = express.Router();

// Contact page
router.get('/contact', (req, res) => {
  res.render('pages/contact', {
    title: 'Contact Us - Top Dispensaries 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000'
  });
});

// Claim listing page
router.get('/claim', (req, res) => {
  res.render('pages/claim', {
    title: 'Claim Your Dispensary Listing - Top Dispensaries 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000'
  });
});

// Privacy Policy
router.get('/privacy', (req, res) => {
  res.render('pages/privacy', {
    title: 'Privacy Policy - Top Dispensaries 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000'
  });
});

// Terms of Service
router.get('/terms', (req, res) => {
  res.render('pages/terms', {
    title: 'Terms of Service - Top Dispensaries 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000'
  });
});

module.exports = router;
