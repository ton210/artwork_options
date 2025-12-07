const db = require('../config/database');
const crypto = require('crypto');

class BusinessClaim {
  /**
   * Create a new business claim
   */
  static async create(data) {
    const { dispensaryId, userId, contactName, contactEmail, contactPhone } = data;

    const verificationCode = crypto.randomBytes(6).toString('hex').toUpperCase();

    const result = await db.query(
      `INSERT INTO business_claims
       (dispensary_id, user_id, contact_name, contact_email, contact_phone,
        verification_code, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, NOW(), NOW())
       RETURNING *`,
      [dispensaryId, userId, contactName, contactEmail.toLowerCase(), contactPhone || null, verificationCode]
    );

    return result.rows[0];
  }

  /**
   * Find claim by ID
   */
  static async findById(id) {
    const result = await db.query(
      `SELECT bc.*, d.name as dispensary_name, d.website, d.slug as dispensary_slug
       FROM business_claims bc
       JOIN dispensaries d ON bc.dispensary_id = d.id
       WHERE bc.id = $1`,
      [id]
    );
    return result.rows[0];
  }

  /**
   * Find claim by verification code
   */
  static async findByVerificationCode(code) {
    const result = await db.query(
      'SELECT * FROM business_claims WHERE verification_code = $1',
      [code.toUpperCase()]
    );
    return result.rows[0];
  }

  /**
   * Check if dispensary already has a claim
   */
  static async hasActiveClaim(dispensaryId) {
    const result = await db.query(
      `SELECT COUNT(*) FROM business_claims
       WHERE dispensary_id = $1 AND (is_approved = true OR is_verified = true)`,
      [dispensaryId]
    );
    return parseInt(result.rows[0].count) > 0;
  }

  /**
   * Check if dispensary is already claimed
   */
  static async isClaimed(dispensaryId) {
    const result = await db.query(
      'SELECT is_claimed FROM dispensaries WHERE id = $1',
      [dispensaryId]
    );
    return result.rows[0]?.is_claimed || false;
  }

  /**
   * Auto-verify claim if email domain matches website
   */
  static async autoVerifyByDomain(claimId) {
    const claim = await this.findById(claimId);
    if (!claim) return false;

    const emailDomain = claim.contact_email.split('@')[1].toLowerCase();

    if (!claim.website) return false;

    const websiteDomain = claim.website
      .replace(/^https?:\/\//, '')
      .replace(/^www\./, '')
      .split('/')[0]
      .toLowerCase();

    if (emailDomain === websiteDomain) {
      await this.verify(claimId);
      await this.approve(claimId);
      return true;
    }

    return false;
  }

  /**
   * Verify claim manually
   */
  static async verify(claimId) {
    await db.query(
      `UPDATE business_claims
       SET is_verified = true, verified_at = NOW(), updated_at = NOW()
       WHERE id = $1`,
      [claimId]
    );
  }

  /**
   * Approve claim and mark dispensary as claimed
   */
  static async approve(claimId) {
    const claim = await this.findById(claimId);
    if (!claim) return;

    // Start transaction
    const client = await db.pool.connect();

    try {
      await client.query('BEGIN');

      // Update claim
      await client.query(
        `UPDATE business_claims
         SET is_approved = true, approved_at = NOW(), updated_at = NOW()
         WHERE id = $1`,
        [claimId]
      );

      // Update dispensary
      await client.query(
        `UPDATE dispensaries
         SET is_claimed = true, claimed_by_user_id = $1, claimed_at = NOW()
         WHERE id = $2`,
        [claim.user_id, claim.dispensary_id]
      );

      await client.query('COMMIT');
    } catch (error) {
      await client.query('ROLLBACK');
      throw error;
    } finally {
      client.release();
    }
  }

  /**
   * Reject claim
   */
  static async reject(claimId, adminNotes) {
    await db.query(
      `UPDATE business_claims
       SET is_approved = false, admin_notes = $1, updated_at = NOW()
       WHERE id = $2`,
      [adminNotes, claimId]
    );
  }

  /**
   * Get all pending claims for admin
   */
  static async getPending(options = {}) {
    const { limit = 50, offset = 0 } = options;

    const result = await db.query(
      `SELECT bc.*, d.name as dispensary_name, d.slug as dispensary_slug, d.website
       FROM business_claims bc
       JOIN dispensaries d ON bc.dispensary_id = d.id
       WHERE bc.is_approved = false
       ORDER BY bc.created_at DESC
       LIMIT $1 OFFSET $2`,
      [limit, offset]
    );

    return result.rows;
  }

  /**
   * Get user's claims
   */
  static async getByUser(userId) {
    const result = await db.query(
      `SELECT bc.*, d.name as dispensary_name, d.slug as dispensary_slug
       FROM business_claims bc
       JOIN dispensaries d ON bc.dispensary_id = d.id
       WHERE bc.user_id = $1
       ORDER BY bc.created_at DESC`,
      [userId]
    );

    return result.rows;
  }
}

module.exports = BusinessClaim;
