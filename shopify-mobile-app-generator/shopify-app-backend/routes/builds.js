const express = require('express');
const router = express.Router();
const AppConfiguration = require('../models/AppConfiguration');

// Get all builds for the current shop
router.get('/', async (req, res) => {
  try {
    const shop = res.locals.shopify.session.shop;
    
    const config = await AppConfiguration.findOne({ shop });
    
    if (!config) {
      return res.json([]);
    }
    
    // Return build history (you could expand this to store multiple builds)
    const builds = config.lastBuild ? [config.lastBuild] : [];
    
    res.json(builds);
  } catch (error) {
    console.error('Error fetching builds:', error);
    res.status(500).json({ error: 'Failed to fetch builds' });
  }
});

// Get specific build details
router.get('/:buildId', async (req, res) => {
  try {
    const shop = res.locals.shopify.session.shop;
    const buildId = req.params.buildId;
    
    const config = await AppConfiguration.findOne({ shop });
    
    if (!config || !config.lastBuild || config.lastBuild.id !== buildId) {
      return res.status(404).json({ error: 'Build not found' });
    }
    
    res.json(config.lastBuild);
  } catch (error) {
    console.error('Error fetching build details:', error);
    res.status(500).json({ error: 'Failed to fetch build details' });
  }
});

// Delete build
router.delete('/:buildId', async (req, res) => {
  try {
    const shop = res.locals.shopify.session.shop;
    const buildId = req.params.buildId;
    
    const config = await AppConfiguration.findOne({ shop });
    
    if (!config || !config.lastBuild || config.lastBuild.id !== buildId) {
      return res.status(404).json({ error: 'Build not found' });
    }
    
    // Clear the build information
    config.lastBuild = null;
    await config.save();
    
    res.json({ message: 'Build deleted successfully' });
  } catch (error) {
    console.error('Error deleting build:', error);
    res.status(500).json({ error: 'Failed to delete build' });
  }
});

module.exports = router;