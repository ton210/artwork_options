const axios = require('axios');
const crypto = require('crypto');
const db = require('../config/database');

// Cloudflare R2 configuration
const {
  S3Client,
  PutObjectCommand,
  HeadObjectCommand
} = require('@aws-sdk/client-s3');

const r2Client = new S3Client({
  region: 'auto',
  endpoint: `https://${process.env.R2_ACCOUNT_ID}.r2.cloudflarestorage.com`,
  credentials: {
    accessKeyId: process.env.R2_ACCESS_KEY_ID,
    secretAccessKey: process.env.R2_SECRET_ACCESS_KEY,
  },
});

const R2_BUCKET = process.env.R2_BUCKET || 'munchmakers-grinder';
const R2_PUBLIC_URL = process.env.R2_PUBLIC_URL || 'https://pub-0cf6e905891a477fbe1dc1b8360ef92c.r2.dev';

/**
 * Check if a photo exists on R2
 */
async function photoExistsOnR2(key) {
  try {
    await r2Client.send(new HeadObjectCommand({
      Bucket: R2_BUCKET,
      Key: key
    }));
    return true;
  } catch (error) {
    return false;
  }
}

/**
 * Download photo from Google and upload to R2
 */
async function migratePhotoToR2(googlePhotoUrl, dispensarySlug, photoIndex) {
  try {
    // Generate unique filename
    const hash = crypto.createHash('md5').update(googlePhotoUrl).digest('hex').substring(0, 8);
    const key = `dispensaries/${dispensarySlug}/${photoIndex}-${hash}.jpg`;

    // Check if already exists on R2
    const exists = await photoExistsOnR2(key);
    if (exists) {
      return `${R2_PUBLIC_URL}/${key}`;
    }

    // Download from Google
    console.log(`  Downloading photo ${photoIndex} for ${dispensarySlug}...`);
    const response = await axios.get(googlePhotoUrl, {
      responseType: 'arraybuffer',
      timeout: 10000
    });

    // Upload to R2
    await r2Client.send(new PutObjectCommand({
      Bucket: R2_BUCKET,
      Key: key,
      Body: response.data,
      ContentType: 'image/jpeg',
      CacheControl: 'public, max-age=31536000' // Cache for 1 year
    }));

    const r2Url = `${R2_PUBLIC_URL}/${key}`;
    console.log(`  ✓ Uploaded to R2: ${r2Url}`);

    return r2Url;
  } catch (error) {
    console.error(`  ✗ Error migrating photo:`, error.message);
    // Return original URL as fallback
    return googlePhotoUrl;
  }
}

/**
 * Migrate all photos for a dispensary (lazy migration on page load)
 */
async function migrateDispensaryPhotos(dispensary) {
  try {
    // Check if already migrated (R2 URLs don't contain googleapis.com)
    const logoNeedsMigration = dispensary.logo_url &&
                                dispensary.logo_url.includes('googleapis.com');

    let photosArray = [];
    if (dispensary.photos) {
      photosArray = typeof dispensary.photos === 'string'
        ? JSON.parse(dispensary.photos)
        : dispensary.photos;
    }

    const photosNeedMigration = photosArray.some(url =>
      typeof url === 'string' && url.includes('googleapis.com')
    );

    // If nothing needs migration, return original data
    if (!logoNeedsMigration && !photosNeedMigration) {
      return {
        logo_url: dispensary.logo_url,
        photos: photosArray,
        migrated: false
      };
    }

    console.log(`Migrating photos for: ${dispensary.name} (${dispensary.slug})`);

    // Migrate logo
    let newLogoUrl = dispensary.logo_url;
    if (logoNeedsMigration) {
      newLogoUrl = await migratePhotoToR2(
        dispensary.logo_url,
        dispensary.slug,
        'logo'
      );
    }

    // Migrate photos array
    const newPhotos = [];
    for (let i = 0; i < photosArray.length; i++) {
      const photoUrl = photosArray[i];
      if (typeof photoUrl === 'string' && photoUrl.includes('googleapis.com')) {
        const r2Url = await migratePhotoToR2(photoUrl, dispensary.slug, i);
        newPhotos.push(r2Url);
      } else {
        newPhotos.push(photoUrl);
      }

      // Rate limiting: small delay between photos
      if (i < photosArray.length - 1) {
        await new Promise(resolve => setTimeout(resolve, 200));
      }
    }

    // Update database with R2 URLs
    await db.query(
      `UPDATE dispensaries
       SET logo_url = $1, photos = $2
       WHERE id = $3`,
      [newLogoUrl, JSON.stringify(newPhotos), dispensary.id]
    );

    console.log(`  ✓ Updated database for ${dispensary.name}`);

    return {
      logo_url: newLogoUrl,
      photos: newPhotos,
      migrated: true
    };

  } catch (error) {
    console.error('Error in migrateDispensaryPhotos:', error);
    // Return original data on error
    return {
      logo_url: dispensary.logo_url,
      photos: typeof dispensary.photos === 'string'
        ? JSON.parse(dispensary.photos)
        : dispensary.photos,
      migrated: false
    };
  }
}

/**
 * Migrate photos and return updated dispensary data
 * This runs async in background, doesn't block page render
 */
async function ensurePhotosOnR2(dispensary) {
  // Don't block the page render - migrate in background
  setImmediate(async () => {
    try {
      await migrateDispensaryPhotos(dispensary);
    } catch (error) {
      console.error('Background photo migration error:', error);
    }
  });

  // Return original data immediately for page render
  return dispensary;
}

module.exports = {
  migrateDispensaryPhotos,
  migratePhotoToR2,
  ensurePhotosOnR2,
  photoExistsOnR2
};
