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
}

module.exports = Vote;
