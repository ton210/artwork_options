/**
 * BigCommerce Blog API Service
 * Publishes generated blog posts to BigCommerce
 */

const axios = require('axios');

const BC_BLOG_API = `https://api.bigcommerce.com/stores/${process.env.BC_STORE_HASH}/v2/blog/posts`;

const HEADERS = {
  'X-Auth-Token': process.env.BC_ACCESS_TOKEN,
  'Content-Type': 'application/json',
  'Accept': 'application/json'
};

/**
 * Create and publish a blog post to BigCommerce
 * @param {object} blogPost - Complete blog post data
 * @returns {Promise<object>} - Published blog post info
 */
async function publishBlogPost(blogPost) {
  console.log(`\n[BigCommerce] Publishing blog post...`);
  console.log(`  Title: ${blogPost.title}`);

  try {
    const payload = {
      title: blogPost.title,
      author: 'MunchMakers Team',  // Add author name
      body: blogPost.body,
      is_published: true,
      url: `/blog/${blogPost.slug}/`,  // Proper blog URL format with /blog/ prefix
      meta_description: blogPost.metaDescription,
      meta_keywords: blogPost.metaKeywords || ''
    };

    const response = await axios.post(BC_BLOG_API, payload, {
      headers: HEADERS,
      timeout: 30000
    });

    const createdPost = response.data;

    console.log(`  ✓ Published successfully`);
    console.log(`  ID: ${createdPost.id}`);
    console.log(`  URL: https://munchmakers.com${createdPost.url}`);

    return {
      id: createdPost.id,
      url: `https://munchmakers.com${createdPost.url}`,
      title: createdPost.title
    };

  } catch (error) {
    console.error(`  ✗ BigCommerce Error: ${error.message}`);

    if (error.response) {
      console.error('  API Response:', error.response.data);
    }

    throw new Error(`Failed to publish blog post: ${error.message}`);
  }
}

/**
 * Get existing blog posts (for duplicate checking)
 */
async function getExistingBlogPosts() {
  try {
    const response = await axios.get(`${BC_BLOG_API}?limit=250`, {
      headers: HEADERS,
      timeout: 15000
    });

    return response.data;

  } catch (error) {
    console.error(`Error fetching blog posts: ${error.message}`);
    return [];
  }
}

module.exports = {
  publishBlogPost,
  getExistingBlogPosts
};
