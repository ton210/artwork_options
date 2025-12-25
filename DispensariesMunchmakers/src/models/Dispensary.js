const db = require('../config/database');
const slugify = require('slugify');

class Dispensary {
  static async create(data) {
    // Use a transaction to prevent race conditions
    const client = await db.pool.connect();

    try {
      await client.query('BEGIN');

      // Check if this dispensary already exists by google_place_id
      const existing = await client.query(
        'SELECT id, slug FROM dispensaries WHERE google_place_id = $1',
        [data.google_place_id]
      );

      let slug;

      if (existing.rows.length > 0) {
        // Dispensary exists, keep the existing slug to avoid duplicates
        slug = existing.rows[0].slug;
      } else {
        // New dispensary, generate a unique slug
        let baseSlug = slugify(data.name, { lower: true, strict: true }) + '-' + data.city?.toLowerCase().replace(/\s+/g, '-');
        slug = baseSlug;

        // Check for duplicate slugs and append number if needed
        let counter = 2;
        while (true) {
          const existingSlug = await client.query(
            'SELECT id FROM dispensaries WHERE slug = $1',
            [slug]
          );

          if (existingSlug.rows.length === 0) {
            break; // Slug is unique
          }

          // Slug exists, try with counter
          slug = `${baseSlug}-${counter}`;
          counter++;
        }
      }

      const query = `
        INSERT INTO dispensaries (
          google_place_id, name, slug, address_street, city, county_id,
          zip, lat, lng, phone, website, logo_url, photos, hours,
          google_rating, google_review_count, external_listings,
          license_number, data_completeness_score
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19)
        ON CONFLICT (google_place_id) DO UPDATE SET
          name = EXCLUDED.name,
          slug = dispensaries.slug,
          address_street = EXCLUDED.address_street,
          city = EXCLUDED.city,
          county_id = EXCLUDED.county_id,
          zip = EXCLUDED.zip,
          lat = EXCLUDED.lat,
          lng = EXCLUDED.lng,
          phone = EXCLUDED.phone,
          website = EXCLUDED.website,
          logo_url = EXCLUDED.logo_url,
          photos = EXCLUDED.photos,
          hours = EXCLUDED.hours,
          google_rating = EXCLUDED.google_rating,
          google_review_count = EXCLUDED.google_review_count,
          external_listings = EXCLUDED.external_listings,
          data_completeness_score = EXCLUDED.data_completeness_score,
          updated_at = NOW()
        RETURNING *
      `;

      const values = [
        data.google_place_id,
        data.name,
        slug,
        data.address_street,
        data.city,
        data.county_id,
        data.zip,
        data.lat,
        data.lng,
        data.phone,
        data.website,
        data.logo_url,
        JSON.stringify(data.photos || []),
        JSON.stringify(data.hours || {}),
        data.google_rating,
        data.google_review_count || 0,
        JSON.stringify(data.external_listings || {}),
        data.license_number,
        data.data_completeness_score || 0
      ];

      const result = await client.query(query, values);
      await client.query('COMMIT');

      return result.rows[0];
    } catch (error) {
      await client.query('ROLLBACK');

      // If it's a unique constraint violation on slug, retry with a different approach
      if (error.code === '23505' && error.constraint === 'dispensaries_slug_key') {
        console.error('Slug constraint violation, retrying with timestamp suffix...');

        // Release this client and retry with timestamp-based slug
        client.release();
        return this.createWithTimestampSlug(data);
      }

      throw error;
    } finally {
      client.release();
    }
  }

  // Fallback method for edge cases where slug conflicts still occur
  static async createWithTimestampSlug(data) {
    let baseSlug = slugify(data.name, { lower: true, strict: true }) + '-' + data.city?.toLowerCase().replace(/\s+/g, '-');
    let slug = `${baseSlug}-${Date.now()}`;

    const query = `
      INSERT INTO dispensaries (
        google_place_id, name, slug, address_street, city, county_id,
        zip, lat, lng, phone, website, logo_url, photos, hours,
        google_rating, google_review_count, external_listings,
        license_number, data_completeness_score
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19)
      ON CONFLICT (google_place_id) DO UPDATE SET
        name = EXCLUDED.name,
        slug = dispensaries.slug,
        address_street = EXCLUDED.address_street,
        city = EXCLUDED.city,
        county_id = EXCLUDED.county_id,
        zip = EXCLUDED.zip,
        lat = EXCLUDED.lat,
        lng = EXCLUDED.lng,
        phone = EXCLUDED.phone,
        website = EXCLUDED.website,
        logo_url = EXCLUDED.logo_url,
        photos = EXCLUDED.photos,
        hours = EXCLUDED.hours,
        google_rating = EXCLUDED.google_rating,
        google_review_count = EXCLUDED.google_review_count,
        external_listings = EXCLUDED.external_listings,
        data_completeness_score = EXCLUDED.data_completeness_score,
        updated_at = NOW()
      RETURNING *
    `;

    const values = [
      data.google_place_id,
      data.name,
      slug,
      data.address_street,
      data.city,
      data.county_id,
      data.zip,
      data.lat,
      data.lng,
      data.phone,
      data.website,
      data.logo_url,
      JSON.stringify(data.photos || []),
      JSON.stringify(data.hours || {}),
      data.google_rating,
      data.google_review_count || 0,
      JSON.stringify(data.external_listings || {}),
      data.license_number,
      data.data_completeness_score || 0
    ];

    const result = await db.query(query, values);
    return result.rows[0];
  }

  static async findById(id) {
    const result = await db.query(
      'SELECT * FROM dispensaries WHERE id = $1',
      [id]
    );
    return result.rows[0];
  }

  static async findBySlug(slug) {
    const result = await db.query(
      `SELECT d.*, c.name as county_name, c.slug as county_slug,
              s.name as state_name, s.slug as state_slug, s.abbreviation as state_abbr
       FROM dispensaries d
       LEFT JOIN counties c ON d.county_id = c.id
       LEFT JOIN states s ON c.state_id = s.id
       WHERE d.slug = $1 AND d.is_active = true`,
      [slug]
    );
    return result.rows[0];
  }

  static async findByCounty(countyId, limit = 100, offset = 0) {
    const result = await db.query(
      `SELECT d.*, r.composite_score, r.rank, r.previous_rank
       FROM dispensaries d
       LEFT JOIN rankings r ON d.id = r.dispensary_id
         AND r.location_type = 'county'
         AND r.location_id = $1
       WHERE d.county_id = $1 AND d.is_active = true
       ORDER BY r.rank ASC NULLS LAST, d.google_rating DESC
       LIMIT $2 OFFSET $3`,
      [countyId, limit, offset]
    );
    return result.rows;
  }

  static async findByState(stateId, limit = 100, offset = 0) {
    const result = await db.query(
      `SELECT d.*, c.name as county_name, r.composite_score, r.rank, r.previous_rank
       FROM dispensaries d
       JOIN counties c ON d.county_id = c.id
       LEFT JOIN rankings r ON d.id = r.dispensary_id
         AND r.location_type = 'state'
         AND r.location_id = $1
       WHERE c.state_id = $1 AND d.is_active = true
       ORDER BY r.rank ASC NULLS LAST, d.google_rating DESC
       LIMIT $2 OFFSET $3`,
      [stateId, limit, offset]
    );
    return result.rows;
  }

  static async update(id, data) {
    const fields = [];
    const values = [];
    let paramCount = 1;

    // JSON fields that need to be stringified
    const jsonFields = ['photos', 'hours', 'external_listings'];

    Object.keys(data).forEach(key => {
      if (data[key] !== undefined) {
        fields.push(`${key} = $${paramCount}`);

        // Stringify JSON fields
        if (jsonFields.includes(key) && typeof data[key] === 'object') {
          values.push(JSON.stringify(data[key]));
        } else {
          values.push(data[key]);
        }

        paramCount++;
      }
    });

    if (fields.length === 0) return null;

    values.push(id);
    const query = `
      UPDATE dispensaries
      SET ${fields.join(', ')}, updated_at = NOW()
      WHERE id = $${paramCount}
      RETURNING *
    `;

    const result = await db.query(query, values);
    return result.rows[0];
  }

  static async delete(id) {
    await db.query('UPDATE dispensaries SET is_active = false WHERE id = $1', [id]);
  }

  static async getStats() {
    const result = await db.query(`
      SELECT
        COUNT(*) as total_dispensaries,
        COUNT(CASE WHEN is_verified THEN 1 END) as verified_dispensaries,
        AVG(google_rating) as avg_rating,
        SUM(google_review_count) as total_reviews
      FROM dispensaries
      WHERE is_active = true
    `);
    return result.rows[0];
  }

  static calculateCompletenessScore(dispensary) {
    let score = 0;

    if (dispensary.name) score += 10;
    if (dispensary.address_street) score += 10;
    if (dispensary.phone) score += 10;
    if (dispensary.website) score += 15;
    if (dispensary.logo_url) score += 10;
    if (dispensary.photos && dispensary.photos.length > 0) score += 15;
    if (dispensary.hours) score += 10;
    if (dispensary.google_rating) score += 10;
    if (dispensary.google_review_count > 0) score += 10;

    return score;
  }

  // Get optimized data for map display (minimal fields for performance)
  static async getMapData(countyId) {
    const result = await db.query(
      `SELECT d.id, d.name, d.slug, d.lat, d.lng, d.google_rating,
              d.google_review_count, d.address_street, d.city, d.zip, d.logo_url,
              s.abbreviation as state_abbr
       FROM dispensaries d
       LEFT JOIN counties c ON d.county_id = c.id
       LEFT JOIN states s ON c.state_id = s.id
       WHERE d.county_id = $1 AND d.is_active = true AND d.lat IS NOT NULL
       ORDER BY d.google_rating DESC`,
      [countyId]
    );
    return result.rows;
  }

  // Find nearby dispensaries within radius (miles)
  static async findNearby(lat, lng, radiusMiles = 25, limit = 50) {
    const result = await db.query(
      `SELECT d.*, c.name as county_name, s.name as state_name, s.abbreviation as state_abbr,
              (3959 * acos(
                cos(radians($1)) * cos(radians(d.lat)) *
                cos(radians(d.lng) - radians($2)) +
                sin(radians($1)) * sin(radians(d.lat))
              )) AS distance
       FROM dispensaries d
       LEFT JOIN counties c ON d.county_id = c.id
       LEFT JOIN states s ON c.state_id = s.id
       WHERE d.is_active = true AND d.lat IS NOT NULL
       HAVING distance < $3
       ORDER BY distance ASC
       LIMIT $4`,
      [lat, lng, radiusMiles, limit]
    );
    return result.rows;
  }
}

module.exports = Dispensary;
