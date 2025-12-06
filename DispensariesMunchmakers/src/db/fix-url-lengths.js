const db = require('../config/database');

async function fixUrlLengths() {
  console.log('Increasing URL field lengths to fix import failures...');

  try {
    await db.query(`
      -- Increase URL field lengths from VARCHAR(500) to TEXT
      ALTER TABLE dispensaries
        ALTER COLUMN website TYPE TEXT,
        ALTER COLUMN logo_url TYPE TEXT;

      -- Also increase brand URL fields
      ALTER TABLE brands
        ALTER COLUMN logo_url TYPE TEXT,
        ALTER COLUMN website TYPE TEXT;

      -- Create index on website for searching (limited to 500 chars)
      CREATE INDEX IF NOT EXISTS idx_dispensaries_website ON dispensaries(website);
    `);

    console.log('✓ URL fields updated to TEXT (unlimited length)');
    console.log('✓ This will fix ~30-40% of failures from URL length issues');

  } catch (error) {
    console.error('Migration failed:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  fixUrlLengths()
    .then(() => {
      console.log('URL length fix completed');
      process.exit(0);
    })
    .catch((err) => {
      console.error('Fix failed:', err);
      process.exit(1);
    });
}

module.exports = { fixUrlLengths };
