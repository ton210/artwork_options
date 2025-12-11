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

// Guide: What Makes a Great Dispensary
router.get('/guides/what-makes-great-dispensary', (req, res) => {
  res.render('pages/what-makes-great-dispensary', {
    title: 'What Makes a Great Dispensary? | Expert Guide 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000',
    meta: {
      description: 'Discover the key factors that make a cannabis dispensary exceptional: product quality, knowledgeable staff, atmosphere, pricing, and customer service.',
      keywords: 'best dispensary, what makes good dispensary, cannabis dispensary quality, dispensary customer service, budtender knowledge'
    }
  });
});

// Guide: First-Time Visitor
router.get('/guides/first-time-visitor', (req, res) => {
  res.render('pages/first-time-visitor', {
    title: 'First-Time Dispensary Visitor Guide | What to Expect 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000',
    meta: {
      description: 'Complete guide for first-time dispensary visitors: what to bring, what to expect, how to order, payment methods, and tips for a smooth experience.',
      keywords: 'first time dispensary, dispensary first visit, what to bring to dispensary, dispensary guide, cannabis dispensary tips'
    }
  });
});

// Guide: How to Choose a Dispensary
router.get('/guides/how-to-choose', (req, res) => {
  res.render('pages/how-to-choose', {
    title: 'How to Choose a Cannabis Dispensary | Complete Selection Guide 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000',
    meta: {
      description: 'Expert guide to choosing the right cannabis dispensary: compare prices, quality, location, service, and find the perfect match for your needs.',
      keywords: 'choose dispensary, best dispensary near me, compare dispensaries, find dispensary, cannabis dispensary selection'
    }
  });
});

// Guide: Dispensary Etiquette
router.get('/guides/dispensary-etiquette', (req, res) => {
  res.render('pages/dispensary-etiquette', {
    title: 'Cannabis Dispensary Etiquette Guide | Do\'s and Don\'ts 2026',
    baseUrl: process.env.BASE_URL || 'http://localhost:3000',
    meta: {
      description: 'Complete dispensary etiquette guide: proper behavior, what to avoid, tipping guidelines, and how to be a respectful cannabis customer.',
      keywords: 'dispensary etiquette, cannabis etiquette, dispensary rules, budtender tips, dispensary behavior, marijuana dispensary manners'
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
