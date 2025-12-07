/**
 * MunchMakers Constants and Configuration
 * Product data, categories, and templates for content generation
 */

// MunchMakers product categories
const PRODUCT_CATEGORIES = {
  grinders: {
    name: 'Custom Grinders',
    url: '/product-category/custom-grinders/',
    keywords: ['grinder', 'grinders', 'herb grinder', 'weed grinder', 'cannabis grinder'],
    products: ['Custom Big Grinder', 'EasyGrind', '4-Piece Grinder', 'Metal Grinder']
  },
  rollingTrays: {
    name: 'Custom Rolling Trays',
    url: '/product-category/custom-rolling-trays/',
    keywords: ['rolling tray', 'rolling trays', 'weed tray', 'bamboo tray'],
    products: ['Bamboo Rolling Tray', 'LED Rolling Tray', 'Magnetic Lid Rolling Tray']
  },
  ashtrays: {
    name: 'Custom Ashtrays',
    url: '/product-category/custom-ashtrays/',
    keywords: ['ashtray', 'ashtrays', 'weed ashtray', 'custom ashtray'],
    products: ['Ceramic Ashtray', 'Glow in Dark Ashtray', 'Metal Ashtray']
  },
  vapePens: {
    name: 'Custom Vape Pens',
    url: '/product-category/custom-vape-pen/',
    keywords: ['vape pen', 'vape battery', '510 battery', 'custom vape'],
    products: ['Custom Vape Pen Battery', '510 Thread Battery']
  },
  rollingPapers: {
    name: 'Custom Rolling Papers',
    url: '/product-category/custom-rolling-papers/',
    keywords: ['rolling papers', 'custom papers', 'raw papers'],
    products: ['Custom Rolling Papers', '1 1/4 Size Papers', 'King Size Papers']
  },
  stashJars: {
    name: 'Custom Weed Stash Jars',
    url: '/product-category/custom-weed-stash-jars/',
    keywords: ['stash jar', 'weed storage', 'smell proof jar', 'cannabis storage'],
    products: ['Glass Stash Jar', 'Smell Proof Container']
  },
  lighters: {
    name: 'Custom Lighters',
    url: '/product-category/custom-lighters/',
    keywords: ['custom lighter', 'branded lighter', 'bic lighter', 'torch lighter'],
    products: ['Custom BIC Lighter', 'Torch Lighter', 'Compact Lighter']
  }
};

// Common product names for image generation
const MUNCHMAKERS_PRODUCTS = [
  'custom branded grinders',
  'metal herb grinders',
  'bamboo rolling trays',
  'wooden rolling trays',
  'LED rolling trays',
  'ceramic ashtrays',
  'glass ashtrays',
  'custom vape pens',
  '510 thread batteries',
  'smell-proof stash jars',
  'custom rolling papers',
  'branded lighters',
  'joint cases',
  'dab mats',
  'smoking accessories'
];

// Image generation templates
const IMAGE_PROMPTS = {
  featured: (products) =>
    `Professional product photography of ${products}, cannabis accessories, premium quality, clean white background, studio lighting, commercial photography style, high-end branding, modern aesthetic, sharp focus, 8K resolution, no people, no faces, no humans`,

  lifestyle: (products, setting) =>
    `Lifestyle product photography showing ${products} in ${setting}, cannabis industry, professional setting, modern dispensary environment, clean aesthetic, natural lighting, commercial photography, no people visible, no faces, no humans, high quality, detailed, 8K`,

  detail: (product, feature) =>
    `Close-up detail shot of ${feature} on ${product}, premium cannabis accessories, macro photography, professional product photography, textured surfaces, high-end quality, commercial style, no people, no humans, clean composition, 8K detail, studio lighting`
};

// SEO templates
const SEO_TEMPLATES = {
  metaDescriptionTemplate: (topic, benefit) =>
    `Discover ${topic} for dispensaries and smoke shops. ${benefit} Shop wholesale custom cannabis accessories at MunchMakers.`,

  titleSuffixes: [
    'Complete Guide',
    'Ultimate Guide',
    'for Dispensaries',
    'Wholesale Guide',
    'B2B Buyers Guide',
    'Professional Guide'
  ]
};

// Business context
const BUSINESS_CONTEXT = {
  companyName: 'MunchMakers',
  industry: 'B2B Cannabis Accessories Manufacturing',
  targetAudience: [
    'dispensary owners',
    'smoke shop operators',
    'cannabis brand managers',
    'wholesale buyers',
    'B2B procurement teams'
  ],
  tone: {
    style: 'professional and knowledgeable',
    approach: 'educational and authoritative',
    focus: 'solutions-oriented and trust-building'
  },
  usps: [
    'custom branding and logo services',
    'bulk wholesale pricing',
    'low minimum orders',
    'fast turnaround',
    'premium quality'
  ]
};

module.exports = {
  PRODUCT_CATEGORIES,
  MUNCHMAKERS_PRODUCTS,
  IMAGE_PROMPTS,
  SEO_TEMPLATES,
  BUSINESS_CONTEXT
};
