require('dotenv').config();
const db = require('./src/config/database');

async function clearTranslationCache() {
  try {
    console.log('Clearing translation cache from database...');

    // Delete all cached page translations
    const result = await db.query(`
      DELETE FROM translations
      WHERE content_type = 'page'
    `);

    console.log(`✓ Deleted ${result.rowCount} cached page translations`);
    console.log('✅ Translation cache cleared!');
    console.log('\nNote: Server restart required for in-memory cache to be cleared.');

  } catch (error) {
    console.error('❌ Error clearing cache:', error);
  } finally {
    await db.pool.end();
  }
}

clearTranslationCache();
