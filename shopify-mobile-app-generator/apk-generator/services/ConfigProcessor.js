const fs = require('fs-extra');
const path = require('path');

class ConfigProcessor {
  constructor(config, files) {
    this.config = config;
    this.files = files || {};
  }

  async process() {
    console.log('Processing configuration and assets...');
    
    // Process uploaded files and convert to base64
    const processedConfig = {
      ...this.config,
      logoBase64: await this.processFile('logo'),
      splashScreenBase64: await this.processFile('splashScreen'),
      faviconBase64: await this.processFile('favicon'),
    };

    // Validate required fields
    this.validateConfig(processedConfig);
    
    // Generate Shopify Storefront Access Token (placeholder - you'll need to implement this)
    processedConfig.storefrontAccessToken = await this.generateStorefrontToken(processedConfig);
    
    return processedConfig;
  }

  async processFile(fileType) {
    if (!this.files[fileType] || !this.files[fileType][0]) {
      return null;
    }

    const file = this.files[fileType][0];
    const filePath = file.path;
    
    try {
      const fileBuffer = await fs.readFile(filePath);
      const base64 = fileBuffer.toString('base64');
      
      // Clean up temp file
      await fs.remove(filePath);
      
      return base64;
    } catch (error) {
      console.error(`Error processing ${fileType}:`, error);
      return null;
    }
  }

  validateConfig(config) {
    const requiredFields = [
      'storeDomain',
      'appName',
      'primaryColor'
    ];

    for (const field of requiredFields) {
      if (!config[field]) {
        throw new Error(`Missing required field: ${field}`);
      }
    }

    // Validate store domain format
    if (!config.storeDomain.includes('.myshopify.com')) {
      throw new Error('Invalid store domain format. Expected: store-name.myshopify.com');
    }

    // Validate colors
    const colorFields = ['primaryColor', 'secondaryColor', 'accentColor', 'textColor', 'backgroundColor'];
    for (const colorField of colorFields) {
      if (config[colorField] && !this.isValidColor(config[colorField])) {
        throw new Error(`Invalid color format for ${colorField}. Expected hex color (e.g., #FF0000)`);
      }
    }

    console.log('Configuration validation passed');
  }

  isValidColor(color) {
    return /^#[0-9A-F]{6}$/i.test(color);
  }

  async generateStorefrontToken(config) {
    // In a real implementation, you would:
    // 1. Use Shopify Admin API to create a Storefront Access Token
    // 2. Store the token securely
    // 3. Return the token
    
    // For now, return a placeholder
    // You'll need to implement this based on your Shopify app's Admin API access
    console.log(`Generating Storefront token for ${config.storeDomain}...`);
    
    // Placeholder token - replace with actual Shopify API call
    return 'storefront_access_token_placeholder';
  }
}

module.exports = ConfigProcessor;