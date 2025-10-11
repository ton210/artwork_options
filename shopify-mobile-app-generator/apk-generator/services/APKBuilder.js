const fs = require('fs-extra');
const path = require('path');
const { execSync, spawn } = require('child_process');
const { v4: uuidv4 } = require('uuid');

class APKBuilder {
  constructor(buildId, config) {
    this.buildId = buildId;
    this.config = config;
    this.buildDir = path.join(__dirname, '../../builds', buildId);
    this.templateDir = path.join(__dirname, '../../mobile-app-template');
    this.appDir = path.join(this.buildDir, 'app');
  }

  async cloneTemplate() {
    console.log(`Cloning template for build ${this.buildId}`);
    
    // Create build directory
    await fs.ensureDir(this.buildDir);
    
    // Copy template to build directory
    await fs.copy(this.templateDir, this.appDir);
    
    console.log(`Template cloned to ${this.appDir}`);
  }

  async configureApp() {
    console.log(`Configuring app for build ${this.buildId}`);
    
    // Replace configuration file
    await this.replaceStoreConfig();
    
    // Update app name and package name
    await this.updateAppIdentity();
    
    // Replace assets (logo, splash screen, etc.)
    await this.replaceAssets();
    
    // Install dependencies
    await this.installDependencies();
    
    console.log(`App configuration completed for build ${this.buildId}`);
  }

  async replaceStoreConfig() {
    const configPath = path.join(this.appDir, 'src/config/storeConfig.js');
    let configTemplate = await fs.readFile(configPath, 'utf8');
    
    // Replace all template variables
    const replacements = {
      '{{STORE_DOMAIN}}': this.config.storeDomain,
      '{{STOREFRONT_TOKEN}}': this.config.storefrontAccessToken,
      '{{APP_NAME}}': this.config.appName,
      '{{PRIMARY_COLOR}}': this.config.primaryColor || '#007AFF',
      '{{SECONDARY_COLOR}}': this.config.secondaryColor || '#5856D6',
      '{{ACCENT_COLOR}}': this.config.accentColor || '#FF9500',
      '{{TEXT_COLOR}}': this.config.textColor || '#333333',
      '{{BACKGROUND_COLOR}}': this.config.backgroundColor || '#FFFFFF',
      '{{LOGO_BASE64}}': this.config.logoBase64 || '',
      '{{SPLASH_SCREEN_BASE64}}': this.config.splashScreenBase64 || '',
      '{{FAVICON_BASE64}}': this.config.faviconBase64 || '',
      '{{ENABLE_REVIEWS}}': this.config.features.reviews || false,
      '{{ENABLE_WISHLIST}}': this.config.features.wishlist || false,
      '{{ENABLE_PUSH_NOTIFICATIONS}}': this.config.features.pushNotifications || false,
      '{{ENABLE_SOCIAL_LOGIN}}': this.config.features.socialLogin || false,
      '{{ENABLE_GUEST_CHECKOUT}}': this.config.features.guestCheckout || true,
      '{{TEMPLATE_ID}}': this.config.template || 'modern',
      '{{HOME_BLOCKS}}': JSON.stringify(this.config.layout.homeBlocks || []),
      '{{CATEGORY_LAYOUT}}': this.config.layout.categoryLayout || 'grid',
      '{{PRODUCT_LAYOUT}}': this.config.layout.productLayout || 'standard',
      '{{FACEBOOK_URL}}': this.config.social.facebook || '',
      '{{INSTAGRAM_URL}}': this.config.social.instagram || '',
      '{{TWITTER_URL}}': this.config.social.twitter || '',
      '{{TIKTOK_URL}}': this.config.social.tiktok || '',
      '{{WEBHOOK_URL}}': this.config.webhookUrl || ''
    };

    for (const [placeholder, value] of Object.entries(replacements)) {
      configTemplate = configTemplate.replace(new RegExp(placeholder, 'g'), value);
    }

    await fs.writeFile(configPath, configTemplate);
  }

  async updateAppIdentity() {
    const packageName = `com.shopifymobile.${this.config.storeDomain.replace(/[^a-zA-Z0-9]/g, '').toLowerCase()}`;
    
    // Update Android package name and app name
    await this.updateAndroidConfig(packageName);
    
    // Update package.json
    const packageJsonPath = path.join(this.appDir, 'package.json');
    const packageJson = await fs.readJson(packageJsonPath);
    packageJson.name = this.config.appName.replace(/[^a-zA-Z0-9]/g, '').toLowerCase();
    await fs.writeJson(packageJsonPath, packageJson, { spaces: 2 });
  }

  async updateAndroidConfig(packageName) {
    // Update MainActivity.java package name
    const mainActivityPath = path.join(
      this.appDir, 
      'android/app/src/main/java/com/shopifymobileapptemplate/MainActivity.java'
    );
    
    if (await fs.pathExists(mainActivityPath)) {
      let mainActivity = await fs.readFile(mainActivityPath, 'utf8');
      mainActivity = mainActivity.replace(
        /package com\.shopifymobileapptemplate;/g,
        `package ${packageName};`
      );
      await fs.writeFile(mainActivityPath, mainActivity);
    }

    // Update MainApplication.java package name
    const mainApplicationPath = path.join(
      this.appDir,
      'android/app/src/main/java/com/shopifymobileapptemplate/MainApplication.java'
    );
    
    if (await fs.pathExists(mainApplicationPath)) {
      let mainApplication = await fs.readFile(mainApplicationPath, 'utf8');
      mainApplication = mainApplication.replace(
        /package com\.shopifymobileapptemplate;/g,
        `package ${packageName};`
      );
      await fs.writeFile(mainApplicationPath, mainApplication);
    }

    // Update AndroidManifest.xml
    const manifestPath = path.join(this.appDir, 'android/app/src/main/AndroidManifest.xml');
    if (await fs.pathExists(manifestPath)) {
      let manifest = await fs.readFile(manifestPath, 'utf8');
      manifest = manifest.replace(
        /package="com\.shopifymobileapptemplate"/g,
        `package="${packageName}"`
      );
      manifest = manifest.replace(
        /android:label="ShopifyMobileAppTemplate"/g,
        `android:label="${this.config.appName}"`
      );
      await fs.writeFile(manifestPath, manifest);
    }

    // Update build.gradle
    const buildGradlePath = path.join(this.appDir, 'android/app/build.gradle');
    if (await fs.pathExists(buildGradlePath)) {
      let buildGradle = await fs.readFile(buildGradlePath, 'utf8');
      buildGradle = buildGradle.replace(
        /applicationId "com\.shopifymobileapptemplate"/g,
        `applicationId "${packageName}"`
      );
      await fs.writeFile(buildGradlePath, buildGradle);
    }

    // Move Java files to correct package structure
    const newPackagePath = packageName.split('.').join('/');
    const newJavaDir = path.join(this.appDir, 'android/app/src/main/java', newPackagePath);
    const oldJavaDir = path.join(this.appDir, 'android/app/src/main/java/com/shopifymobileapptemplate');
    
    if (await fs.pathExists(oldJavaDir)) {
      await fs.ensureDir(newJavaDir);
      await fs.copy(oldJavaDir, newJavaDir);
      await fs.remove(path.join(this.appDir, 'android/app/src/main/java/com'));
    }
  }

  async replaceAssets() {
    // Replace app icon
    if (this.config.logoBase64) {
      await this.replaceIcon(this.config.logoBase64);
    }

    // Replace splash screen
    if (this.config.splashScreenBase64) {
      await this.replaceSplashScreen(this.config.splashScreenBase64);
    }
  }

  async replaceIcon(logoBase64) {
    const sharp = require('sharp');
    const logoBuffer = Buffer.from(logoBase64, 'base64');
    
    // Android icon sizes
    const androidSizes = [
      { size: 36, path: 'android/app/src/main/res/mipmap-ldpi/ic_launcher.png' },
      { size: 48, path: 'android/app/src/main/res/mipmap-mdpi/ic_launcher.png' },
      { size: 72, path: 'android/app/src/main/res/mipmap-hdpi/ic_launcher.png' },
      { size: 96, path: 'android/app/src/main/res/mipmap-xhdpi/ic_launcher.png' },
      { size: 144, path: 'android/app/src/main/res/mipmap-xxhdpi/ic_launcher.png' },
      { size: 192, path: 'android/app/src/main/res/mipmap-xxxhdpi/ic_launcher.png' },
    ];

    for (const { size, path: iconPath } of androidSizes) {
      const fullPath = path.join(this.appDir, iconPath);
      await fs.ensureDir(path.dirname(fullPath));
      
      await sharp(logoBuffer)
        .resize(size, size)
        .png()
        .toFile(fullPath);
    }
  }

  async replaceSplashScreen(splashScreenBase64) {
    const sharp = require('sharp');
    const splashBuffer = Buffer.from(splashScreenBase64, 'base64');
    
    // Create splash screen for different densities
    const splashSizes = [
      { width: 320, height: 480, path: 'android/app/src/main/res/drawable-mdpi/launch_screen.png' },
      { width: 480, height: 800, path: 'android/app/src/main/res/drawable-hdpi/launch_screen.png' },
      { width: 720, height: 1280, path: 'android/app/src/main/res/drawable-xhdpi/launch_screen.png' },
      { width: 1080, height: 1920, path: 'android/app/src/main/res/drawable-xxhdpi/launch_screen.png' },
    ];

    for (const { width, height, path: splashPath } of splashSizes) {
      const fullPath = path.join(this.appDir, splashPath);
      await fs.ensureDir(path.dirname(fullPath));
      
      await sharp(splashBuffer)
        .resize(width, height, { fit: 'contain', background: { r: 255, g: 255, b: 255, alpha: 1 } })
        .png()
        .toFile(fullPath);
    }
  }

  async installDependencies() {
    console.log(`Installing dependencies for build ${this.buildId}`);
    
    return new Promise((resolve, reject) => {
      const npmInstall = spawn('npm', ['install'], {
        cwd: this.appDir,
        stdio: 'pipe'
      });

      let output = '';
      npmInstall.stdout.on('data', (data) => {
        output += data.toString();
      });

      npmInstall.stderr.on('data', (data) => {
        output += data.toString();
      });

      npmInstall.on('close', (code) => {
        if (code === 0) {
          console.log(`Dependencies installed successfully for build ${this.buildId}`);
          resolve();
        } else {
          console.error(`npm install failed for build ${this.buildId}:`, output);
          reject(new Error(`npm install failed with code ${code}`));
        }
      });
    });
  }

  async buildAPK() {
    console.log(`Building APK for build ${this.buildId}`);
    
    return new Promise((resolve, reject) => {
      // Generate Android bundle first
      const bundleCommand = spawn('npx', ['react-native', 'bundle', 
        '--platform', 'android',
        '--dev', 'false',
        '--entry-file', 'index.js',
        '--bundle-output', 'android/app/src/main/assets/index.android.bundle',
        '--assets-dest', 'android/app/src/main/res/'
      ], {
        cwd: this.appDir,
        stdio: 'pipe'
      });

      bundleCommand.on('close', (bundleCode) => {
        if (bundleCode !== 0) {
          return reject(new Error('React Native bundle generation failed'));
        }

        // Build APK using Gradle
        const gradlewPath = path.join(this.appDir, 'android/gradlew');
        const buildCommand = spawn(gradlewPath, ['assembleRelease'], {
          cwd: path.join(this.appDir, 'android'),
          stdio: 'pipe',
          env: {
            ...process.env,
            ANDROID_HOME: process.env.ANDROID_HOME || '/opt/android-sdk'
          }
        });

        let output = '';
        buildCommand.stdout.on('data', (data) => {
          output += data.toString();
        });

        buildCommand.stderr.on('data', (data) => {
          output += data.toString();
        });

        buildCommand.on('close', (code) => {
          if (code === 0) {
            const apkPath = path.join(
              this.appDir,
              'android/app/build/outputs/apk/release/app-release.apk'
            );
            
            if (fs.existsSync(apkPath)) {
              // Copy APK to downloads folder
              const downloadsDir = path.join(__dirname, '../../downloads');
              fs.ensureDirSync(downloadsDir);
              
              const finalApkPath = path.join(downloadsDir, `${this.buildId}.apk`);
              fs.copySync(apkPath, finalApkPath);
              
              console.log(`APK built successfully for build ${this.buildId}: ${finalApkPath}`);
              resolve(finalApkPath);
            } else {
              reject(new Error('APK file not found after build'));
            }
          } else {
            console.error(`Gradle build failed for build ${this.buildId}:`, output);
            reject(new Error(`Gradle build failed with code ${code}`));
          }
        });
      });
    });
  }

  async cleanup() {
    try {
      if (await fs.pathExists(this.buildDir)) {
        await fs.remove(this.buildDir);
        console.log(`Cleaned up build directory for ${this.buildId}`);
      }
    } catch (error) {
      console.error(`Error cleaning up build ${this.buildId}:`, error);
    }
  }
}

module.exports = APKBuilder;