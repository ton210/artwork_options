const express = require('express');
const router = express.Router();
const AppConfiguration = require('../models/AppConfiguration');
const ShopifyService = require('../services/ShopifyService');
const APKService = require('../services/APKService');
const multer = require('multer');

// Configure multer for file uploads
const upload = multer({ 
  dest: 'temp-uploads/',
  limits: { fileSize: 10 * 1024 * 1024 }, // 10MB limit
  fileFilter: (req, file, cb) => {
    // Accept images only
    if (file.mimetype.startsWith('image/')) {
      cb(null, true);
    } else {
      cb(new Error('Only image files are allowed'), false);
    }
  }
});

// Get app configuration
router.get('/config', async (req, res) => {
  try {
    const shop = res.locals.shopify.session.shop;
    
    let config = await AppConfiguration.findOne({ shop });
    
    if (!config) {
      // Create default configuration
      config = new AppConfiguration({
        shop,
        appName: shop.replace('.myshopify.com', '').replace(/-/g, ' ').replace(/\\b\\w/g, l => l.toUpperCase()) + ' App',
        layout: {
          homeBlocks: [
            { id: '1', type: 'hero', title: 'Welcome to Our Store', order: 0 },
            { id: '2', type: 'featured-products', title: 'Featured Products', order: 1 },
            { id: '3', type: 'collections', title: 'Shop by Category', order: 2 }
          ]
        }
      });
      
      await config.save();
    }
    
    res.json(config);
  } catch (error) {
    console.error('Error fetching app config:', error);
    res.status(500).json({ error: 'Failed to fetch app configuration' });
  }
});

// Update app configuration
router.put('/config', upload.fields([
  { name: 'logo', maxCount: 1 },
  { name: 'splashScreen', maxCount: 1 },
  { name: 'favicon', maxCount: 1 }
]), async (req, res) => {
  try {
    const shop = res.locals.shopify.session.shop;
    const updateData = JSON.parse(req.body.config || '{}');
    
    // Process uploaded files
    if (req.files) {
      if (req.files.logo) {
        updateData.logo = await processUploadedFile(req.files.logo[0]);
      }
      if (req.files.splashScreen) {
        updateData.splashScreen = await processUploadedFile(req.files.splashScreen[0]);
      }
      if (req.files.favicon) {
        updateData.favicon = await processUploadedFile(req.files.favicon[0]);
      }
    }
    
    const config = await AppConfiguration.findOneAndUpdate(
      { shop },
      updateData,
      { new: true, upsert: true }
    );
    
    res.json(config);
  } catch (error) {
    console.error('Error updating app config:', error);
    res.status(500).json({ error: 'Failed to update app configuration' });
  }
});

// Get store information
router.get('/store-info', async (req, res) => {
  try {
    const session = res.locals.shopify.session;
    const shopifyService = new ShopifyService(session);
    
    const storeInfo = await shopifyService.getStoreInfo();
    res.json(storeInfo);
  } catch (error) {
    console.error('Error fetching store info:', error);
    res.status(500).json({ error: 'Failed to fetch store information' });
  }
});

// Generate Shopify Storefront Access Token
router.post('/generate-storefront-token', async (req, res) => {
  try {
    const session = res.locals.shopify.session;
    const shopifyService = new ShopifyService(session);
    
    const token = await shopifyService.createStorefrontAccessToken();
    
    // Update configuration with new token
    await AppConfiguration.findOneAndUpdate(
      { shop: session.shop },
      { storefrontAccessToken: token },
      { upsert: true }
    );
    
    res.json({ token });
  } catch (error) {
    console.error('Error generating storefront token:', error);
    res.status(500).json({ error: 'Failed to generate storefront access token' });
  }
});

// Build APK
router.post('/build-apk', async (req, res) => {
  try {
    const shop = res.locals.shopify.session.shop;
    const config = await AppConfiguration.findOne({ shop });
    
    if (!config) {
      return res.status(400).json({ error: 'App configuration not found' });
    }
    
    if (!config.storefrontAccessToken) {
      return res.status(400).json({ error: 'Storefront access token not found. Please generate one first.' });
    }
    
    // Start APK build
    const apkService = new APKService();
    const buildResult = await apkService.startBuild(config);
    
    // Update configuration with build info
    config.lastBuild = {
      id: buildResult.buildId,
      status: 'started',
      createdAt: new Date()
    };
    await config.save();
    
    res.json(buildResult);
  } catch (error) {
    console.error('Error starting APK build:', error);
    res.status(500).json({ error: 'Failed to start APK build' });
  }
});

// Get build status
router.get('/build-status/:buildId', async (req, res) => {
  try {
    const buildId = req.params.buildId;
    const apkService = new APKService();
    
    const status = await apkService.getBuildStatus(buildId);
    
    // Update local configuration
    const shop = res.locals.shopify.session.shop;
    const config = await AppConfiguration.findOne({ shop });
    
    if (config && config.lastBuild.id === buildId) {
      config.lastBuild.status = status.status;
      if (status.status === 'completed') {
        config.lastBuild.completedAt = new Date();
        config.lastBuild.apkUrl = status.apkPath;
      } else if (status.status === 'failed') {
        config.lastBuild.error = status.error;
      }
      await config.save();
    }
    
    res.json(status);
  } catch (error) {
    console.error('Error fetching build status:', error);
    res.status(500).json({ error: 'Failed to fetch build status' });
  }
});

// Download APK
router.get('/download-apk/:buildId', async (req, res) => {
  try {
    const buildId = req.params.buildId;
    const apkService = new APKService();
    
    const downloadUrl = await apkService.getDownloadUrl(buildId);
    
    // Increment download counter
    const shop = res.locals.shopify.session.shop;
    await AppConfiguration.findOneAndUpdate(
      { shop },
      { $inc: { downloads: 1 } }
    );
    
    res.json({ downloadUrl });
  } catch (error) {
    console.error('Error getting download URL:', error);
    res.status(500).json({ error: 'Failed to get download URL' });
  }
});

// Helper function to process uploaded files
async function processUploadedFile(file) {
  const fs = require('fs');
  
  try {
    const fileBuffer = fs.readFileSync(file.path);
    const base64 = fileBuffer.toString('base64');
    
    // Clean up temp file
    fs.unlinkSync(file.path);
    
    return base64;
  } catch (error) {
    console.error('Error processing uploaded file:', error);
    return null;
  }
}

module.exports = router;