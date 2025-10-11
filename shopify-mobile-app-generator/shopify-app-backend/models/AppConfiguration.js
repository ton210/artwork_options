const mongoose = require('mongoose');

const blockSchema = new mongoose.Schema({
  id: { type: String, required: true },
  type: { 
    type: String, 
    required: true,
    enum: ['hero', 'featured-products', 'collections', 'banner', 'text', 'image', 'video', 'testimonials']
  },
  title: String,
  subtitle: String,
  content: String,
  image: String,
  settings: mongoose.Schema.Types.Mixed,
  order: { type: Number, default: 0 }
});

const appConfigurationSchema = new mongoose.Schema({
  shop: { type: String, required: true, unique: true },
  
  // Basic app settings
  appName: { type: String, required: true },
  description: String,
  
  // Branding
  primaryColor: { type: String, default: '#007AFF' },
  secondaryColor: { type: String, default: '#5856D6' },
  accentColor: { type: String, default: '#FF9500' },
  textColor: { type: String, default: '#333333' },
  backgroundColor: { type: String, default: '#FFFFFF' },
  
  // Assets
  logo: String, // Base64 or URL
  splashScreen: String, // Base64 or URL
  favicon: String, // Base64 or URL
  
  // Features
  features: {
    reviews: { type: Boolean, default: false },
    wishlist: { type: Boolean, default: false },
    pushNotifications: { type: Boolean, default: false },
    socialLogin: { type: Boolean, default: false },
    guestCheckout: { type: Boolean, default: true }
  },
  
  // Template and layout
  template: { 
    type: String, 
    default: 'modern',
    enum: ['minimal', 'modern', 'classic', 'bold']
  },
  
  layout: {
    homeBlocks: [blockSchema],
    categoryLayout: { 
      type: String, 
      default: 'grid',
      enum: ['grid', 'list']
    },
    productLayout: { 
      type: String, 
      default: 'standard',
      enum: ['standard', 'carousel', 'minimal']
    }
  },
  
  // Social media
  social: {
    facebook: String,
    instagram: String,
    twitter: String,
    tiktok: String,
    youtube: String
  },
  
  // Shopify integration
  storefrontAccessToken: String,
  
  // Build information
  lastBuild: {
    id: String,
    status: String,
    createdAt: Date,
    completedAt: Date,
    apkUrl: String,
    error: String
  },
  
  // Analytics
  downloads: { type: Number, default: 0 },
  installs: { type: Number, default: 0 }
}, {
  timestamps: true
});

// Index for efficient queries
appConfigurationSchema.index({ shop: 1 });

module.exports = mongoose.model('AppConfiguration', appConfigurationSchema);