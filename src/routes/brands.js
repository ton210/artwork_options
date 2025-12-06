const express = require('express');
const router = express.Router();
const Brand = require('../models/Brand');

// Brands listing page
router.get('/', async (req, res) => {
  try {
    const page = parseInt(req.query.page) || 1;
    const limit = 50;
    const offset = (page - 1) * limit;

    const brands = await Brand.findAll({ limit, offset });
    const franchises = await Brand.getFranchises();

    res.render('brands/index', {
      title: 'Cannabis Dispensary Brands - Top Rated Chains & Franchises',
      brands,
      franchises,
      currentPage: page,
      baseUrl: process.env.BASE_URL || 'http://localhost:3000'
    });
  } catch (error) {
    console.error('Error loading brands page:', error);
    res.status(500).send('Error loading brands');
  }
});

// Individual brand page
router.get('/:slug', async (req, res) => {
  try {
    const brand = await Brand.findBySlug(req.params.slug);

    if (!brand) {
      return res.status(404).render('404', {
        title: 'Brand Not Found',
        baseUrl: process.env.BASE_URL || 'http://localhost:3000'
      });
    }

    const locations = await Brand.getLocations(brand.id);

    // Group locations by state
    const locationsByState = {};
    locations.forEach(location => {
      const state = location.state_name || 'Unknown';
      if (!locationsByState[state]) {
        locationsByState[state] = [];
      }
      locationsByState[state].push(location);
    });

    res.render('brands/show', {
      title: `${brand.name} Dispensary Locations - Ratings & Reviews`,
      brand,
      locations,
      locationsByState,
      baseUrl: process.env.BASE_URL || 'http://localhost:3000'
    });
  } catch (error) {
    console.error('Error loading brand page:', error);
    res.status(500).send('Error loading brand');
  }
});

module.exports = router;
