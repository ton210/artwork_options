const axios = require('axios');

class APKService {
  constructor() {
    this.apkGeneratorUrl = process.env.APK_GENERATOR_URL || 'http://localhost:3001';
  }

  async startBuild(config) {
    try {
      // Prepare configuration for APK generator
      const buildConfig = {
        storeDomain: config.shop,
        storefrontAccessToken: config.storefrontAccessToken,
        appName: config.appName,
        primaryColor: config.primaryColor,
        secondaryColor: config.secondaryColor,
        accentColor: config.accentColor,
        textColor: config.textColor,
        backgroundColor: config.backgroundColor,
        features: config.features,
        template: config.template,
        layout: config.layout,
        social: config.social
      };

      // Prepare form data for multipart upload
      const FormData = require('form-data');
      const formData = new FormData();
      
      formData.append('config', JSON.stringify(buildConfig));
      
      // Add logo if exists
      if (config.logo) {
        const logoBuffer = Buffer.from(config.logo, 'base64');
        formData.append('logo', logoBuffer, { filename: 'logo.png' });
      }
      
      // Add splash screen if exists
      if (config.splashScreen) {
        const splashBuffer = Buffer.from(config.splashScreen, 'base64');
        formData.append('splashScreen', splashBuffer, { filename: 'splash.png' });
      }
      
      // Add favicon if exists
      if (config.favicon) {
        const faviconBuffer = Buffer.from(config.favicon, 'base64');
        formData.append('favicon', faviconBuffer, { filename: 'favicon.png' });
      }

      const response = await axios.post(
        `${this.apkGeneratorUrl}/generate-apk`,
        formData,
        {
          headers: formData.getHeaders(),
          timeout: 30000 // 30 second timeout for initial response
        }
      );

      return response.data;
    } catch (error) {
      console.error('Error starting APK build:', error);
      throw new Error('Failed to start APK build: ' + error.message);
    }
  }

  async getBuildStatus(buildId) {
    try {
      const response = await axios.get(
        `${this.apkGeneratorUrl}/build-status/${buildId}`,
        { timeout: 10000 }
      );

      return response.data;
    } catch (error) {
      console.error('Error fetching build status:', error);
      throw new Error('Failed to fetch build status: ' + error.message);
    }
  }

  async getDownloadUrl(buildId) {
    try {
      // First check if build is completed
      const status = await this.getBuildStatus(buildId);
      
      if (status.status !== 'completed') {
        throw new Error('APK build is not completed yet');
      }

      return `${this.apkGeneratorUrl}/download/${buildId}`;
    } catch (error) {
      console.error('Error getting download URL:', error);
      throw new Error('Failed to get download URL: ' + error.message);
    }
  }

  async getAllBuilds() {
    try {
      const response = await axios.get(
        `${this.apkGeneratorUrl}/builds`,
        { timeout: 10000 }
      );

      return response.data;
    } catch (error) {
      console.error('Error fetching all builds:', error);
      throw new Error('Failed to fetch builds: ' + error.message);
    }
  }

  async checkServiceHealth() {
    try {
      const response = await axios.get(
        `${this.apkGeneratorUrl}/health`,
        { timeout: 5000 }
      );

      return response.data;
    } catch (error) {
      console.error('APK Generator service is not available:', error);
      return { status: 'unavailable', error: error.message };
    }
  }
}

module.exports = APKService;