require('dotenv').config();
const db = require('./src/config/database');

/**
 * Clean URL by removing all UTM parameters and adding only utm_source=munchmakers
 */
function cleanAndAddUtm(url) {
  if (!url) return null;

  try {
    // Ensure URL has protocol
    let cleanUrl = url.trim();
    if (!cleanUrl.startsWith('http://') && !cleanUrl.startsWith('https://')) {
      cleanUrl = 'https://' + cleanUrl;
    }

    const urlObj = new URL(cleanUrl);

    // Remove all UTM parameters
    const utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
    utmParams.forEach(param => urlObj.searchParams.delete(param));

    // Also remove any other common tracking parameters
    const trackingParams = ['ref', 'fbclid', 'gclid', 'gclsrc', 'dclid', 'msclkid', 'mc_cid', 'mc_eid'];
    trackingParams.forEach(param => urlObj.searchParams.delete(param));

    // Add only utm_source=munchmakers
    urlObj.searchParams.set('utm_source', 'munchmakers');

    return urlObj.toString();
  } catch (error) {
    console.error(`  ! Error parsing URL "${url}":`, error.message);
    return null;
  }
}

async function updateAllWebsites() {
  console.log('===========================================');
  console.log('  UPDATE UTM PARAMETERS FOR DISPENSARIES');
  console.log('===========================================\n');

  try {
    // Get all dispensaries with websites
    const result = await db.query(`
      SELECT id, name, website
      FROM dispensaries
      WHERE website IS NOT NULL AND website != ''
      ORDER BY id
    `);

    const dispensaries = result.rows;
    console.log(`Found ${dispensaries.length} dispensaries with websites\n`);

    let updated = 0;
    let skipped = 0;
    let errors = 0;

    for (const disp of dispensaries) {
      const newUrl = cleanAndAddUtm(disp.website);

      if (!newUrl) {
        console.log(`[SKIP] ${disp.name}: Invalid URL "${disp.website}"`);
        errors++;
        continue;
      }

      if (newUrl === disp.website) {
        // Already has correct UTM
        skipped++;
        continue;
      }

      // Update the database
      await db.query(
        'UPDATE dispensaries SET website = $1 WHERE id = $2',
        [newUrl, disp.id]
      );

      console.log(`[UPDATE] ${disp.name}`);
      console.log(`   Old: ${disp.website}`);
      console.log(`   New: ${newUrl}`);

      updated++;

      // Progress every 100
      if ((updated + skipped + errors) % 100 === 0) {
        console.log(`\n--- Progress: ${updated + skipped + errors}/${dispensaries.length} ---\n`);
      }
    }

    console.log(`\n===========================================`);
    console.log(`             COMPLETE`);
    console.log(`===========================================`);
    console.log(`Total dispensaries: ${dispensaries.length}`);
    console.log(`Updated: ${updated}`);
    console.log(`Already correct: ${skipped}`);
    console.log(`Errors: ${errors}`);
    console.log(`===========================================\n`);

  } catch (error) {
    console.error('Error:', error);
  } finally {
    await db.pool.end();
  }
}

// Run
updateAllWebsites();
