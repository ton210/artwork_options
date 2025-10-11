const express = require('express');
const { shopifyApp } = require('@shopify/shopify-app-express');
const { MemorySessionStorage } = require('@shopify/shopify-app-session-storage-memory');
const { ApiVersion } = require('@shopify/shopify-api');
const cors = require('cors');
const mongoose = require('mongoose');
const path = require('path');
require('dotenv').config();

// Import routes
const appRoutes = require('./routes/app');
const buildsRoutes = require('./routes/builds');
const templatesRoutes = require('./routes/templates');
const webhooksRoutes = require('./routes/webhooks');

// Import middleware
const authenticateShopify = require('./middleware/authenticateShopify');

const app = express();
const PORT = process.env.PORT || 3000;

// Connect to MongoDB
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/shopify-mobile-app-builder')
  .then(() => console.log('Connected to MongoDB'))
  .catch(err => console.error('MongoDB connection error:', err));

// Shopify app configuration
const shopify = shopifyApp({
  api: {
    apiKey: process.env.SHOPIFY_API_KEY,
    apiSecretKey: process.env.SHOPIFY_API_SECRET,
    scopes: process.env.SHOPIFY_SCOPES.split(','),
    hostName: process.env.SHOPIFY_APP_URL.replace(/https:\\/\\//, ''),
    apiVersion: ApiVersion.October23,
  },
  auth: {
    path: '/auth',
    callbackPath: '/auth/callback',
  },
  webhooks: {
    path: '/webhooks',
  },
  sessionStorage: new MemorySessionStorage(),
});

// Middleware
app.use(cors());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Shopify app middleware
app.use(shopify.config.auth.path, shopify.auth.begin());
app.use(shopify.config.auth.callbackPath, shopify.auth.callback(), (req, res) => {
  res.redirect('/?shop=' + req.query.shop + '&host=' + req.query.host);
});

// Serve static files from React build
app.use(express.static(path.join(__dirname, 'client/build')));

// API Routes
app.use('/api/app', authenticateShopify(shopify), appRoutes);
app.use('/api/builds', authenticateShopify(shopify), buildsRoutes);
app.use('/api/templates', templatesRoutes);
app.use('/api/webhooks', webhooksRoutes);

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Shopify webhook handler
app.use(shopify.processWebhooks({ webhookHandlers: {} }));

// Serve React app for all other routes
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'client/build/index.html'));
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Something went wrong!' });
});

app.listen(PORT, () => {
  console.log(`Shopify Mobile App Builder running on port ${PORT}`);
  console.log(`App URL: ${process.env.SHOPIFY_APP_URL}`);
});