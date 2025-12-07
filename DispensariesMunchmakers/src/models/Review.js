const db = require('../config/database');
const crypto = require('crypto');

class Review {
  /**
   * Create IP hash for privacy
   */
  static hashIP(ip) {
    return crypto.createHash('sha256').update(ip + process.env.IP_SALT || 'default-salt').digest('hex');
  }

  /**
   * Create a new review
   */
  static async create(data) {
    const { dispensaryId, authorName, authorEmail, rating, reviewText, ipAddress } = data;

    const ipHash = this.hashIP(ipAddress);

    const result = await db.query(
      `INSERT INTO reviews
       (dispensary_id, author_name, author_email, rating, review_text, ip_hash, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, NOW(), NOW())
       RETURNING *`,
      [dispensaryId, authorName, authorEmail || null, rating, reviewText, ipHash]
    );

    // Update dispensary aggregates
    await this.updateDispensaryAggregates(dispensaryId);

    return result.rows[0];
  }

  /**
   * Find reviews by dispensary
   */
  static async findByDispensary(dispensaryId, options = {}) {
    const { limit = 20, offset = 0, sortBy = 'recent' } = options;

    let orderBy = 'created_at DESC';
    if (sortBy === 'highest') {
      orderBy = 'rating DESC, created_at DESC';
    } else if (sortBy === 'helpful') {
      orderBy = 'helpful_count DESC, created_at DESC';
    }

    const result = await db.query(
      `SELECT id, author_name, rating, review_text, helpful_count, not_helpful_count,
              created_at, is_verified
       FROM reviews
       WHERE dispensary_id = $1 AND is_approved = true
       ORDER BY ${orderBy}
       LIMIT $2 OFFSET $3`,
      [dispensaryId, limit, offset]
    );

    return result.rows;
  }

  /**
   * Find a single review by ID
   */
  static async findById(id) {
    const result = await db.query(
      'SELECT * FROM reviews WHERE id = $1',
      [id]
    );
    return result.rows[0];
  }

  /**
   * Check if user can review (one review per dispensary per IP)
   */
  static async canReview(dispensaryId, ipAddress) {
    const ipHash = this.hashIP(ipAddress);

    const result = await db.query(
      'SELECT COUNT(*) FROM reviews WHERE dispensary_id = $1 AND ip_hash = $2',
      [dispensaryId, ipHash]
    );

    return parseInt(result.rows[0].count) === 0;
  }

  /**
   * Mark review as helpful or not helpful
   */
  static async markHelpful(reviewId, ipAddress, helpful) {
    const ipHash = this.hashIP(ipAddress);

    try {
      // Insert vote (will fail if already voted due to UNIQUE constraint)
      await db.query(
        `INSERT INTO review_helpfulness (review_id, ip_hash, helpful)
         VALUES ($1, $2, $3)`,
        [reviewId, ipHash, helpful]
      );

      // Update counts
      const column = helpful ? 'helpful_count' : 'not_helpful_count';
      await db.query(
        `UPDATE reviews SET ${column} = ${column} + 1 WHERE id = $1`,
        [reviewId]
      );

      return { success: true };
    } catch (error) {
      if (error.code === '23505') { // Unique violation - already voted
        return { success: false, error: 'Already voted on this review' };
      }
      throw error;
    }
  }

  /**
   * Get review statistics for a dispensary
   */
  static async getStats(dispensaryId) {
    const result = await db.query(
      `SELECT
         rating,
         COUNT(*) as count
       FROM reviews
       WHERE dispensary_id = $1 AND is_approved = true
       GROUP BY rating
       ORDER BY rating DESC`,
      [dispensaryId]
    );

    const stats = {
      5: 0, 4: 0, 3: 0, 2: 0, 1: 0,
      total: 0,
      average: 0
    };

    let totalRating = 0;
    result.rows.forEach(row => {
      const count = parseInt(row.count);
      stats[row.rating] = count;
      stats.total += count;
      totalRating += row.rating * count;
    });

    if (stats.total > 0) {
      stats.average = (totalRating / stats.total).toFixed(1);
    }

    return stats;
  }

  /**
   * Get total review count for dispensary
   */
  static async getCount(dispensaryId) {
    const result = await db.query(
      'SELECT COUNT(*) FROM reviews WHERE dispensary_id = $1 AND is_approved = true',
      [dispensaryId]
    );
    return parseInt(result.rows[0].count);
  }

  /**
   * Update dispensary aggregate review data
   */
  static async updateDispensaryAggregates(dispensaryId) {
    const stats = await this.getStats(dispensaryId);

    await db.query(
      `UPDATE dispensaries
       SET user_review_count = $1, user_average_rating = $2
       WHERE id = $3`,
      [stats.total, parseFloat(stats.average), dispensaryId]
    );
  }

  /**
   * Get all reviews for admin moderation
   */
  static async getAllForModeration(options = {}) {
    const { limit = 50, offset = 0 } = options;

    const result = await db.query(
      `SELECT r.*, d.name as dispensary_name
       FROM reviews r
       JOIN dispensaries d ON r.dispensary_id = d.id
       ORDER BY r.created_at DESC
       LIMIT $1 OFFSET $2`,
      [limit, offset]
    );

    return result.rows;
  }

  /**
   * Approve a review
   */
  static async approve(id) {
    await db.query(
      'UPDATE reviews SET is_approved = true WHERE id = $1',
      [id]
    );

    const review = await this.findById(id);
    await this.updateDispensaryAggregates(review.dispensary_id);
  }

  /**
   * Reject/delete a review
   */
  static async reject(id) {
    const review = await this.findById(id);
    const dispensaryId = review.dispensary_id;

    await db.query('DELETE FROM reviews WHERE id = $1', [id]);

    await this.updateDispensaryAggregates(dispensaryId);
  }

  /**
   * Check for spam patterns
   */
  static isSpam(reviewText) {
    const lowerText = reviewText.toLowerCase();

    // Check for excessive URLs
    const urlCount = (reviewText.match(/http/gi) || []).length;
    if (urlCount > 2) return true;

    // Check for excessive caps
    const capsRatio = (reviewText.match(/[A-Z]/g) || []).length / reviewText.length;
    if (capsRatio > 0.5 && reviewText.length > 20) return true;

    // Check for common spam phrases
    const spamPhrases = ['click here', 'buy now', 'limited time', 'act now', 'viagra', 'cialis'];
    if (spamPhrases.some(phrase => lowerText.includes(phrase))) return true;

    return false;
  }
}

module.exports = Review;
