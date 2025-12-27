const db = require('../config/database');

class Ranking {
  static async upsert(dispensaryId, locationType, locationId, compositeScore) {
    const result = await db.query(
      `INSERT INTO rankings (dispensary_id, location_type, location_id, composite_score)
       VALUES ($1, $2, $3, $4)
       ON CONFLICT (dispensary_id, location_type, location_id)
       DO UPDATE SET
         previous_rank = rankings.rank,
         composite_score = EXCLUDED.composite_score,
         calculated_at = NOW()
       RETURNING *`,
      [dispensaryId, locationType, locationId, compositeScore]
    );

    return result.rows[0];
  }

  static async updateRanks(locationType, locationId) {
    await db.query(
      `WITH ranked AS (
        SELECT id, ROW_NUMBER() OVER (ORDER BY composite_score DESC) as new_rank
        FROM rankings
        WHERE location_type = $1 AND location_id = $2
      )
      UPDATE rankings r
      SET rank = ranked.new_rank
      FROM ranked
      WHERE r.id = ranked.id`,
      [locationType, locationId]
    );
  }

  static async getByLocation(locationType, locationId, limit = 100) {
    const result = await db.query(
      `SELECT r.*, d.id as dispensary_id, d.name, d.slug, d.logo_url, d.address_street, d.city,
              d.google_rating, d.google_review_count, d.phone, d.website
       FROM rankings r
       JOIN dispensaries d ON r.dispensary_id = d.id
       WHERE r.location_type = $1 AND r.location_id = $2
         AND d.is_active = true
       ORDER BY r.rank ASC
       LIMIT $3`,
      [locationType, locationId, limit]
    );

    return result.rows;
  }

  static async getByDispensary(dispensaryId) {
    const result = await db.query(
      `SELECT * FROM rankings
       WHERE dispensary_id = $1
       ORDER BY location_type, rank`,
      [dispensaryId]
    );

    return result.rows;
  }

  static async getRankHistory(dispensaryId, locationType, locationId, days = 30) {
    const result = await db.query(
      `SELECT rank, composite_score, calculated_at
       FROM rankings
       WHERE dispensary_id = $1
         AND location_type = $2
         AND location_id = $3
         AND calculated_at >= NOW() - INTERVAL '${days} days'
       ORDER BY calculated_at DESC`,
      [dispensaryId, locationType, locationId]
    );

    return result.rows;
  }

  static async getStats(locationType, locationId) {
    const result = await db.query(
      `SELECT
         COUNT(*) as total_ranked,
         AVG(composite_score) as avg_score,
         MAX(composite_score) as max_score,
         MIN(composite_score) as min_score
       FROM rankings
       WHERE location_type = $1 AND location_id = $2`,
      [locationType, locationId]
    );

    return result.rows[0];
  }

  static async deleteByDispensary(dispensaryId) {
    await db.query('DELETE FROM rankings WHERE dispensary_id = $1', [dispensaryId]);
  }
}

module.exports = Ranking;
