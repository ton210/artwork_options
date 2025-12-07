/**
 * Update existing blog post with schema markup
 */

require('dotenv').config();
const axios = require('axios');
const fs = require('fs').promises;
const { generateAllSchemas, schemasToHtml } = require('./utils/schemaGenerator');

const BC_BLOG_API = `https://api.bigcommerce.com/stores/${process.env.BC_STORE_HASH}/v2/blog/posts`;
const HEADERS = {
  'X-Auth-Token': process.env.BC_ACCESS_TOKEN,
  'Content-Type': 'application/json',
  'Accept': 'application/json'
};

async function updatePostWithSchema(postId) {
  console.log(`\nUpdating post ${postId} with schema markup...`);

  try {
    // Get current post
    const response = await axios.get(`${BC_BLOG_API}/${postId}`, { headers: HEADERS });
    const post = response.data;

    console.log(`  Current title: ${post.title}`);

    // Load output data for schema generation
    const outputData = JSON.parse(
      await fs.readFile('./data/blog-output-1763801743113.json', 'utf8')
    );

    // Image URLs
    const imageUrls = {
      featured: 'https://cdn11.bigcommerce.com/s-tqjrceegho/content/blog_images/blog-cannabis-measurements-guide-dispensaries-sizes-weights-packaging-featured.jpg',
      'inline-1': 'https://cdn11.bigcommerce.com/s-tqjrceegho/content/blog_images/blog-cannabis-measurements-guide-dispensaries-sizes-weights-packaging-inline-1.jpg',
      'inline-2': 'https://cdn11.bigcommerce.com/s-tqjrceegho/content/blog_images/blog-cannabis-measurements-guide-dispensaries-sizes-weights-packaging-inline-2.jpg'
    };

    // Generate all schemas
    const schemas = generateAllSchemas(
      outputData.seoMetadata,
      imageUrls,
      post.body
    );

    const schemaHtml = schemasToHtml(schemas);

    // Prepend schema to body
    const updatedBody = schemaHtml + '\n\n' + post.body;

    // Update post
    const updateResponse = await axios.put(
      `${BC_BLOG_API}/${postId}`,
      { body: updatedBody },
      { headers: HEADERS }
    );

    console.log(`  ✓ Updated with ${schemas.length} schema markups`);
    console.log(`  - Article schema`);
    console.log(`  - Breadcrumb schema`);
    console.log(`  - Organization schema`);
    if (schemas.length > 3) {
      console.log(`  - FAQ schema`);
    }

    console.log(`\n✓ Post ${postId} now has full structured data!`);
    console.log(`  URL: https://munchmakers.com${post.url}`);

  } catch (error) {
    console.error(`Error: ${error.message}`);
    if (error.response) {
      console.error('API Response:', error.response.data);
    }
  }
}

// Run
updatePostWithSchema(865);
