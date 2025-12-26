const express = require('express');
const router = express.Router();
const { State } = require('../models/State');
const SchemaGenerator = require('../utils/schemaGenerator');

// General FAQ
router.get('/', (req, res) => {
  const faqs = [
    {
      question: "How do I find a dispensary near me?",
      answer: "Use our 'Near Me' feature or browse by state and county. We list thousands of dispensaries across the US and Canada with ratings, reviews, and directions."
    },
    {
      question: "Are all dispensaries on your site licensed?",
      answer: "Yes, we only list licensed, legal cannabis dispensaries. All listings are verified from official sources like Google Places and state registries."
    },
    {
      question: "How are dispensaries ranked?",
      answer: "Rankings are based on multiple factors: user votes (30%), user reviews (20%), Google ratings (15%), page views (8%), and other engagement metrics. Our algorithm favors community feedback."
    },
    {
      question: "Can I leave a review for a dispensary?",
      answer: "Yes! Visit any dispensary page and scroll to the review section. You'll need to complete a reCAPTCHA to prevent spam. Reviews are limited to 3 per day."
    },
    {
      question: "Do I need a medical card to visit a dispensary?",
      answer: "It depends on your state. Some states are recreational (21+ with ID), some are medical-only (require medical card), and some have both options. Check the dispensary's details."
    },
    {
      question: "What should I bring to a dispensary?",
      answer: "Bring a valid government-issued ID (driver's license, passport, etc.). If it's a medical dispensary, you'll also need your medical marijuana card. Bring cash as many dispensaries don't accept cards."
    },
    {
      question: "How do I claim my dispensary listing?",
      answer: "Visit our Claim page and fill out the form. We'll verify your ownership and give you access to update information, view analytics, and manage your listing."
    },
    {
      question: "Can I vote for multiple dispensaries?",
      answer: "Yes, you can vote for as many dispensaries as you want. However, you can only vote once per dispensary per day to prevent manipulation."
    }
  ];

  const faqSchema = SchemaGenerator.generateFAQSchema(faqs);

  res.render('faq/general', {
    title: 'Frequently Asked Questions - Cannabis Dispensary Guide',
    faqs,
    faqSchema,
    baseUrl: process.env.BASE_URL || 'https://bestdispensaries.munchmakers.com',
    meta: {
      description: 'Common questions about finding and visiting cannabis dispensaries. Learn about our rankings, how to leave reviews, medical vs recreational, and more.',
      keywords: 'dispensary faq, cannabis questions, marijuana dispensary guide, dispensary help'
    }
  });
});

// State-specific FAQ
router.get('/:stateSlug', async (req, res) => {
  try {
    const state = await State.findBySlug(req.params.stateSlug);

    if (!state) {
      return res.status(404).render('404', { title: 'State Not Found' });
    }

    const faqs = [
      {
        question: `Is cannabis legal in ${state.name}?`,
        answer: `Yes, cannabis dispensaries operate legally in ${state.name}. Check individual dispensary listings to see if they're medical-only or recreational.`
      },
      {
        question: `How many dispensaries are in ${state.name}?`,
        answer: `We currently list dispensaries across ${state.name}. Visit our ${state.name} page to see all locations and rankings.`
      },
      {
        question: `What are the best dispensaries in ${state.name}?`,
        answer: `Our top-ranked dispensaries in ${state.name} are determined by community votes, user reviews, Google ratings, and engagement metrics. Check our ${state.name} rankings page for the current top 10.`
      },
      {
        question: `Do I need a medical card in ${state.name}?`,
        answer: `This depends on the specific dispensary and ${state.name}'s laws. Some dispensaries are recreational (21+ with ID), others are medical-only. Check each dispensary's details for requirements.`
      },
      {
        question: `Can I order cannabis online for delivery in ${state.name}?`,
        answer: `Many ${state.name} dispensaries offer online ordering and delivery. Look for the 'Delivery' or 'Online Ordering' tags on dispensary listings.`
      },
      {
        question: `What forms of payment do ${state.name} dispensaries accept?`,
        answer: `Most dispensaries in ${state.name} prefer cash, though some accept debit cards. Credit cards are rare due to federal banking restrictions. Many locations have ATMs on-site.`
      },
      {
        question: `Are ${state.name} dispensary prices competitive?`,
        answer: `Prices vary by location and product quality. Use our rankings to find highly-rated dispensaries with good reviews, which often indicate fair pricing and quality products.`
      },
      {
        question: `How often are ${state.name} dispensary rankings updated?`,
        answer: `Rankings are recalculated regularly based on new votes, reviews, and engagement. The community determines which dispensaries rise to the top.`
      }
    ];

    const faqSchema = SchemaGenerator.generateFAQSchema(faqs);

    res.render('faq/state', {
      title: `${state.name} Cannabis Dispensary FAQ - Common Questions`,
      state,
      faqs,
      faqSchema,
      baseUrl: process.env.BASE_URL || 'https://bestdispensaries.munchmakers.com',
      meta: {
        description: `Answers to common questions about cannabis dispensaries in ${state.name}. Learn about legality, requirements, best dispensaries, and more.`,
        keywords: `${state.name} dispensary faq, cannabis ${state.name}, marijuana questions ${state.name}`
      }
    });
  } catch (error) {
    console.error('Error loading state FAQ:', error);
    res.status(500).send('Error loading FAQ');
  }
});

module.exports = router;
