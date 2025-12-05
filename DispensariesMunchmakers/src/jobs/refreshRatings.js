require('dotenv').config();
const db = require('../config/database');
const googlePlaces = require('../services/googlePlaces');

async function refreshRatings() {
  console.log('Starting rating refresh job...');

  try {
    // Get top 100 dispensaries per state based on rankings
    const dispensaries = await db.query(`
      SELECT DISTINCT ON (r.dispensary_id)
        d.id,
        d.google_place_id,
        d.name
      FROM rankings r
      JOIN dispensaries d ON r.dispensary_id = d.id
      WHERE d.is_active = true
        AND d.google_place_id IS NOT NULL
        AND r.location_type = 'state'
      ORDER BY r.dispensary_id, r.composite_score DESC
      LIMIT 100
    `);

    console.log(`Refreshing ratings for ${dispensaries.rows.length} dispensaries...`);

    let updated = 0;
    let failed = 0;

    for (const dispensary of dispensaries.rows) {
      try {
        await new Promise(resolve => setTimeout(resolve, 1000)); // Rate limit

        const details = await googlePlaces.getPlaceDetails(dispensary.google_place_id);

        await db.query(
          `UPDATE dispensaries
           SET google_rating = $1,
               google_review_count = $2,
               updated_at = NOW()
           WHERE id = $3`,
          [
            details.rating,
            details.user_ratings_total || 0,
            dispensary.id
          ]
        );

        updated++;

        if (updated % 10 === 0) {
          console.log(`  Updated ${updated}/${dispensaries.rows.length}...`);
        }
      } catch (error) {
        console.error(`Error updating ${dispensary.name}:`, error.message);
        failed++;
      }
    }

    console.log('✓ Rating refresh completed!');
    console.log(`  Updated: ${updated}`);
    console.log(`  Failed: ${failed}`);

    process.exit(0);
  } catch (error) {
    console.error('Rating refresh failed:', error);
    process.exit(1);
  }
}

// Run if called directly
if (require.main === module) {
  refreshRatings();
}

module.exports = { refreshRatings };
