/**
 * Execute Blog Publishing Workflow
 * Generates images, uploads them, and publishes to BigCommerce
 */

require('dotenv').config();
const fs = require('fs').promises;
const path = require('path');
const { generateImage } = require('./services/imagen');
const { uploadBlogImages } = require('./services/webdav');
const { publishBlogPost } = require('./services/bigcommerce');
const { generateAllSchemas, schemasToHtml } = require('./utils/schemaGenerator');

async function executeBlogPublish(outputFilename) {
  console.log('\n' + '='.repeat(70));
  console.log('EXECUTING BLOG PUBLISH WORKFLOW');
  console.log('='.repeat(70));

  try {
    // Read the output file created by Claude Code
    const outputPath = path.join(__dirname, 'data', outputFilename);
    const outputData = JSON.parse(await fs.readFile(outputPath, 'utf8'));

    console.log(`\nBlog Title: ${outputData.seoMetadata.title}`);
    console.log(`Slug: ${outputData.seoMetadata.slug}`);
    console.log(`Internal Links: ${outputData.content.internalLinksCount}`);

    // Step 1: Generate 3 images with Imagen3
    console.log('\n' + '='.repeat(70));
    console.log('STEP 1: GENERATING IMAGES WITH IMAGEN3');
    console.log('='.repeat(70));

    const images = [];

    // Generate featured image
    console.log('\n[1/3] Featured Image (1:1)...');
    const featuredBuffer = await generateImage(
      outputData.images.featured.prompt,
      '1:1'
    );
    images.push({
      type: 'featured',
      buffer: featuredBuffer
    });

    // Generate inline image 1
    console.log('\n[2/3] Inline Image 1 (16:9)...');
    const inline1Buffer = await generateImage(
      outputData.images.inline1.prompt,
      '16:9'
    );
    images.push({
      type: 'inline-1',
      buffer: inline1Buffer
    });

    // Generate inline image 2
    console.log('\n[3/3] Inline Image 2 (16:9)...');
    const inline2Buffer = await generateImage(
      outputData.images.inline2.prompt,
      '16:9'
    );
    images.push({
      type: 'inline-2',
      buffer: inline2Buffer
    });

    console.log('\n✓ All 3 images generated successfully');

    // Step 2: Upload images to BigCommerce
    console.log('\n' + '='.repeat(70));
    console.log('STEP 2: UPLOADING IMAGES TO BIGCOMMERCE');
    console.log('='.repeat(70));

    const imageUrls = await uploadBlogImages(images, outputData.seoMetadata.slug);

    console.log('\nImage URLs:');
    console.log(`  Featured: ${imageUrls.featured}`);
    console.log(`  Inline 1: ${imageUrls['inline-1']}`);
    console.log(`  Inline 2: ${imageUrls['inline-2']}`);

    // Step 3: Assemble final HTML with images and schema markup
    console.log('\n' + '='.repeat(70));
    console.log('STEP 3: ASSEMBLING FINAL HTML + SCHEMA MARKUP');
    console.log('='.repeat(70));

    // Generate schema markup
    const schemas = generateAllSchemas(
      outputData.seoMetadata,
      imageUrls,
      outputData.content.htmlBody
    );
    const schemaHtml = schemasToHtml(schemas);

    console.log(`  ✓ Generated ${schemas.length} schema markups`);

    const finalHtml = assembleFinalHtml(
      outputData.content.htmlBody,
      imageUrls,
      schemaHtml
    );

    // Step 4: Publish to BigCommerce
    console.log('\n' + '='.repeat(70));
    console.log('STEP 4: PUBLISHING TO BIGCOMMERCE');
    console.log('='.repeat(70));

    const blogPost = {
      title: outputData.seoMetadata.title,
      body: finalHtml,
      summary: extractSummary(outputData.content.htmlBody),
      slug: outputData.seoMetadata.slug,
      metaDescription: outputData.seoMetadata.metaDescription,
      metaKeywords: outputData.seoMetadata.metaKeywords,
      tags: extractTags(outputData.content.htmlBody)
    };

    const publishedPost = await publishBlogPost(blogPost);

    // Final success
    console.log('\n' + '='.repeat(70));
    console.log('✅ BLOG POST PUBLISHED SUCCESSFULLY!');
    console.log('='.repeat(70));
    console.log(`\nLive URL: ${publishedPost.url}`);
    console.log(`Post ID: ${publishedPost.id}`);
    console.log(`Word Count: ${outputData.content.wordCount}`);
    console.log(`Internal Links: ${outputData.content.internalLinksCount}`);
    console.log(`Images: 3`);
    console.log('\n' + '='.repeat(70));

    return publishedPost;

  } catch (error) {
    console.error('\n' + '='.repeat(70));
    console.error('❌ ERROR IN WORKFLOW');
    console.error('='.repeat(70));
    console.error(error);
    throw error;
  }
}

/**
 * Assemble final HTML with embedded images and schema markup
 */
function assembleFinalHtml(htmlBody, imageUrls, schemaHtml) {
  // Start with schema markup at the top
  let finalHtml = schemaHtml + '\n\n';

  // Insert featured image
  finalHtml += `<img src="${imageUrls.featured}" alt="Cannabis measurement guide for dispensaries" style="width: 100%; max-width: 800px; margin-bottom: 30px;" />\n\n`;

  // Split content into sections
  const sections = htmlBody.split('<h2>');

  // Add first section (intro + H1)
  finalHtml += sections[0];

  // Add second section with inline image 1 after it
  if (sections.length > 1) {
    finalHtml += '<h2>' + sections[1];
    finalHtml += `\n\n<img src="${imageUrls['inline-1']}" alt="Dispensary cannabis storage solutions" style="width: 100%; max-width: 800px; margin: 30px 0;" />\n\n`;
  }

  // Add remaining sections
  for (let i = 2; i < sections.length; i++) {
    finalHtml += '<h2>' + sections[i];

    // Add inline image 2 after 4th or 5th section
    if (i === 4 && imageUrls['inline-2']) {
      finalHtml += `\n\n<img src="${imageUrls['inline-2']}" alt="Custom branded cannabis accessories" style="width: 100%; max-width: 800px; margin: 30px 0;" />\n\n`;
    }
  }

  return finalHtml;
}

/**
 * Extract first 150-200 characters as summary
 */
function extractSummary(htmlBody) {
  const text = htmlBody.replace(/<[^>]*>/g, '');  // Strip HTML
  return text.substring(0, 200).trim() + '...';
}

/**
 * Extract relevant tags from content
 */
function extractTags(htmlBody) {
  // Simple tag extraction (can be enhanced)
  const tags = ['cannabis', 'dispensary', 'wholesale', 'accessories'];
  return tags;
}

// Run if called directly
if (require.main === module) {
  const outputFile = process.argv[2];

  if (!outputFile) {
    console.error('Usage: node execute-blog-publish.js <output-filename>');
    process.exit(1);
  }

  executeBlogPublish(outputFile)
    .then(() => process.exit(0))
    .catch((error) => {
      console.error(error);
      process.exit(1);
    });
}

module.exports = { executeBlogPublish };
