/**
 * WebDAV Service for BigCommerce Image Uploads
 * Uses curl with digest auth for reliable uploads
 */

const fs = require('fs').promises;
const path = require('path');
const { exec } = require('child_process');
const util = require('util');
const execPromise = util.promisify(exec);

/**
 * Upload image to BigCommerce via WebDAV using curl
 * @param {Buffer} imageBuffer - Image data
 * @param {string} filename - Filename (e.g., 'blog-slug-featured.jpg')
 * @returns {Promise<string>} - Public URL of uploaded image
 */
async function uploadImage(imageBuffer, filename) {
  console.log(`\n  [WebDAV] Uploading ${filename}...`);

  const webdavUrl = process.env.WEBDAV_URL;
  const username = process.env.WEBDAV_USERNAME;
  const password = process.env.WEBDAV_PASSWORD;
  const uploadPath = process.env.WEBDAV_UPLOAD_PATH;

  try {
    // Save image to temp file
    const tempDir = path.join(__dirname, '..', 'temp');
    await fs.mkdir(tempDir, { recursive: true });
    const tempFile = path.join(tempDir, filename);
    await fs.writeFile(tempFile, imageBuffer);

    // Upload using curl with digest auth
    const remotePath = `${webdavUrl}${uploadPath}${filename}`;
    const curlCommand = `curl --digest --user "${username}:${password}" -T "${tempFile}" "${remotePath}"`;

    console.log(`    Uploading to: ${remotePath}`);

    const { stdout, stderr } = await execPromise(curlCommand);

    // Clean up temp file
    await fs.unlink(tempFile);

    // Construct public URL (use blog_images path)
    const publicUrl = `https://cdn11.bigcommerce.com/s-${process.env.BC_STORE_HASH}/content/blog_images/${filename}`;

    console.log(`  ✓ Uploaded successfully`);
    console.log(`  URL: ${publicUrl}`);

    return publicUrl;

  } catch (error) {
    console.error(`  ✗ WebDAV Upload Error: ${error.message}`);
    throw new Error(`Failed to upload ${filename}: ${error.message}`);
  }
}

/**
 * Upload all blog images
 * @param {Array} images - Array of {type, buffer} objects
 * @param {string} slug - Blog post slug for naming
 * @returns {Promise<object>} - Object with image URLs
 */
async function uploadBlogImages(images, slug) {
  console.log(`\n[WebDAV] Uploading ${images.length} images...`);

  const urls = {};

  for (const image of images) {
    const filename = `blog-${slug}-${image.type}.jpg`;
    const url = await uploadImage(image.buffer, filename);
    urls[image.type] = url;
  }

  console.log(`  ✓ All images uploaded`);

  return urls;
}

module.exports = {
  uploadImage,
  uploadBlogImages
};
