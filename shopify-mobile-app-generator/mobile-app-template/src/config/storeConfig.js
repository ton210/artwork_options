// This file will be dynamically generated for each merchant
export const STORE_CONFIG = {
  // Shopify store details
  storeDomain: '{{STORE_DOMAIN}}', // e.g., 'merchant-store.myshopify.com'
  storefrontAccessToken: '{{STOREFRONT_TOKEN}}',
  
  // App branding
  appName: '{{APP_NAME}}',
  primaryColor: '{{PRIMARY_COLOR}}',
  secondaryColor: '{{SECONDARY_COLOR}}',
  accentColor: '{{ACCENT_COLOR}}',
  textColor: '{{TEXT_COLOR}}',
  backgroundColor: '{{BACKGROUND_COLOR}}',
  
  // Logo and assets
  logo: '{{LOGO_BASE64}}',
  splashScreen: '{{SPLASH_SCREEN_BASE64}}',
  favicon: '{{FAVICON_BASE64}}',
  
  // App features
  features: {
    reviews: {{ENABLE_REVIEWS}},
    wishlist: {{ENABLE_WISHLIST}},
    pushNotifications: {{ENABLE_PUSH_NOTIFICATIONS}},
    socialLogin: {{ENABLE_SOCIAL_LOGIN}},
    guestCheckout: {{ENABLE_GUEST_CHECKOUT}}
  },
  
  // Template configuration
  template: '{{TEMPLATE_ID}}', // 'minimal', 'modern', 'classic'
  layout: {
    homeBlocks: {{HOME_BLOCKS}}, // Array of block configurations
    categoryLayout: '{{CATEGORY_LAYOUT}}', // 'grid', 'list'
    productLayout: '{{PRODUCT_LAYOUT}}' // 'standard', 'carousel'
  },
  
  // Social media links
  social: {
    facebook: '{{FACEBOOK_URL}}',
    instagram: '{{INSTAGRAM_URL}}',
    twitter: '{{TWITTER_URL}}',
    tiktok: '{{TIKTOK_URL}}'
  }
};

// API endpoints
export const API_CONFIG = {
  storefront: `https://${STORE_CONFIG.storeDomain}/api/2023-10/graphql.json`,
  webhook: '{{WEBHOOK_URL}}'
};