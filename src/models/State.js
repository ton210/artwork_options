const db = require('../config/database');

class State {
  static async findAll() {
    const result = await db.query(
      'SELECT * FROM states ORDER BY name ASC'
    );
    return result.rows;
  }

  static async findById(id) {
    const result = await db.query(
      'SELECT * FROM states WHERE id = $1',
      [id]
    );
    return result.rows[0];
  }

  static async findBySlug(slug) {
    const result = await db.query(
      'SELECT * FROM states WHERE slug = $1',
      [slug]
    );
    return result.rows[0];
  }

  static async getCounties(stateId) {
    const result = await db.query(
      `SELECT c.*, COUNT(d.id) as dispensary_count
       FROM counties c
       LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
       WHERE c.state_id = $1
       GROUP BY c.id
       ORDER BY c.name ASC`,
      [stateId]
    );
    return result.rows;
  }

  static async getStats(stateId) {
    const result = await db.query(
      `SELECT
         COUNT(DISTINCT c.id) as county_count,
         COUNT(d.id) as dispensary_count,
         AVG(d.google_rating) as avg_rating
       FROM states s
       LEFT JOIN counties c ON s.id = c.state_id
       LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
       WHERE s.id = $1
       GROUP BY s.id`,
      [stateId]
    );
    return result.rows[0];
  }
}

class County {
  static async findById(id) {
    const result = await db.query(
      `SELECT c.*, s.name as state_name, s.slug as state_slug, s.abbreviation as state_abbr
       FROM counties c
       JOIN states s ON c.state_id = s.id
       WHERE c.id = $1`,
      [id]
    );
    return result.rows[0];
  }

  static async findBySlug(stateSlug, countySlug) {
    const result = await db.query(
      `SELECT c.*, s.name as state_name, s.slug as state_slug, s.abbreviation as state_abbr
       FROM counties c
       JOIN states s ON c.state_id = s.id
       WHERE s.slug = $1 AND c.slug = $2`,
      [stateSlug, countySlug]
    );
    return result.rows[0];
  }

  static async getStats(countyId) {
    const result = await db.query(
      `SELECT
         COUNT(d.id) as dispensary_count,
         AVG(d.google_rating) as avg_rating,
         SUM(d.google_review_count) as total_reviews
       FROM dispensaries d
       WHERE d.county_id = $1 AND d.is_active = true`,
      [countyId]
    );
    return result.rows[0];
  }
}

module.exports = { State, County };
