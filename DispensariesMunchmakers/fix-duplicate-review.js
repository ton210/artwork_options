require('dotenv').config();
const db = require('./src/config/database');

async function fixDuplicate() {
  try {
    const result = await db.query("SELECT id FROM reviews WHERE author_name = 'Malcolm R.' ORDER BY id");

    if (result.rows.length > 1) {
      const duplicateId = result.rows[1].id;
      await db.query('DELETE FROM reviews WHERE id = $1', [duplicateId]);
      console.log('✓ Deleted duplicate review ID:', duplicateId);
    } else {
      console.log('No duplicate found');
    }
  } catch (error) {
    console.error('Error:', error);
  } finally {
    await db.pool.end();
  }
}

fixDuplicate();
