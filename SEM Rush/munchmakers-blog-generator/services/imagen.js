/**
 * Google Vertex AI Imagen3 Service
 * Uses gcloud auth for authentication
 */

const axios = require('axios');
const { exec } = require('child_process');
const util = require('util');
const execPromise = util.promisify(exec);

/**
 * Get access token using gcloud auth
 */
async function getGcloudAccessToken() {
  try {
    const { stdout } = await execPromise('gcloud auth print-access-token');
    return stdout.trim();
  } catch (error) {
    throw new Error(`Failed to get gcloud access token: ${error.message}`);
  }
}

/**
 * Generate a single image with Imagen3 Fast Generate
 * @param {string} prompt - Detailed image prompt
 * @param {string} aspectRatio - "1:1" or "16:9"
 * @returns {Promise<Buffer>} - Image as buffer
 */
async function generateImage(prompt, aspectRatio = '1:1') {
  const projectId = 'scrapingjanuary2024';
  const location = 'us-central1';
  const model = 'imagen-4.0-generate-001';  // Using Imagen 4.0

  // Imagen4 endpoint
  const url = `https://us-central1-aiplatform.googleapis.com/v1/projects/${projectId}/locations/${location}/publishers/google/models/${model}:predict`;

  const payload = {
    instances: [{
      prompt: prompt
    }],
    parameters: {
      aspectRatio: aspectRatio,
      sampleCount: 1,
      negativePrompt: 'no copyrighted media',
      enhancePrompt: false,
      personGeneration: 'dont_allow',
      safetySetting: 'block_few',
      addWatermark: false,
      includeRaiReason: true,
      language: 'auto'
    }
  };

  console.log(`\n  [Imagen3] Generating ${aspectRatio} image...`);
  console.log(`  Prompt: ${prompt.substring(0, 80)}...`);

  try {
    // Get access token
    console.log(`  Getting gcloud access token...`);
    const accessToken = await getGcloudAccessToken();

    console.log(`  Making API request to Imagen3 Fast Generate...`);

    const response = await axios.post(url, payload, {
      headers: {
        'Authorization': `Bearer ${accessToken}`,
        'Content-Type': 'application/json'
      },
      timeout: 60000
    });

    const predictions = response.data.predictions;
    if (!predictions || predictions.length === 0) {
      throw new Error('No image returned');
    }

    // Try different response formats
    let imageBase64 = predictions[0].bytesBase64Encoded ||
                      predictions[0].image?.bytesBase64Encoded ||
                      predictions[0].content?.bytesBase64Encoded;

    if (!imageBase64) {
      console.error('  Response structure:', JSON.stringify(predictions[0], null, 2));
      throw new Error('Could not find image data in response');
    }

    const imageBuffer = Buffer.from(imageBase64, 'base64');

    console.log(`  ✓ Generated (${(imageBuffer.length / 1024).toFixed(2)} KB)`);

    return imageBuffer;

  } catch (error) {
    console.error(`  ✗ Imagen3 Error: ${error.message}`);

    if (error.response) {
      console.error('  API Response:', JSON.stringify(error.response.data, null, 2));
    }

    throw error;
  }
}

module.exports = {
  generateImage
};
