require('dotenv').config();
const https = require('https');
const http = require('http');
const fs = require('fs');
const path = require('path');
const db = require('./src/config/database');

// We'll use sharp for image processing (square + minimize)
let sharp;
try {
  sharp = require('sharp');
} catch (e) {
  console.log('Note: sharp not installed. Run: npm install sharp');
  console.log('Images will be saved as-is without processing.\n');
}

const OUTPUT_DIR = './brand-logos';
const TARGET_SIZE = 400; // Square size in pixels
const JPEG_QUALITY = 80; // Compression quality

// Ensure output directory exists
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

/**
 * Simple HTTP GET function with redirect support
 */
function httpGet(url, maxRedirects = 5) {
  return new Promise((resolve, reject) => {
    if (maxRedirects <= 0) {
      return reject(new Error('Too many redirects'));
    }

    const client = url.startsWith('https') ? https : http;
    const request = client.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.5'
      },
      timeout: 10000
    }, (res) => {
      // Handle redirects
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        let redirectUrl = res.headers.location;
        if (redirectUrl.startsWith('/')) {
          const baseUrl = new URL(url);
          redirectUrl = baseUrl.origin + redirectUrl;
        } else if (!redirectUrl.startsWith('http')) {
          const baseUrl = new URL(url);
          redirectUrl = baseUrl.origin + '/' + redirectUrl;
        }
        return httpGet(redirectUrl, maxRedirects - 1).then(resolve).catch(reject);
      }

      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => resolve({ data, status: res.statusCode, url }));
    });

    request.on('error', reject);
    request.on('timeout', () => {
      request.destroy();
      reject(new Error('Request timeout'));
    });
  });
}

/**
 * Download binary data (for images)
 */
function downloadBinary(url, maxRedirects = 5) {
  return new Promise((resolve, reject) => {
    if (maxRedirects <= 0) {
      return reject(new Error('Too many redirects'));
    }

    const client = url.startsWith('https') ? https : http;
    const request = client.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept': 'image/*,*/*;q=0.8'
      },
      timeout: 15000
    }, (res) => {
      // Handle redirects
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        let redirectUrl = res.headers.location;
        if (redirectUrl.startsWith('/')) {
          const baseUrl = new URL(url);
          redirectUrl = baseUrl.origin + redirectUrl;
        } else if (!redirectUrl.startsWith('http')) {
          const baseUrl = new URL(url);
          redirectUrl = baseUrl.origin + '/' + redirectUrl;
        }
        return downloadBinary(redirectUrl, maxRedirects - 1).then(resolve).catch(reject);
      }

      if (res.statusCode !== 200) {
        return reject(new Error(`HTTP ${res.statusCode}`));
      }

      const chunks = [];
      res.on('data', chunk => chunks.push(chunk));
      res.on('end', () => resolve(Buffer.concat(chunks)));
    });

    request.on('error', reject);
    request.on('timeout', () => {
      request.destroy();
      reject(new Error('Request timeout'));
    });
  });
}

/**
 * Extract logo URLs from HTML using regex (no cheerio needed)
 */
function extractLogosFromHtml(html, baseUrl) {
  const logos = [];

  // Parse base URL
  let origin;
  try {
    origin = new URL(baseUrl).origin;
  } catch (e) {
    return logos;
  }

  // Helper to make absolute URL
  const makeAbsolute = (src) => {
    if (!src) return null;
    if (src.startsWith('data:')) return null; // Skip data URIs
    if (src.startsWith('//')) return 'https:' + src;
    if (src.startsWith('/')) return origin + src;
    if (src.startsWith('http')) return src;
    return origin + '/' + src;
  };

  // Pattern 1: og:image meta tag (often high quality logo)
  const ogImageMatch = html.match(/<meta[^>]*property=["']og:image["'][^>]*content=["']([^"']+)["']/i) ||
                       html.match(/<meta[^>]*content=["']([^"']+)["'][^>]*property=["']og:image["']/i);
  if (ogImageMatch) {
    const url = makeAbsolute(ogImageMatch[1]);
    if (url) logos.push({ url, priority: 1, source: 'og:image' });
  }

  // Pattern 2: Images with "logo" in alt, class, or id
  const imgTagRegex = /<img[^>]+>/gi;
  const imgTags = html.match(imgTagRegex) || [];

  for (const img of imgTags) {
    const srcMatch = img.match(/src=["']([^"']+)["']/i);
    const altMatch = img.match(/alt=["']([^"']+)["']/i);
    const classMatch = img.match(/class=["']([^"']+)["']/i);
    const idMatch = img.match(/id=["']([^"']+)["']/i);

    if (!srcMatch) continue;

    const src = srcMatch[1];
    const alt = altMatch ? altMatch[1].toLowerCase() : '';
    const cls = classMatch ? classMatch[1].toLowerCase() : '';
    const id = idMatch ? idMatch[1].toLowerCase() : '';

    // Check if it looks like a logo
    const isLogoByAttr = alt.includes('logo') || cls.includes('logo') || id.includes('logo');
    const isLogoByPath = src.toLowerCase().includes('logo');
    const isInHeader = html.toLowerCase().indexOf(img) < html.length * 0.3; // In first 30% of page

    if (isLogoByAttr || isLogoByPath) {
      const url = makeAbsolute(src);
      if (url) logos.push({ url, priority: isLogoByAttr ? 2 : 3, source: 'img-logo' });
    } else if (isInHeader && !src.includes('pixel') && !src.includes('tracking')) {
      const url = makeAbsolute(src);
      if (url) logos.push({ url, priority: 5, source: 'header-img' });
    }
  }

  // Pattern 3: Apple touch icon (usually square, good for logos)
  const touchIconMatch = html.match(/<link[^>]*rel=["']apple-touch-icon["'][^>]*href=["']([^"']+)["']/i) ||
                         html.match(/<link[^>]*href=["']([^"']+)["'][^>]*rel=["']apple-touch-icon["']/i);
  if (touchIconMatch) {
    const url = makeAbsolute(touchIconMatch[1]);
    if (url) logos.push({ url, priority: 4, source: 'apple-touch-icon' });
  }

  // Pattern 4: Favicon (last resort)
  const faviconMatch = html.match(/<link[^>]*rel=["'](?:shortcut )?icon["'][^>]*href=["']([^"']+)["']/i) ||
                       html.match(/<link[^>]*href=["']([^"']+)["'][^>]*rel=["'](?:shortcut )?icon["']/i);
  if (faviconMatch) {
    const url = makeAbsolute(faviconMatch[1]);
    if (url) logos.push({ url, priority: 6, source: 'favicon' });
  }

  // Sort by priority and remove duplicates
  const seen = new Set();
  return logos
    .sort((a, b) => a.priority - b.priority)
    .filter(logo => {
      if (seen.has(logo.url)) return false;
      seen.add(logo.url);
      return true;
    });
}

/**
 * Strategy 1: Scrape logo from brand website (PRIORITY)
 */
async function scrapeLogoFromWebsite(website, brandName) {
  try {
    if (!website) return null;

    // Normalize URL
    let url = website.trim();
    if (!url.startsWith('http')) {
      url = 'https://' + url;
    }

    console.log(`  Fetching website: ${url}`);
    const response = await httpGet(url);

    if (response.status !== 200) {
      console.log(`  ! Website returned status ${response.status}`);
      return null;
    }

    const logos = extractLogosFromHtml(response.data, url);

    if (logos.length > 0) {
      console.log(`  Found ${logos.length} potential logos`);

      // Try each logo URL until one works
      for (const logo of logos.slice(0, 3)) { // Try top 3
        try {
          // Verify the image is downloadable
          const imgBuffer = await downloadBinary(logo.url);
          if (imgBuffer && imgBuffer.length > 1000) { // At least 1KB
            console.log(`  ✓ Found logo via ${logo.source}: ${logo.url.substring(0, 60)}...`);
            return logo.url;
          }
        } catch (e) {
          // Try next logo
        }
      }
    }

    console.log(`  ! No usable logo found on website`);
  } catch (error) {
    console.log(`  ! Error scraping website: ${error.message}`);
  }
  return null;
}

/**
 * Strategy 2: Reuse existing dispensary logos for the brand
 */
async function getLogoFromDispensaries(brandId, brandName) {
  try {
    const result = await db.query(
      `SELECT logo_url FROM dispensaries
       WHERE brand_id = $1 AND logo_url IS NOT NULL AND logo_url != ''
       LIMIT 1`,
      [brandId]
    );

    if (result.rows.length > 0 && result.rows[0].logo_url) {
      console.log(`  ✓ Found logo from dispensary: ${result.rows[0].logo_url.substring(0, 60)}...`);
      return result.rows[0].logo_url;
    }
  } catch (error) {
    console.log(`  ! Error getting logo from dispensaries: ${error.message}`);
  }
  return null;
}

/**
 * Strategy 3: Search Google Images for brand logo
 */
async function searchGoogleForLogo(brandName) {
  try {
    const apiKey = process.env.GOOGLE_CUSTOM_SEARCH_API_KEY;
    const cx = process.env.GOOGLE_SEARCH_ENGINE_ID;

    if (!apiKey || !cx) {
      console.log('  ! Google Custom Search not configured (add GOOGLE_CUSTOM_SEARCH_API_KEY and GOOGLE_SEARCH_ENGINE_ID to .env)');
      return null;
    }

    const searchQuery = `${brandName} cannabis dispensary logo`;
    const url = `https://www.googleapis.com/customsearch/v1?q=${encodeURIComponent(searchQuery)}&cx=${cx}&key=${apiKey}&searchType=image&num=5&imgSize=medium`;

    console.log(`  Searching Google Images...`);
    const response = await httpGet(url);
    const data = JSON.parse(response.data);

    if (data.error) {
      console.log(`  ! Google API error: ${data.error.message}`);
      return null;
    }

    if (data.items && data.items.length > 0) {
      // Try each result until one works
      for (const item of data.items) {
        try {
          const imgBuffer = await downloadBinary(item.link);
          if (imgBuffer && imgBuffer.length > 1000) {
            console.log(`  ✓ Found logo via Google: ${item.link.substring(0, 60)}...`);
            return item.link;
          }
        } catch (e) {
          // Try next result
        }
      }
    }

    console.log(`  ! No usable logo found on Google`);
  } catch (error) {
    console.log(`  ! Error searching Google: ${error.message}`);
  }
  return null;
}

/**
 * Process image: make square and optimize
 */
async function processImage(inputBuffer) {
  if (!sharp) {
    return inputBuffer; // Return as-is if sharp not available
  }

  try {
    // Get image metadata
    const metadata = await sharp(inputBuffer).metadata();

    // Calculate dimensions for square crop (center crop)
    const size = Math.min(metadata.width || TARGET_SIZE, metadata.height || TARGET_SIZE);

    // Process: resize to square, optimize
    const processed = await sharp(inputBuffer)
      .resize(TARGET_SIZE, TARGET_SIZE, {
        fit: 'cover',      // Cover the square area
        position: 'center' // Center the image
      })
      .jpeg({
        quality: JPEG_QUALITY,
        mozjpeg: true      // Better compression
      })
      .toBuffer();

    return processed;
  } catch (error) {
    console.log(`  ! Image processing failed: ${error.message}`);
    return inputBuffer; // Return original if processing fails
  }
}

/**
 * Download, process, and save logo
 */
async function downloadAndSaveLogo(logoUrl, brandSlug) {
  try {
    console.log(`  Downloading logo...`);
    const buffer = await downloadBinary(logoUrl);

    if (!buffer || buffer.length < 500) {
      console.log(`  ! Downloaded file too small`);
      return null;
    }

    // Process image (make square + optimize)
    console.log(`  Processing image (${Math.round(buffer.length / 1024)}KB)...`);
    const processed = await processImage(buffer);

    const filename = `${brandSlug}.jpg`;
    const filepath = path.join(OUTPUT_DIR, filename);

    fs.writeFileSync(filepath, processed);

    const savedSize = Math.round(processed.length / 1024);
    console.log(`  ✓ Saved: ${filename} (${savedSize}KB, ${TARGET_SIZE}x${TARGET_SIZE}px)`);

    return `/brand-logos/${filename}`;
  } catch (error) {
    console.log(`  ! Error downloading/saving logo: ${error.message}`);
    return null;
  }
}

/**
 * Main function to scrape logos for all brands
 */
async function scrapeAllBrandLogos() {
  console.log('===========================================');
  console.log('       BRAND LOGO SCRAPER');
  console.log('===========================================\n');
  console.log(`Output directory: ${OUTPUT_DIR}`);
  console.log(`Target size: ${TARGET_SIZE}x${TARGET_SIZE}px (square)`);
  console.log(`JPEG quality: ${JPEG_QUALITY}%`);
  console.log(`Sharp available: ${sharp ? 'Yes' : 'No (images won\'t be processed)'}\n`);

  try {
    // Get all brands without logos, prioritized by location count
    const result = await db.query(`
      SELECT id, name, slug, website, logo_url
      FROM brands
      WHERE logo_url IS NULL OR logo_url = ''
      ORDER BY location_count DESC NULLS LAST
    `);

    const brands = result.rows;
    console.log(`Found ${brands.length} brands without logos\n`);

    if (brands.length === 0) {
      console.log('All brands already have logos!');
      return;
    }

    let processed = 0;
    let found = 0;
    let downloaded = 0;

    for (const brand of brands) {
      console.log(`\n[${processed + 1}/${brands.length}] ${brand.name}`);
      console.log(`  Slug: ${brand.slug}`);
      console.log(`  Website: ${brand.website || 'None'}`);

      let logoUrl = null;

      // Strategy 1: Scrape from website (PRIORITY)
      if (brand.website) {
        logoUrl = await scrapeLogoFromWebsite(brand.website, brand.name);
        await sleep(1500); // Rate limit
      }

      // Strategy 2: Reuse from dispensaries
      if (!logoUrl) {
        console.log(`  Checking dispensary logos...`);
        logoUrl = await getLogoFromDispensaries(brand.id, brand.name);
      }

      // Strategy 3: Google Image Search
      if (!logoUrl) {
        logoUrl = await searchGoogleForLogo(brand.name);
        await sleep(2000); // Rate limit for Google
      }

      if (logoUrl) {
        found++;

        // Download, process, and save
        const localPath = await downloadAndSaveLogo(logoUrl, brand.slug);

        if (localPath) {
          downloaded++;

          // Update database with logo URL (use original URL for reference)
          await db.query(
            'UPDATE brands SET logo_url = $1 WHERE id = $2',
            [logoUrl, brand.id]
          );
          console.log(`  ✓ Database updated`);
        }
      } else {
        console.log(`  ✗ No logo found for ${brand.name}`);
      }

      processed++;

      // Progress report every 10 brands
      if (processed % 10 === 0) {
        console.log(`\n--- Progress: ${processed}/${brands.length} | Found: ${found} | Downloaded: ${downloaded} ---\n`);
      }
    }

    console.log(`\n\n===========================================`);
    console.log(`           SCRAPING COMPLETE`);
    console.log(`===========================================`);
    console.log(`Processed: ${processed} brands`);
    console.log(`Logos found: ${found}`);
    console.log(`Logos downloaded: ${downloaded}`);
    console.log(`Still missing: ${processed - found}`);
    console.log(`===========================================\n`);

  } catch (error) {
    console.error('Error in logo scraping:', error);
  } finally {
    await db.pool.end();
  }
}

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// Run if called directly
if (require.main === module) {
  scrapeAllBrandLogos()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { scrapeAllBrandLogos };
