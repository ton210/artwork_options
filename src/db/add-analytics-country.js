require('dotenv').config();
const db = require('../config/database');

async function addAnalyticsCountry() {
  console.log('Adding country tracking to analytics tables...\n');

  try {
    await db.query(`
      ALTER TABLE page_views
      ADD COLUMN IF NOT EXISTS country VARCHAR(2),
      ADD COLUMN IF NOT EXISTS url_path TEXT;
    `);
    console.log('✓ Added country and url_path columns to page_views');

    await db.query(`
      CREATE INDEX IF NOT EXISTS idx_page_views_country ON page_views(country);
      CREATE INDEX IF NOT EXISTS idx_page_views_url_path ON page_views(url_path);
    `);
    console.log('✓ Created analytics indexes');

    console.log('\n=== Analytics country tracking added! ===');

  } catch (error) {
    console.error('Error adding analytics columns:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  addAnalyticsCountry()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { addAnalyticsCountry };
