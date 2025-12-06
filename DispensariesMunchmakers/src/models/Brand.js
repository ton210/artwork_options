const db = require('../config/database');

class Brand {
  static async findAll(options = {}) {
    const { orderBy = 'location_count DESC', limit, offset } = options;

    let query = `
      SELECT b.*,
             COUNT(d.id) as actual_location_count,
             AVG(d.google_rating) as avg_rating,
             SUM(d.google_review_count) as total_review_count
      FROM brands b
      LEFT JOIN dispensaries d ON d.brand_id = b.id AND d.is_active = true
      GROUP BY b.id
      ORDER BY ${orderBy}
    `;

    const params = [];
    if (limit) {
      params.push(limit);
      query += ` LIMIT $${params.length}`;
    }
    if (offset) {
      params.push(offset);
      query += ` OFFSET $${params.length}`;
    }

    const result = await db.query(query, params);
    return result.rows;
  }

  static async findBySlug(slug) {
    const result = await db.query(
      `SELECT b.*,
              COUNT(d.id) as location_count,
              AVG(d.google_rating) as average_rating,
              SUM(d.google_review_count) as total_reviews
       FROM brands b
       LEFT JOIN dispensaries d ON d.brand_id = b.id AND d.is_active = true
       WHERE b.slug = $1
       GROUP BY b.id`,
      [slug]
    );

    return result.rows[0];
  }

  static async findById(id) {
    const result = await db.query(
      `SELECT b.*,
              COUNT(d.id) as location_count,
              AVG(d.google_rating) as average_rating,
              SUM(d.google_review_count) as total_reviews
       FROM brands b
       LEFT JOIN dispensaries d ON d.brand_id = b.id AND d.is_active = true
       WHERE b.id = $1
       GROUP BY b.id`,
      [id]
    );

    return result.rows[0];
  }

  static async getLocations(brandId, options = {}) {
    const { orderBy = 'google_rating DESC', limit, offset } = options;

    let query = `
      SELECT d.*,
             c.name as county_name,
             s.name as state_name,
             s.abbreviation as state_abbr
      FROM dispensaries d
      LEFT JOIN counties c ON c.id = d.county_id
      LEFT JOIN states s ON s.id = c.state_id
      WHERE d.brand_id = $1 AND d.is_active = true
      ORDER BY ${orderBy}
    `;

    const params = [brandId];
    if (limit) {
      params.push(limit);
      query += ` LIMIT $${params.length}`;
    }
    if (offset) {
      params.push(offset);
      query += ` OFFSET $${params.length}`;
    }

    const result = await db.query(query, params);
    return result.rows;
  }

  static async getFranchises() {
    const result = await db.query(
      `SELECT b.*,
              COUNT(d.id) as location_count,
              AVG(d.google_rating) as average_rating,
              SUM(d.google_review_count) as total_reviews
       FROM brands b
       LEFT JOIN dispensaries d ON d.brand_id = b.id AND d.is_active = true
       WHERE b.is_franchise = true
       GROUP BY b.id
       HAVING COUNT(d.id) > 0
       ORDER BY COUNT(d.id) DESC`,
      []
    );

    return result.rows;
  }

  static async updateStats(brandId) {
    await db.query(
      `UPDATE brands SET
        location_count = (SELECT COUNT(*) FROM dispensaries WHERE brand_id = $1 AND is_active = true),
        average_rating = (SELECT AVG(google_rating) FROM dispensaries WHERE brand_id = $1 AND is_active = true),
        total_reviews = (SELECT SUM(google_review_count) FROM dispensaries WHERE brand_id = $1 AND is_active = true),
        updated_at = NOW()
       WHERE id = $1`,
      [brandId]
    );
  }
}

module.exports = Brand;
