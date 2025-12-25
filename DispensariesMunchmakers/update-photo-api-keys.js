require('dotenv').config();
const db = require('./src/config/database');

const OLD_KEY = 'AIzaSyCd_kX94LwunGDgupq3eIj9p-RJ3YVi4Tw';
const NEW_KEY = process.env.GOOGLE_PLACES_API_KEY || 'AIzaSyCg9QV1rVp7WmVxZDyF5mzQHkXBY6Vu1x4';

async function updatePhotoApiKeys() {
  console.log('Updating photo URLs with new API key...\n');
  console.log(`Old key: ${OLD_KEY}`);
  console.log(`New key: ${NEW_KEY}\n`);

  try {
    // Get all dispensaries with photos or logo_url
    const result = await db.query(`
      SELECT id, logo_url, photos
      FROM dispensaries
      WHERE logo_url IS NOT NULL OR photos IS NOT NULL
    `);

    console.log(`Found ${result.rows.length} dispensaries with photos\n`);

    let updated = 0;

    for (const dispensary of result.rows) {
      let needsUpdate = false;
      let newLogoUrl = dispensary.logo_url;
      let newPhotos = dispensary.photos;

      // Update logo_url if it contains old key
      if (dispensary.logo_url && dispensary.logo_url.includes(OLD_KEY)) {
        newLogoUrl = dispensary.logo_url.replace(OLD_KEY, NEW_KEY);
        needsUpdate = true;
      }

      // Update photos array if it contains old key
      if (dispensary.photos) {
        const photosArray = typeof dispensary.photos === 'string'
          ? JSON.parse(dispensary.photos)
          : dispensary.photos;

        if (Array.isArray(photosArray) && photosArray.length > 0) {
          const updatedPhotos = photosArray.map(url => {
            if (typeof url === 'string' && url.includes(OLD_KEY)) {
              needsUpdate = true;
              return url.replace(OLD_KEY, NEW_KEY);
            }
            return url;
          });

          if (needsUpdate) {
            newPhotos = JSON.stringify(updatedPhotos);
          }
        }
      }

      // Update database if needed
      if (needsUpdate) {
        await db.query(
          `UPDATE dispensaries
           SET logo_url = $1, photos = $2
           WHERE id = $3`,
          [newLogoUrl, newPhotos, dispensary.id]
        );
        updated++;

        if (updated % 100 === 0) {
          console.log(`  Updated ${updated} dispensaries...`);
        }
      }
    }

    console.log(`\n✓ Successfully updated ${updated} dispensaries with new API key!`);
    console.log(`\nImages should now load correctly on all pages.`);

  } catch (error) {
    console.error('Error updating photo URLs:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  updatePhotoApiKeys()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { updatePhotoApiKeys };
