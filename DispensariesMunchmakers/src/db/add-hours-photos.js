require('dotenv').config();
const db = require('../config/database');

async function addHoursAndPhotos() {
  console.log('Adding hours and photos columns to dispensaries...\n');

  try {
    await db.query(`
      ALTER TABLE dispensaries
      ADD COLUMN IF NOT EXISTS hours JSONB,
      ADD COLUMN IF NOT EXISTS is_open_now BOOLEAN DEFAULT NULL;
    `);
    console.log('✓ Added hours and is_open_now columns');

    // photo_urls already exists as TEXT, but let's ensure it's JSONB for better handling
    await db.query(`
      ALTER TABLE dispensaries
      ADD COLUMN IF NOT EXISTS photos JSONB;
    `);
    console.log('✓ Added photos JSONB column');

    console.log('\n=== Hours and photos columns added successfully! ===');

  } catch (error) {
    console.error('Error adding columns:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  addHoursAndPhotos()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { addHoursAndPhotos };
