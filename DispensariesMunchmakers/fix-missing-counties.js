require('dotenv').config();
const db = require('./src/config/database');

async function fixMissingCounties() {
  console.log('Fixing missing county assignments...\n');

  try {
    // Get dispensaries without county_id
    const result = await db.query(`
      SELECT d.id, d.name, d.city, d.address_street, d.zip
      FROM dispensaries d
      WHERE d.is_active = true AND d.county_id IS NULL
      ORDER BY d.city
    `);

    console.log(`Found ${result.rows.length} dispensaries without county_id\n`);

    let fixed = 0;
    let notFound = 0;

    for (const disp of result.rows) {
      // Try to find county by city name (fuzzy match)
      const countyResult = await db.query(`
        SELECT c.id, c.name, s.abbreviation
        FROM counties c
        JOIN states s ON c.state_id = s.id
        WHERE LOWER(c.name) LIKE LOWER($1)
           OR LOWER(c.name) LIKE '%' || LOWER($2) || '%'
        LIMIT 1
      `, [disp.city, disp.city]);

      if (countyResult.rows.length > 0) {
        const county = countyResult.rows[0];

        // Update dispensary with found county
        await db.query(
          'UPDATE dispensaries SET county_id = $1 WHERE id = $2',
          [county.id, disp.id]
        );

        console.log(`✓ Fixed: ${disp.name} (${disp.city}) → ${county.name} County, ${county.abbreviation}`);
        fixed++;
      } else {
        console.log(`✗ Not found: ${disp.name} (${disp.city})`);
        notFound++;
      }
    }

    console.log(`\n=== Summary ===`);
    console.log(`Fixed: ${fixed}`);
    console.log(`Not found: ${notFound}`);
    console.log(`Remaining without county: ${notFound}`);

  } catch (error) {
    console.error('Error:', error);
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  fixMissingCounties()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { fixMissingCounties };
