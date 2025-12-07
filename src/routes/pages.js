const express = require('express');
const router = express.Router();

// Contact page
router.get('/contact', (req, res) => {
  res.render('pages/contact', {
    title: 'Contact Us - Top Dispensaries 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000',
    meta: {
      description: 'Contact Top Dispensaries 2026 for inquiries, partnerships, or to claim your dispensary listing.',
      keywords: 'contact, dispensary contact, claim listing, partnerships'
    }
  });
});

// Claim listing page
router.get('/claim', (req, res) => {
  res.render('pages/claim', {
    title: 'Claim Your Dispensary Listing - Top Dispensaries 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000',
    meta: {
      description: 'Claim your cannabis dispensary listing to update information, access analytics, and get wholesale custom products from MunchMakers.',
      keywords: 'claim dispensary, verify listing, dispensary owner, manage listing'
    }
  });
});

// Privacy Policy
router.get('/privacy', (req, res) => {
  res.render('pages/privacy', {
    title: 'Privacy Policy - Top Dispensaries 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000',
    meta: {
      description: 'Privacy policy for Top Dispensaries 2026. Learn how we collect, use, and protect your data.',
      keywords: 'privacy policy, data protection, privacy'
    }
  });
});

// Terms of Service
router.get('/terms', (req, res) => {
  res.render('pages/terms', {
    title: 'Terms of Service - Top Dispensaries 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000',
    meta: {
      description: 'Terms of service for using Top Dispensaries 2026 ranking platform.',
      keywords: 'terms of service, user agreement, terms and conditions'
    }
  });
});

// Login page
router.get('/login', (req, res) => {
  res.render('login', {
    title: 'Login - Top Dispensaries 2026'
  });
});

// Register page
router.get('/register', (req, res) => {
  res.render('register', {
    title: 'Sign Up - Top Dispensaries 2026'
  });
});

// Account page
router.get('/account', (req, res) => {
  if (!req.session.userId) {
    return res.redirect('/login');
  }
  res.render('account', {
    title: 'My Account - Top Dispensaries 2026'
  });
});

module.exports = router;
