const db = require('../config/database');
const crypto = require('crypto');

class Vote {
  static hashIP(ip) {
    return crypto.createHash('sha256').update(ip).digest('hex');
  }

  static async create(dispensaryId, voteType, ip, sessionId, isVerified = false) {
    const ipHash = this.hashIP(ip);

    try {
      const result = await db.query(
        `INSERT INTO votes (dispensary_id, vote_type, ip_hash, session_id, is_verified_vote)
         VALUES ($1, $2, $3, $4, $5)
         RETURNING *`,
        [dispensaryId, voteType, ipHash, sessionId, isVerified]
      );
      return result.rows[0];
    } catch (error) {
      if (error.code === '23505') {
        throw new Error('You have already voted for this dispensary today');
      }
      throw error;
    }
  }

  static async canVote(dispensaryId, ip) {
    const ipHash = this.hashIP(ip);

    const result = await db.query(
      `SELECT COUNT(*) as count
       FROM votes
       WHERE dispensary_id = $1
         AND ip_hash = $2
         AND DATE(created_at) = CURRENT_DATE`,
      [dispensaryId, ipHash]
    );

    return parseInt(result.rows[0].count) === 0;
  }

  static async getVoteCounts(dispensaryId) {
    const result = await db.query(
      `SELECT
         COUNT(CASE WHEN vote_type = 1 THEN 1 END) as upvotes,
         COUNT(CASE WHEN vote_type = -1 THEN 1 END) as downvotes,
         SUM(vote_type) as net_votes
       FROM votes
       WHERE dispensary_id = $1`,
      [dispensaryId]
    );

    return result.rows[0];
  }

  static async getRecentVotes(dispensaryId, days = 7) {
    const result = await db.query(
      `SELECT COUNT(*) as count
       FROM votes
       WHERE dispensary_id = $1
         AND created_at >= NOW() - INTERVAL '${days} days'`,
      [dispensaryId]
    );

    return parseInt(result.rows[0].count);
  }

  static async getTopVoted(limit = 10, locationType = null, locationId = null) {
    let query = `
      SELECT d.*, COUNT(v.id) as total_votes, SUM(v.vote_type) as net_votes
      FROM dispensaries d
      LEFT JOIN votes v ON d.id = v.dispensary_id
    `;

    const params = [];
    const conditions = ['d.is_active = true'];

    if (locationType === 'county' && locationId) {
      conditions.push('d.county_id = $1');
      params.push(locationId);
    } else if (locationType === 'state' && locationId) {
      query += ' JOIN counties c ON d.county_id = c.id';
      conditions.push('c.state_id = $1');
      params.push(locationId);
    }

    query += ` WHERE ${conditions.join(' AND ')}
      GROUP BY d.id
      ORDER BY net_votes DESC, total_votes DESC
      LIMIT ${limit}`;

    const result = await db.query(query, params);
    return result.rows;
  }

  static async delete(id) {
    await db.query('DELETE FROM votes WHERE id = $1', [id]);
  }

  /**
   * Get dispensaries that received votes in the last 24 hours
   * @param {number} limit - Number of results (default 5)
   * @returns {array} Dispensaries with recent vote activity
   */
  static async getDispensariesWithRecentVotes(limit = 5) {
    const result = await db.query(`
      SELECT
        d.id,
        d.name,
        d.slug,
        d.city,
        d.logo_url,
        s.abbreviation as state_abbr,
        s.slug as state_slug,
        COUNT(v.id) as total_recent_votes,
        COUNT(CASE WHEN v.vote_type = 1 THEN 1 END) as upvotes,
        COUNT(CASE WHEN v.vote_type = -1 THEN 1 END) as downvotes,
        SUM(v.vote_type) as net_votes
      FROM votes v
      JOIN dispensaries d ON v.dispensary_id = d.id
      JOIN counties c ON d.county_id = c.id
      JOIN states s ON c.state_id = s.id
      WHERE v.created_at >= NOW() - INTERVAL '24 hours'
        AND d.is_active = true
      GROUP BY d.id, d.name, d.slug, d.city, d.logo_url, s.abbreviation, s.slug
      ORDER BY total_recent_votes DESC, net_votes DESC
      LIMIT $1
    `, [limit]);

    return result.rows.map(row => ({
      ...row,
      total_recent_votes: parseInt(row.total_recent_votes),
      upvotes: parseInt(row.upvotes),
      downvotes: parseInt(row.downvotes),
      net_votes: parseInt(row.net_votes)
    }));
  }
}

module.exports = Vote;
