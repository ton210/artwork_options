require('dotenv').config();
const https = require('https');
const http = require('http');
const fs = require('fs');
const path = require('path');
const db = require('./src/config/database');

// Simple HTTP GET function to replace axios
function httpGet(url) {
  return new Promise((resolve, reject) => {
    const client = url.startsWith('https') ? https : http;
    client.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
      }
    }, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => resolve({ data, status: res.statusCode }));
    }).on('error', reject);
  });
}

const OUTPUT_DIR = './brand-logos';

// Ensure output directory exists
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

/**
 * Strategy 1: Reuse existing dispensary logos for the brand
 */
async function getLogoFromDispensaries(brandId, brandName) {
  try {
    const result = await db.query(
      `SELECT logo_url FROM dispensaries
       WHERE brand_id = $1 AND logo_url IS NOT NULL
       LIMIT 1`,
      [brandId]
    );

    if (result.rows.length > 0) {
      console.log(`  ✓ Found logo from dispensary for ${brandName}`);
      return result.rows[0].logo_url;
    }
  } catch (error) {
    console.error(`  Error getting logo from dispensaries:`, error.message);
  }
  return null;
}

/**
 * Strategy 2: Search Google Custom Search for brand logo
 */
async function searchGoogleForLogo(brandName) {
  try {
    const apiKey = process.env.GOOGLE_CUSTOM_SEARCH_API_KEY;
    const cx = process.env.GOOGLE_SEARCH_ENGINE_ID;

    if (!apiKey || !cx) {
      console.log('  ! Google Custom Search not configured');
      return null;
    }

    const searchQuery = `${brandName} dispensary logo`;
    const url = `https://www.googleapis.com/customsearch/v1?q=${encodeURIComponent(searchQuery)}&cx=${cx}&key=${apiKey}&searchType=image&num=3&imgSize=medium&imgType=photo`;

    const response = await httpGet(url);
    const data = JSON.parse(response.data);

    if (data.items && data.items.length > 0) {
      // Return first image result
      const logoUrl = data.items[0].link;
      console.log(`  ✓ Found logo via Google Image Search: ${logoUrl.substring(0, 60)}...`);
      return logoUrl;
    }
  } catch (error) {
    console.error(`  Error searching Google:`, error.message);
  }
  return null;
}

/**
 * Strategy 3: Simple pattern matching for common logo URLs
 */
async function guessLogoFromWebsite(website, brandName) {
  try {
    if (!website) return null;

    // Normalize URL
    let url = website;
    if (!url.startsWith('http')) {
      url = 'https://' + url;
    }

    const baseUrl = new URL(url);
    const domain = baseUrl.origin;

    // Common logo URL patterns
    const logoPatterns = [
      `${domain}/logo.png`,
      `${domain}/logo.jpg`,
      `${domain}/images/logo.png`,
      `${domain}/assets/logo.png`,
      `${domain}/wp-content/uploads/logo.png`,
      `${domain}/static/logo.png`
    ];

    // Try each pattern
    for (const logoUrl of logoPatterns) {
      try {
        const testResponse = await httpGet(logoUrl);
        if (testResponse.status === 200) {
          console.log(`  ✓ Found logo at: ${logoUrl}`);
          return logoUrl;
        }
      } catch (err) {
        // Pattern not found, continue
      }
    }

  } catch (error) {
    console.error(`  Error checking website patterns:`, error.message);
  }
  return null;
}

/**
 * Download and save logo as square JPEG
 */
async function downloadAndSaveLogo(logoUrl, brandSlug) {
  try {
    return new Promise((resolve, reject) => {
      const client = logoUrl.startsWith('https') ? https : http;
      client.get(logoUrl, {
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
      }, (res) => {
        const chunks = [];
        res.on('data', chunk => chunks.push(chunk));
        res.on('end', () => {
          const buffer = Buffer.concat(chunks);
          const filename = `${brandSlug}.jpg`;
          const filepath = path.join(OUTPUT_DIR, filename);

          fs.writeFileSync(filepath, buffer);
          console.log(`  ✓ Downloaded and saved: ${filename}`);
          resolve(`/brand-logos/${filename}`);
        });
      }).on('error', (err) => {
        console.error(`  Error downloading logo:`, err.message);
        resolve(null);
      });
    });
  } catch (error) {
    console.error(`  Error downloading logo:`, error.message);
    return null;
  }
}

/**
 * Main function to scrape logos for all brands
 */
async function scrapeAllBrandLogos() {
  console.log('Starting brand logo scraping...\n');

  try {
    // Get all brands
    const result = await db.query(`
      SELECT id, name, slug, website, logo_url
      FROM brands
      ORDER BY location_count DESC
    `);

    const brands = result.rows;
    console.log(`Found ${brands.length} brands\n`);

    let processed = 0;
    let found = 0;
    let downloaded = 0;

    for (const brand of brands) {
      console.log(`\n[${processed + 1}/${brands.length}] Processing: ${brand.name}`);

      // Skip if already has logo
      if (brand.logo_url) {
        console.log(`  → Already has logo: ${brand.logo_url.substring(0, 60)}...`);
        processed++;
        found++;
        continue;
      }

      let logoUrl = null;

      // Strategy 1: Reuse from dispensaries
      logoUrl = await getLogoFromDispensaries(brand.id, brand.name);

      // Strategy 2: Google Image Search
      if (!logoUrl) {
        console.log(`  Searching Google Images...`);
        logoUrl = await searchGoogleForLogo(brand.name);
        await sleep(2000); // Rate limit
      }

      // Strategy 3: Guess common logo URLs
      if (!logoUrl && brand.website) {
        console.log(`  Checking website for logo: ${brand.website}`);
        logoUrl = await guessLogoFromWebsite(brand.website, brand.name);
        await sleep(1000); // Rate limit
      }

      if (logoUrl) {
        found++;

        // Download and save
        const localPath = await downloadAndSaveLogo(logoUrl, brand.slug);

        if (localPath) {
          downloaded++;
          // Update database with logo URL
          await db.query(
            'UPDATE brands SET logo_url = $1 WHERE id = $2',
            [logoUrl, brand.id]
          );
          console.log(`  ✓ Updated database with logo URL`);
        }
      } else {
        console.log(`  ✗ No logo found for ${brand.name}`);
      }

      processed++;

      // Rate limiting
      if (processed % 5 === 0) {
        console.log(`\n--- Progress: ${processed}/${brands.length} (${found} logos found, ${downloaded} downloaded) ---\n`);
        await sleep(3000);
      }
    }

    console.log(`\n\n=== Logo Scraping Complete ===`);
    console.log(`Processed: ${processed} brands`);
    console.log(`Logos found: ${found}`);
    console.log(`Logos downloaded: ${downloaded}`);
    console.log(`Missing: ${processed - found}`);

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
