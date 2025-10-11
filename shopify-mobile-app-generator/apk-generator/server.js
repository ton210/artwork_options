const express = require('express');
const cors = require('cors');
const multer = require('multer');
const path = require('path');
const fs = require('fs-extra');
const { v4: uuidv4 } = require('uuid');
const APKBuilder = require('./services/APKBuilder');
const ConfigProcessor = require('./services/ConfigProcessor');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(express.json({ limit: '10mb' }));
app.use('/downloads', express.static('downloads'));

// Configure multer for file uploads
const upload = multer({ 
  dest: 'temp-uploads/',
  limits: { fileSize: 10 * 1024 * 1024 } // 10MB limit
});

// Store active builds
const builds = new Map();

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Generate APK endpoint
app.post('/generate-apk', upload.fields([
  { name: 'logo', maxCount: 1 },
  { name: 'splashScreen', maxCount: 1 },
  { name: 'favicon', maxCount: 1 }
]), async (req, res) => {
  try {
    const buildId = uuidv4();
    const config = JSON.parse(req.body.config);
    
    console.log(`Starting APK build ${buildId} for store: ${config.storeDomain}`);
    
    // Initialize build status
    builds.set(buildId, {
      id: buildId,
      status: 'started',
      progress: 0,
      message: 'Initializing build...',
      startTime: new Date(),
      config: config
    });

    // Return build ID immediately
    res.json({ 
      buildId: buildId,
      status: 'started',
      message: 'APK generation started. Check status with /build-status endpoint.'
    });

    // Start async build process
    buildAPK(buildId, config, req.files).catch(error => {
      console.error(`Build ${buildId} failed:`, error);
      builds.set(buildId, {
        ...builds.get(buildId),
        status: 'failed',
        error: error.message,
        endTime: new Date()
      });
    });

  } catch (error) {
    console.error('Error starting APK generation:', error);
    res.status(500).json({ error: 'Failed to start APK generation' });
  }
});

// Build status endpoint
app.get('/build-status/:buildId', (req, res) => {
  const buildId = req.params.buildId;
  const build = builds.get(buildId);
  
  if (!build) {
    return res.status(404).json({ error: 'Build not found' });
  }
  
  res.json(build);
});

// Download APK endpoint
app.get('/download/:buildId', (req, res) => {
  const buildId = req.params.buildId;
  const build = builds.get(buildId);
  
  if (!build || build.status !== 'completed') {
    return res.status(404).json({ error: 'APK not ready or build not found' });
  }
  
  const apkPath = build.apkPath;
  if (!fs.existsSync(apkPath)) {
    return res.status(404).json({ error: 'APK file not found' });
  }
  
  const filename = `${build.config.appName.replace(/[^a-zA-Z0-9]/g, '_')}.apk`;
  res.download(apkPath, filename);
});

// Get all builds (admin endpoint)
app.get('/builds', (req, res) => {
  const buildsList = Array.from(builds.values()).map(build => ({
    id: build.id,
    status: build.status,
    storeDomain: build.config?.storeDomain,
    appName: build.config?.appName,
    startTime: build.startTime,
    endTime: build.endTime
  }));
  
  res.json(buildsList);
});

// Main APK building function
async function buildAPK(buildId, config, files) {
  const build = builds.get(buildId);
  
  try {
    // Update status: Processing configuration
    updateBuildStatus(buildId, 'processing', 10, 'Processing configuration...');
    
    // Process configuration and assets
    const configProcessor = new ConfigProcessor(config, files);
    const processedConfig = await configProcessor.process();
    
    // Update status: Cloning template
    updateBuildStatus(buildId, 'processing', 30, 'Cloning React Native template...');
    
    // Initialize APK builder
    const builder = new APKBuilder(buildId, processedConfig);
    await builder.cloneTemplate();
    
    // Update status: Configuring app
    updateBuildStatus(buildId, 'processing', 50, 'Configuring app with store data...');
    
    await builder.configureApp();
    
    // Update status: Building APK
    updateBuildStatus(buildId, 'processing', 70, 'Building APK (this may take several minutes)...');
    
    const apkPath = await builder.buildAPK();
    
    // Update status: Completed
    updateBuildStatus(buildId, 'completed', 100, 'APK generated successfully!', apkPath);
    
    // Clean up build directory after delay
    setTimeout(() => {
      builder.cleanup();
    }, 30 * 60 * 1000); // Clean up after 30 minutes
    
  } catch (error) {
    console.error(`Build ${buildId} error:`, error);
    updateBuildStatus(buildId, 'failed', build.progress, `Build failed: ${error.message}`);
    throw error;
  }
}

function updateBuildStatus(buildId, status, progress, message, apkPath = null) {
  const build = builds.get(buildId);
  if (build) {
    builds.set(buildId, {
      ...build,
      status,
      progress,
      message,
      apkPath,
      endTime: status === 'completed' || status === 'failed' ? new Date() : build.endTime
    });
  }
}

// Cleanup old builds periodically
setInterval(() => {
  const cutoffTime = new Date(Date.now() - 24 * 60 * 60 * 1000); // 24 hours ago
  
  for (const [buildId, build] of builds.entries()) {
    if (build.endTime && build.endTime < cutoffTime) {
      // Clean up files
      if (build.apkPath && fs.existsSync(build.apkPath)) {
        fs.removeSync(build.apkPath);
      }
      builds.delete(buildId);
      console.log(`Cleaned up old build: ${buildId}`);
    }
  }
}, 60 * 60 * 1000); // Run every hour

app.listen(PORT, () => {
  console.log(`APK Generation Service running on port ${PORT}`);
  console.log(`Health check: http://localhost:${PORT}/health`);
});