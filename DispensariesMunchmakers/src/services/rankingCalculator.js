const db = require('../config/database');
const Ranking = require('../models/Ranking');
const Vote = require('../models/Vote');

class RankingCalculator {
  constructor() {
    // Weights for composite score (must sum to 100)
    this.weights = {
      googleRating: 25,
      reviewVolume: 15,
      externalListings: 10,
      userVotes: 20,
      pageViews: 10,
      dataCompleteness: 10,
      engagement: 10
    };
  }

  async calculateCompositeScore(dispensary) {
    const stateId = dispensary.county_state_id;

    const scores = {
      googleRating: await this.normalizeGoogleRating(dispensary.google_rating),
      reviewVolume: await this.normalizeReviewVolume(dispensary.google_review_count, dispensary.county_id, stateId),
      externalListings: this.calculateExternalListingsScore(dispensary.external_listings),
      userVotes: await this.calculateVoteScore(dispensary.id, dispensary.county_id, stateId),
      pageViews: await this.calculatePageViewScore(dispensary.id, dispensary.county_id, stateId),
      dataCompleteness: dispensary.data_completeness_score || 0,
      engagement: await this.calculateEngagementScore(dispensary.id)
    };

    // Calculate weighted composite score
    let composite = 0;
    for (const [key, weight] of Object.entries(this.weights)) {
      composite += (scores[key] * weight) / 100;
    }

    return Math.round(composite * 100) / 100; // Round to 2 decimals
  }

  normalizeGoogleRating(rating) {
    if (!rating || rating === 0) return 0;
    // Normalize 1-5 scale to 0-100
    return ((rating - 1) / 4) * 100;
  }

  async normalizeReviewVolume(reviewCount, countyId, stateId) {
    if (!reviewCount || reviewCount === 0) return 0;

    // Get max reviews in county (or state if no county)
    let query, params;
    if (countyId) {
      query = `SELECT MAX(google_review_count) as max_reviews
               FROM dispensaries
               WHERE county_id = $1 AND is_active = true`;
      params = [countyId];
    } else if (stateId) {
      query = `SELECT MAX(google_review_count) as max_reviews
               FROM dispensaries
               WHERE state_id = $1 AND is_active = true`;
      params = [stateId];
    } else {
      return 0;
    }

    const result = await db.query(query, params);
    const maxReviews = result.rows[0].max_reviews || 1;

    // Use logarithmic scale for review volume to prevent outliers from dominating
    const normalizedScore = (Math.log(reviewCount + 1) / Math.log(maxReviews + 1)) * 100;

    return Math.min(normalizedScore, 100);
  }

  calculateExternalListingsScore(externalListings) {
    if (!externalListings || typeof externalListings !== 'object') return 0;

    let score = 0;

    // 50 points for Leafly
    if (externalListings.leafly) score += 50;

    // 40 points for Weedmaps
    if (externalListings.weedmaps) score += 40;

    // 10 points for other listings (max 2)
    if (externalListings.other && Array.isArray(externalListings.other)) {
      score += Math.min(externalListings.other.length * 5, 10);
    }

    return Math.min(score, 100);
  }

  async calculateVoteScore(dispensaryId, countyId, stateId) {
    try {
      // Get vote counts for this dispensary
      const voteCounts = await Vote.getVoteCounts(dispensaryId);
      const netVotes = parseInt(voteCounts.net_votes) || 0;

      if (netVotes <= 0) return 0;

      // Get max net votes in county or state
      let query, params;
      if (countyId) {
        query = `SELECT d.id, SUM(v.vote_type) as net_votes
                 FROM dispensaries d
                 LEFT JOIN votes v ON d.id = v.dispensary_id
                 WHERE d.county_id = $1 AND d.is_active = true
                 GROUP BY d.id
                 ORDER BY net_votes DESC
                 LIMIT 1`;
        params = [countyId];
      } else if (stateId) {
        query = `SELECT d.id, SUM(v.vote_type) as net_votes
                 FROM dispensaries d
                 LEFT JOIN votes v ON d.id = v.dispensary_id
                 WHERE d.state_id = $1 AND d.is_active = true
                 GROUP BY d.id
                 ORDER BY net_votes DESC
                 LIMIT 1`;
        params = [stateId];
      } else {
        return 0;
      }

      const result = await db.query(query, params);
      const maxNetVotes = result.rows[0]?.net_votes || 1;

      // Normalize to 0-100 scale
      return Math.min((netVotes / maxNetVotes) * 100, 100);
    } catch (error) {
      console.error('Error calculating vote score:', error);
      return 0;
    }
  }

  async calculatePageViewScore(dispensaryId, countyId, stateId) {
    try {
      // Get page views for last 30 days
      const result = await db.query(
        `SELECT COUNT(*) as view_count
         FROM page_views
         WHERE dispensary_id = $1
           AND created_at >= NOW() - INTERVAL '30 days'`,
        [dispensaryId]
      );

      const viewCount = parseInt(result.rows[0].view_count) || 0;

      if (viewCount === 0) return 0;

      // Get max views in county or state
      let query, params;
      if (countyId) {
        query = `SELECT d.id, COUNT(pv.id) as view_count
                 FROM dispensaries d
                 LEFT JOIN page_views pv ON d.id = pv.dispensary_id
                   AND pv.created_at >= NOW() - INTERVAL '30 days'
                 WHERE d.county_id = $1 AND d.is_active = true
                 GROUP BY d.id
                 ORDER BY view_count DESC
                 LIMIT 1`;
        params = [countyId];
      } else if (stateId) {
        query = `SELECT d.id, COUNT(pv.id) as view_count
                 FROM dispensaries d
                 LEFT JOIN page_views pv ON d.id = pv.dispensary_id
                   AND pv.created_at >= NOW() - INTERVAL '30 days'
                 WHERE d.state_id = $1 AND d.is_active = true
                 GROUP BY d.id
                 ORDER BY view_count DESC
                 LIMIT 1`;
        params = [stateId];
      } else {
        return 0;
      }

      const maxResult = await db.query(query, params);

      const maxViews = maxResult.rows[0]?.view_count || 1;

      // Normalize to 0-100 scale
      return Math.min((viewCount / maxViews) * 100, 100);
    } catch (error) {
      console.error('Error calculating page view score:', error);
      return 0;
    }
  }

  async calculateEngagementScore(dispensaryId) {
    try {
      // Get engagement events (clicks to website, phone, directions) for last 30 days
      const result = await db.query(
        `SELECT COUNT(*) as click_count
         FROM click_events
         WHERE dispensary_id = $1
           AND created_at >= NOW() - INTERVAL '30 days'`,
        [dispensaryId]
      );

      const clickCount = parseInt(result.rows[0].click_count) || 0;

      // Simple scoring: 10 clicks = 100 points (linear scale)
      return Math.min((clickCount / 10) * 100, 100);
    } catch (error) {
      console.error('Error calculating engagement score:', error);
      return 0;
    }
  }

  async calculateAllRankings() {
    console.log('Starting ranking calculation...');

    try {
      // Get all active dispensaries (LEFT JOIN to include those without county)
      const result = await db.query(
        `SELECT d.*, c.state_id as county_state_id
         FROM dispensaries d
         LEFT JOIN counties c ON d.county_id = c.id
         WHERE d.is_active = true`
      );

      const dispensaries = result.rows;
      console.log(`Calculating rankings for ${dispensaries.length} dispensaries...`);

      let processed = 0;

      for (const dispensary of dispensaries) {
        // Calculate composite score (use state_id for normalization if no county)
        const compositeScore = await this.calculateCompositeScore(dispensary);

        // Get state_id from county (all counties have state_id)
        const stateId = dispensary.county_state_id;

        // Upsert county ranking ONLY if county_id exists
        if (dispensary.county_id) {
          await Ranking.upsert(dispensary.id, 'county', dispensary.county_id, compositeScore);
        }

        // Upsert state ranking if we have state_id
        if (stateId) {
          await Ranking.upsert(dispensary.id, 'state', stateId, compositeScore);
        }

        processed++;

        if (processed % 10 === 0) {
          console.log(`  Processed ${processed}/${dispensaries.length} dispensaries...`);
        }
      }

      // Update rank positions for all counties
      const counties = await db.query('SELECT id FROM counties');
      for (const county of counties.rows) {
        await Ranking.updateRanks('county', county.id);
      }

      // Update rank positions for all states
      const states = await db.query('SELECT id FROM states');
      for (const state of states.rows) {
        await Ranking.updateRanks('state', state.id);
      }

      console.log('✓ Ranking calculation completed successfully!');

      return { processed, success: true };
    } catch (error) {
      console.error('Error calculating rankings:', error);
      throw error;
    }
  }
}

module.exports = new RankingCalculator();
