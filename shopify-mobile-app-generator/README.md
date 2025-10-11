# Shopify Mobile App Generator

Transform any Shopify store into a native Android app with this comprehensive system. Merchants can choose templates, customize with drag-and-drop blocks, and generate APK files ready for Google Play Store.

## System Overview

This system consists of three main components:

1. **Mobile App Template** - React Native template that gets customized per merchant
2. **APK Generator Service** - Docker-based service that builds Android APKs  
3. **Shopify App Backend** - Main application with merchant dashboard

## Architecture

```
┌─────────────────────┐    ┌─────────────────────┐    ┌─────────────────────┐
│   Shopify App       │    │  APK Generator      │    │  Mobile App         │
│   Backend           │    │  Service            │    │  Template           │
│                     │    │                     │    │                     │
│  - Merchant Dashboard │───→│  - Docker Container │───→│  - React Native App │
│  - Template System   │    │  - Android SDK      │    │  - Shopify API      │
│  - Block Editor      │    │  - Build Pipeline   │    │  - Configurable UI  │
│  - Configuration     │    │  - APK Generation   │    │  - Dynamic Content  │
└─────────────────────┘    └─────────────────────┘    └─────────────────────┘
```

## Features

### For Merchants
- **Template Selection**: Choose from 4 professionally designed templates (Minimal, Modern, Classic, Bold)
- **Drag-and-Drop Builder**: Customize app layout with blocks (hero sections, product showcases, banners, etc.)
- **Brand Customization**: Upload logos, set colors, configure features
- **Live Preview**: See app changes in real-time
- **One-Click APK Generation**: Generate Android app file ready for Google Play
- **Detailed Instructions**: Step-by-step guide for app store submission

### For Developers
- **Modular Architecture**: Easily extend with new templates and block types
- **Docker Deployment**: Containerized APK generation for scalability
- **REST API**: Complete API for all operations
- **React Dashboard**: Modern admin interface using Shopify Polaris
- **MongoDB Storage**: Flexible data storage for configurations
- **Webhook Support**: Real-time sync with Shopify store changes

## Quick Start

### Prerequisites
- Node.js 18+
- Docker and Docker Compose
- MongoDB
- Android SDK (for APK generation)
- Shopify Partner Account

### 1. Clone and Setup

```bash
git clone <repository>
cd shopify-mobile-app-generator

# Setup Shopify App Backend
cd shopify-app-backend
cp .env.example .env
# Edit .env with your Shopify app credentials
npm install

# Setup React Dashboard
cd client
npm install
npm run build

# Setup APK Generator
cd ../apk-generator
npm install

# Setup Mobile App Template
cd ../mobile-app-template
npm install
```

### 2. Configure Environment

**Shopify App Backend (.env)**:
```env
SHOPIFY_API_KEY=your_shopify_api_key
SHOPIFY_API_SECRET=your_shopify_api_secret
SHOPIFY_APP_URL=https://your-app-domain.com
SHOPIFY_SCOPES=read_products,read_orders,read_customers
MONGODB_URI=mongodb://localhost:27017/shopify-mobile-app-builder
APK_GENERATOR_URL=http://localhost:3001
JWT_SECRET=your_jwt_secret_key
```

### 3. Deploy with Docker Compose

```bash
# Create docker-compose.yml
version: '3.8'
services:
  mongodb:
    image: mongo:7
    ports:
      - "27017:27017"
    volumes:
      - mongodb_data:/data/db

  shopify-app:
    build: ./shopify-app-backend
    ports:
      - "3000:3000"
    depends_on:
      - mongodb
    environment:
      - MONGODB_URI=mongodb://mongodb:27017/shopify-mobile-app-builder

  apk-generator:
    build: ./apk-generator
    ports:
      - "3001:3001"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - apk_builds:/app/builds

volumes:
  mongodb_data:
  apk_builds:

# Start services
docker-compose up -d
```

### 4. Create Shopify App

1. Go to [Shopify Partner Dashboard](https://partners.shopify.com/)
2. Create new app with these settings:
   - App URL: `https://your-domain.com`
   - Allowed redirection URLs: `https://your-domain.com/auth/callback`
   - Webhook endpoints: `https://your-domain.com/api/webhooks`
3. Update .env with API credentials

## Templates

### Available Templates

1. **Minimal** - Clean, simple design focusing on products
2. **Modern** - Contemporary with bold colors and modern typography  
3. **Classic** - Traditional ecommerce layout with proven patterns
4. **Bold** - Eye-catching design with vibrant colors

### Block Types

- **Hero Section**: Large banner with call-to-action
- **Featured Products**: Product showcase in various layouts
- **Collections**: Category/collection display
- **Banner**: Promotional announcements
- **Text Block**: Rich text content
- **Image**: Image display with captions
- **Video**: Video embeds
- **Testimonials**: Customer reviews

## APK Generation Process

1. **Configuration Processing**: Merchant settings and assets processed
2. **Template Cloning**: React Native template copied to build directory
3. **Customization**: Configuration injected into template files
4. **Asset Replacement**: Logos, splash screens, icons replaced
5. **Dependency Installation**: npm install in build directory
6. **Bundle Generation**: React Native bundle created
7. **APK Build**: Gradle builds signed APK
8. **File Delivery**: APK made available for download

## API Documentation

### App Configuration
```bash
# Get current configuration
GET /api/app/config

# Update configuration (multipart form)
PUT /api/app/config
Content-Type: multipart/form-data
{
  "config": "{json configuration}",
  "logo": file,
  "splashScreen": file,
  "favicon": file
}

# Generate Shopify Storefront Token
POST /api/app/generate-storefront-token

# Build APK
POST /api/app/build-apk

# Check build status
GET /api/app/build-status/:buildId

# Download APK
GET /api/app/download-apk/:buildId
```

### Templates
```bash
# Get all templates
GET /api/templates

# Get specific template
GET /api/templates/:templateId

# Get block types
GET /api/templates/blocks/types

# Validate block configuration
POST /api/templates/blocks/validate
```

## Deployment

### Production Deployment

1. **Server Setup**: Ubuntu 20.04+ with Docker
2. **Domain Configuration**: Point domain to server
3. **SSL Certificate**: Setup Let's Encrypt SSL
4. **Environment Variables**: Configure production .env
5. **Docker Deployment**: Use docker-compose for production
6. **Monitoring**: Setup logging and monitoring

### Shopify App Store Submission

1. Complete app development and testing
2. Create app listing with screenshots
3. Submit for Shopify review
4. Handle review feedback
5. Publish to Shopify App Store

## Merchant Instructions

### For Merchants Using the App

1. **Install App**: Install from Shopify App Store
2. **Choose Template**: Select template matching your brand
3. **Customize Design**: Use drag-and-drop editor to arrange content
4. **Upload Assets**: Add your logo, colors, and branding
5. **Preview App**: Test app functionality and appearance
6. **Generate APK**: Click "Build APK" and wait for generation
7. **Download File**: Download the generated APK file
8. **Create Developer Account**: Sign up for Google Play Console
9. **Upload APK**: Follow Google's app submission process
10. **Publish App**: Submit for review and publish

### Google Play Store Submission Guide

1. **Google Play Console Account** ($25 one-time fee)
2. **App Information**: Title, description, screenshots
3. **APK Upload**: Upload generated APK file
4. **Store Listing**: Add app icon, feature graphics
5. **Content Rating**: Complete content questionnaire  
6. **Pricing**: Set free or paid pricing
7. **Review Process**: Submit for Google review (2-3 days)
8. **Publication**: App goes live after approval

## Customization

### Adding New Templates

1. Create template definition in `routes/templates.js`
2. Define default blocks and settings
3. Add template preview image
4. Update template selector UI

### Adding New Block Types

1. Define block schema in `routes/templates.js`
2. Create React Native component in mobile template
3. Add block to template renderer
4. Create admin interface for block settings

### Extending Features

- Add new Shopify API integrations
- Create custom mobile app features
- Integrate with external services
- Add analytics and tracking

## Support

- **Documentation**: Full API and setup documentation
- **Community**: GitHub Discussions for questions
- **Issues**: GitHub Issues for bug reports
- **Professional Support**: Available for enterprise clients

## License

MIT License - see LICENSE file for details.

---

Built with ❤️ for Shopify merchants who want to go mobile.