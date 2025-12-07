/**
 * Generate images and save locally for testing
 */

require('dotenv').config();
const fs = require('fs').promises;
const path = require('path');
const { generateImage } = require('./services/imagen');

async function generateAndSaveImages() {
  console.log('Generating test images...\n');

  // Read output file
  const outputData = JSON.parse(
    await fs.readFile('./data/blog-output-1763801743113.json', 'utf8')
  );

  const slug = outputData.seoMetadata.slug;

  // Generate 3 images
  console.log('[1/3] Generating featured image...');
  const featured = await generateImage(outputData.images.featured.prompt, '1:1');
  await fs.writeFile(`./temp/${slug}-featured.jpg`, featured);
  console.log(`✓ Saved: ./temp/${slug}-featured.jpg`);

  console.log('\n[2/3] Generating inline-1 image...');
  const inline1 = await generateImage(outputData.images.inline1.prompt, '16:9');
  await fs.writeFile(`./temp/${slug}-inline-1.jpg`, inline1);
  console.log(`✓ Saved: ./temp/${slug}-inline-1.jpg`);

  console.log('\n[3/3] Generating inline-2 image...');
  const inline2 = await generateImage(outputData.images.inline2.prompt, '16:9');
  await fs.writeFile(`./temp/${slug}-inline-2.jpg`, inline2);
  console.log(`✓ Saved: ./temp/${slug}-inline-2.jpg`);

  console.log('\n✓ All images saved to ./temp/');
}

generateAndSaveImages().catch(console.error);
