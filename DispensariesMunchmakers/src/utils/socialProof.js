const db = require('../config/database');

/**
 * Social Proof Utility
 * Provides real-time visitor and engagement metrics for dispensaries
 */
class SocialProof {
  /**
   * Get view statistics for a dispensary
   * @param {number} dispensaryId - The dispensary ID
   * @returns {object} View statistics
   */
  static async getViewStats(dispensaryId) {
    const result = await db.query(`
      SELECT
        -- Today's views (unique visitors)
        COUNT(DISTINCT CASE WHEN DATE(created_at) = CURRENT_DATE THEN ip_hash END) as views_today,

        -- Last 7 days views
        COUNT(DISTINCT CASE WHEN created_at >= CURRENT_DATE - INTERVAL '7 days' THEN ip_hash END) as views_week,

        -- Last 24 hours views
        COUNT(DISTINCT CASE WHEN created_at >= NOW() - INTERVAL '24 hours' THEN ip_hash END) as views_24h,

        -- Total views ever
        COUNT(DISTINCT ip_hash) as views_total,

        -- Recent view timestamp (for "last viewed X minutes ago")
        MAX(created_at) as last_viewed_at
      FROM page_views
      WHERE dispensary_id = $1
    `, [dispensaryId]);

    return result.rows[0];
  }

  /**
   * Check if dispensary is trending (high recent traffic)
   * @param {number} dispensaryId - The dispensary ID
   * @param {number} stateId - The state ID for comparison
   * @returns {object} Trending status
   */
  static async getTrendingStatus(dispensaryId, stateId) {
    // Get this dispensary's 24h views
    const thisDispensary = await db.query(`
      SELECT COUNT(DISTINCT ip_hash) as views_24h
      FROM page_views
      WHERE dispensary_id = $1
        AND created_at >= NOW() - INTERVAL '24 hours'
    `, [dispensaryId]);

    // Get average 24h views for dispensaries in the same state
    const stateAverage = await db.query(`
      SELECT AVG(view_count) as avg_views
      FROM (
        SELECT COUNT(DISTINCT pv.ip_hash) as view_count
        FROM page_views pv
        JOIN dispensaries d ON pv.dispensary_id = d.id
        JOIN counties c ON d.county_id = c.id
        WHERE c.state_id = $1
          AND pv.created_at >= NOW() - INTERVAL '24 hours'
        GROUP BY pv.dispensary_id
      ) as dispensary_views
    `, [stateId]);

    const views24h = parseInt(thisDispensary.rows[0]?.views_24h || 0);
    const avgViews = parseFloat(stateAverage.rows[0]?.avg_views || 0);

    // Trending if 2x above average and at least 10 views
    const isTrending = views24h >= 10 && views24h >= (avgViews * 2);

    return {
      isTrending,
      views24h,
      percentileRank: avgViews > 0 ? Math.min(100, Math.round((views24h / avgViews) * 50)) : 50
    };
  }

  /**
   * Get popularity rank in the area
   * @param {number} dispensaryId - The dispensary ID
   * @param {number} countyId - The county ID
   * @returns {object} Popularity metrics
   */
  static async getPopularityRank(dispensaryId, countyId) {
    const result = await db.query(`
      WITH dispensary_views AS (
        SELECT
          pv.dispensary_id,
          COUNT(DISTINCT pv.ip_hash) as views_30d
        FROM page_views pv
        JOIN dispensaries d ON pv.dispensary_id = d.id
        WHERE d.county_id = $2
          AND pv.created_at >= CURRENT_DATE - INTERVAL '30 days'
          AND d.is_active = true
        GROUP BY pv.dispensary_id
      )
      SELECT
        dv.views_30d,
        COUNT(*) FILTER (WHERE dv2.views_30d > dv.views_30d) + 1 as rank,
        COUNT(DISTINCT dv2.dispensary_id) as total_dispensaries
      FROM dispensary_views dv
      CROSS JOIN dispensary_views dv2
      WHERE dv.dispensary_id = $1
      GROUP BY dv.views_30d
    `, [dispensaryId, countyId]);

    const data = result.rows[0];

    if (!data) {
      return {
        rank: null,
        total: 0,
        isTopTen: false,
        isTopThird: false,
        views30d: 0
      };
    }

    const rank = parseInt(data.rank);
    const total = parseInt(data.total_dispensaries);
    const topThirdThreshold = Math.ceil(total / 3);

    return {
      rank,
      total,
      isTopTen: rank <= 10,
      isTopThird: rank <= topThirdThreshold,
      views30d: parseInt(data.views_30d || 0)
    };
  }

  /**
   * Get click engagement metrics
   * @param {number} dispensaryId - The dispensary ID
   * @returns {object} Click statistics
   */
  static async getClickStats(dispensaryId) {
    const result = await db.query(`
      SELECT
        COUNT(*) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '7 days') as clicks_week,
        COUNT(*) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '7 days' AND event_type = 'website') as website_clicks_week,
        COUNT(*) FILTER (WHERE created_at >= CURRENT_DATE - INTERVAL '7 days' AND event_type = 'phone') as phone_clicks_week
      FROM click_events
      WHERE dispensary_id = $1
    `, [dispensaryId]);

    return result.rows[0];
  }

  /**
   * Format time ago string (e.g., "5 minutes ago", "2 hours ago")
   * @param {Date} date - The date to format
   * @returns {string} Formatted time ago string
   */
  static formatTimeAgo(date) {
    if (!date) return null;

    const now = new Date();
    const diffMs = now - new Date(date);
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    return 'recently';
  }

  /**
   * Get all social proof data for a dispensary
   * @param {number} dispensaryId - The dispensary ID
   * @param {number} stateId - The state ID
   * @param {number} countyId - The county ID
   * @returns {object} Complete social proof data
   */
  static async getSocialProofData(dispensaryId, stateId, countyId) {
    const [viewStats, trendingStatus, popularityRank, clickStats] = await Promise.all([
      this.getViewStats(dispensaryId),
      this.getTrendingStatus(dispensaryId, stateId),
      this.getPopularityRank(dispensaryId, countyId),
      this.getClickStats(dispensaryId)
    ]);

    return {
      viewStats: {
        ...viewStats,
        lastViewedText: this.formatTimeAgo(viewStats.last_viewed_at)
      },
      trendingStatus,
      popularityRank,
      clickStats
    };
  }

  /**
   * Get trending dispensaries for a location
   * @param {string} locationType - 'state' or 'county'
   * @param {number} locationId - The state or county ID
   * @param {number} limit - Number of results
   * @returns {array} Trending dispensaries with view counts
   */
  static async getTrendingDispensaries(locationType, locationId, limit = 10) {
    const locationJoin = locationType === 'state'
      ? 'JOIN counties c ON d.county_id = c.id WHERE c.state_id = $1'
      : 'WHERE d.county_id = $1';

    const result = await db.query(`
      SELECT
        d.id,
        d.name,
        d.slug,
        COUNT(DISTINCT pv.ip_hash) as views_24h,
        MAX(pv.created_at) as last_viewed_at
      FROM dispensaries d
      ${locationJoin}
        AND d.is_active = true
      LEFT JOIN page_views pv ON d.id = pv.dispensary_id
        AND pv.created_at >= NOW() - INTERVAL '24 hours'
      GROUP BY d.id, d.name, d.slug
      HAVING COUNT(DISTINCT pv.ip_hash) >= 5
      ORDER BY views_24h DESC
      LIMIT $2
    `, [locationId, limit]);

    return result.rows.map(row => ({
      ...row,
      views_24h: parseInt(row.views_24h),
      lastViewedText: this.formatTimeAgo(row.last_viewed_at)
    }));
  }

  /**
   * Get popular dispensaries for a location (based on 7-day views)
   * @param {string} locationType - 'state' or 'county'
   * @param {number} locationId - The state or county ID
   * @param {number} limit - Number of results
   * @returns {array} Popular dispensaries
   */
  static async getPopularDispensaries(locationType, locationId, limit = 10) {
    const locationJoin = locationType === 'state'
      ? 'JOIN counties c ON d.county_id = c.id WHERE c.state_id = $1'
      : 'WHERE d.county_id = $1';

    const result = await db.query(`
      SELECT
        d.id,
        d.name,
        d.slug,
        COUNT(DISTINCT pv.ip_hash) as views_week
      FROM dispensaries d
      ${locationJoin}
        AND d.is_active = true
      LEFT JOIN page_views pv ON d.id = pv.dispensary_id
        AND pv.created_at >= CURRENT_DATE - INTERVAL '7 days'
      GROUP BY d.id, d.name, d.slug
      HAVING COUNT(DISTINCT pv.ip_hash) >= 10
      ORDER BY views_week DESC
      LIMIT $2
    `, [locationId, limit]);

    return result.rows.map(row => ({
      ...row,
      views_week: parseInt(row.views_week)
    }));
  }

  /**
   * Enrich dispensary list with social proof badges
   * @param {array} dispensaries - Array of dispensary objects
   * @param {string} locationType - 'state' or 'county'
   * @param {number} locationId - The location ID
   * @returns {array} Dispensaries with social proof data
   */
  static async enrichDispensariesWithBadges(dispensaries, locationType, locationId) {
    if (!dispensaries || dispensaries.length === 0) return dispensaries;

    // Get trending and popular dispensaries
    const [trending, popular] = await Promise.all([
      this.getTrendingDispensaries(locationType, locationId, 20),
      this.getPopularDispensaries(locationType, locationId, 20)
    ]);

    const trendingIds = new Set(trending.map(d => d.id));
    const popularIds = new Set(popular.map(d => d.id));
    const trendingMap = new Map(trending.map(d => [d.id, d.views_24h]));
    const popularMap = new Map(popular.map(d => [d.id, d.views_week]));

    // Enrich each dispensary
    return dispensaries.map(disp => ({
      ...disp,
      socialProof: {
        isTrending: trendingIds.has(disp.id),
        isPopular: popularIds.has(disp.id),
        views24h: trendingMap.get(disp.id) || 0,
        viewsWeek: popularMap.get(disp.id) || 0
      }
    }));
  }

  /**
   * Get overall stats for a location
   * @param {string} locationType - 'state', 'county', or 'global'
   * @param {number} locationId - The location ID (null for global)
   * @returns {object} Overall stats
   */
  static async getLocationStats(locationType = 'global', locationId = null) {
    let locationFilter = '';
    const params = [];

    if (locationType === 'state' && locationId) {
      locationFilter = 'JOIN counties c ON d.county_id = c.id WHERE c.state_id = $1';
      params.push(locationId);
    } else if (locationType === 'county' && locationId) {
      locationFilter = 'WHERE d.county_id = $1';
      params.push(locationId);
    }

    const result = await db.query(`
      SELECT
        COUNT(DISTINCT pv.ip_hash) FILTER (WHERE pv.created_at >= CURRENT_DATE) as views_today,
        COUNT(DISTINCT pv.ip_hash) FILTER (WHERE pv.created_at >= CURRENT_DATE - INTERVAL '7 days') as views_week,
        COUNT(DISTINCT pv.dispensary_id) FILTER (WHERE pv.created_at >= CURRENT_DATE) as active_today
      FROM dispensaries d
      ${locationFilter}
      LEFT JOIN page_views pv ON d.id = pv.dispensary_id
        AND d.is_active = true
    `, params);

    return {
      viewsToday: parseInt(result.rows[0]?.views_today || 0),
      viewsWeek: parseInt(result.rows[0]?.views_week || 0),
      activeToday: parseInt(result.rows[0]?.active_today || 0)
    };
  }
}

module.exports = SocialProof;
