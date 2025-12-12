const db = require('../config/database');

async function createTranslationsTable() {
  console.log('Creating translations table...');

  try {
    await db.query(`
      CREATE TABLE IF NOT EXISTS translations (
        id SERIAL PRIMARY KEY,
        content_key VARCHAR(255) NOT NULL,
        content_type VARCHAR(50) NOT NULL,
        source_language VARCHAR(10) DEFAULT 'en',
        target_language VARCHAR(10) NOT NULL,
        source_text TEXT NOT NULL,
        translated_text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW(),
        UNIQUE(content_key, target_language)
      )
    `);

    await db.query(`
      CREATE INDEX IF NOT EXISTS idx_translations_lookup
      ON translations(content_key, target_language)
    `);

    console.log('✓ Translations table created successfully');
  } catch (error) {
    console.error('Error creating translations table:', error);
    throw error;
  }
}

if (require.main === module) {
  createTranslationsTable()
    .then(() => {
      console.log('Migration complete');
      process.exit(0);
    })
    .catch(err => {
      console.error('Migration failed:', err);
      process.exit(1);
    });
}

module.exports = { createTranslationsTable };
