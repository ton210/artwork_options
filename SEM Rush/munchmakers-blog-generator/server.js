/**
 * MunchMakers Blog Generator - Express Server
 * Simple server that saves user input for Claude Code to process
 */

require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const fs = require('fs').promises;
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json({ limit: '50mb' }));
app.use(bodyParser.urlencoded({ extended: true, limit: '50mb' }));
app.use(express.static('public'));

// Request logging
app.use((req, res, next) => {
  console.log(`[${new Date().toISOString()}] ${req.method} ${req.path}`);
  next();
});

/**
 * API: Save input data for Claude Code to process
 */
app.post('/api/save-input', async (req, res) => {
  console.log('\n' + '='.repeat(70));
  console.log('NEW BLOG INPUT RECEIVED');
  console.log('='.repeat(70));

  try {
    const { pairs, targetKeyword, internalLinks } = req.body;

    console.log(`  Prompt/Response Pairs: ${pairs.length}`);
    console.log(`  Target Keyword: ${targetKeyword || 'None'}`);
    console.log(`  Internal Link Suggestions: ${internalLinks || 'None'}`);

    // Create input data object
    const inputData = {
      timestamp: new Date().toISOString(),
      pairs: pairs,
      targetKeyword: targetKeyword || '',
      internalLinkSuggestions: internalLinks || '',
      status: 'pending',  // pending, processing, complete
      processedBy: 'claude-code'
    };

    // Save to file for Claude Code to read
    const filename = `blog-input-${Date.now()}.json`;
    const filepath = path.join(__dirname, 'data', filename);

    // Ensure data directory exists
    await fs.mkdir(path.join(__dirname, 'data'), { recursive: true });

    // Write file
    await fs.writeFile(filepath, JSON.stringify(inputData, null, 2));

    console.log(`  ✓ Saved to: ${filename}`);
    console.log('='.repeat(70) + '\n');

    res.json({
      success: true,
      filename: filename,
      filepath: filepath,
      pairCount: pairs.length,
      message: 'Data saved successfully. Ready for Claude Code processing.'
    });

  } catch (error) {
    console.error('Error saving input:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * API: Get pending inputs (for Claude Code to process)
 */
app.get('/api/pending-inputs', async (req, res) => {
  try {
    const dataDir = path.join(__dirname, 'data');
    const files = await fs.readdir(dataDir);

    const pendingFiles = [];

    for (const file of files) {
      if (file.startsWith('blog-input-') && file.endsWith('.json')) {
        const filepath = path.join(dataDir, file);
        const content = await fs.readFile(filepath, 'utf8');
        const data = JSON.parse(content);

        if (data.status === 'pending') {
          pendingFiles.push({
            filename: file,
            timestamp: data.timestamp,
            pairCount: data.pairs.length
          });
        }
      }
    }

    res.json({
      success: true,
      pendingCount: pendingFiles.length,
      files: pendingFiles
    });

  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * API: Mark input as processed
 */
app.post('/api/mark-processed', async (req, res) => {
  try {
    const { filename, blogPostUrl } = req.body;

    const filepath = path.join(__dirname, 'data', filename);
    const content = await fs.readFile(filepath, 'utf8');
    const data = JSON.parse(content);

    data.status = 'complete';
    data.blogPostUrl = blogPostUrl;
    data.completedAt = new Date().toISOString();

    await fs.writeFile(filepath, JSON.stringify(data, null, 2));

    res.json({ success: true });

  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// Start server
app.listen(PORT, () => {
  console.log('\n' + '='.repeat(70));
  console.log('🚀 MUNCHMAKERS BLOG GENERATOR');
  console.log('='.repeat(70));
  console.log(`  Server running at: http://localhost:${PORT}`);
  console.log(`  Data directory: ${path.join(__dirname, 'data')}`);
  console.log('\n  Open http://localhost:${PORT} in your browser');
  console.log('  Then use Claude Code to process the inputs!');
  console.log('='.repeat(70) + '\n');
});
