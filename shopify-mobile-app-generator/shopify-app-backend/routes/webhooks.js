const express = require('express');
const crypto = require('crypto');
const router = express.Router();
const AppConfiguration = require('../models/AppConfiguration');

// Middleware to verify Shopify webhook
function verifyWebhook(req, res, next) {
  const hmac = req.get('X-Shopify-Hmac-Sha256');
  const body = req.body;
  const hash = crypto
    .createHmac('sha256', process.env.SHOPIFY_WEBHOOK_SECRET)
    .update(JSON.stringify(body))
    .digest('base64');

  if (hash !== hmac) {
    return res.status(401).send('Unauthorized');
  }

  next();
}

// Product update webhook
router.post('/products/update', verifyWebhook, async (req, res) => {
  try {
    const product = req.body;
    const shop = req.get('X-Shopify-Shop-Domain');
    
    console.log(`Product updated in ${shop}:`, product.id);
    
    // Here you could trigger app regeneration if needed
    // Or update cached data
    
    res.status(200).send('OK');
  } catch (error) {
    console.error('Error processing product update webhook:', error);
    res.status(500).send('Error');
  }
});

// Product creation webhook
router.post('/products/create', verifyWebhook, async (req, res) => {
  try {
    const product = req.body;
    const shop = req.get('X-Shopify-Shop-Domain');
    
    console.log(`Product created in ${shop}:`, product.id);
    
    res.status(200).send('OK');
  } catch (error) {
    console.error('Error processing product creation webhook:', error);
    res.status(500).send('Error');
  }
});

// Collection update webhook
router.post('/collections/update', verifyWebhook, async (req, res) => {
  try {
    const collection = req.body;
    const shop = req.get('X-Shopify-Shop-Domain');
    
    console.log(`Collection updated in ${shop}:`, collection.id);
    
    res.status(200).send('OK');
  } catch (error) {
    console.error('Error processing collection update webhook:', error);
    res.status(500).send('Error');
  }
});

// Shop update webhook
router.post('/shop/update', verifyWebhook, async (req, res) => {
  try {
    const shop = req.body;
    const shopDomain = req.get('X-Shopify-Shop-Domain');
    
    console.log(`Shop updated: ${shopDomain}`);
    
    // Update any cached shop information
    await AppConfiguration.findOneAndUpdate(
      { shop: shopDomain },
      { 
        $set: { 
          'shopInfo.name': shop.name,
          'shopInfo.email': shop.email,
          'shopInfo.domain': shop.domain,
          'shopInfo.updatedAt': new Date()
        }
      }
    );
    
    res.status(200).send('OK');
  } catch (error) {
    console.error('Error processing shop update webhook:', error);
    res.status(500).send('Error');
  }
});

// App uninstall webhook
router.post('/app/uninstalled', verifyWebhook, async (req, res) => {
  try {
    const shop = req.get('X-Shopify-Shop-Domain');
    
    console.log(`App uninstalled from ${shop}`);
    
    // Clean up app configuration
    await AppConfiguration.findOneAndDelete({ shop });
    
    res.status(200).send('OK');
  } catch (error) {
    console.error('Error processing app uninstall webhook:', error);
    res.status(500).send('Error');
  }
});

module.exports = router;