const express = require('express');
const router = express.Router();
const { State } = require('../models/State');
const SocialProof = require('../utils/socialProof');
const db = require('../config/database');

// Homepage
router.get('/', async (req, res) => {
  try {
    // Get all states with dispensary counts
    const allStates = await db.query(`
      SELECT s.*, COUNT(DISTINCT d.id) as dispensary_count
      FROM states s
      LEFT JOIN counties c ON s.id = c.state_id
      LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
      GROUP BY s.id
      ORDER BY s.name ASC
    `);

    // Separate US states from Canadian provinces
    // Canadian provinces have abbreviations: AB, BC, MB, NB, NL, NT, NS, NU, ON, PE, QC, SK, YT
    const canadianAbbrs = ['AB', 'BC', 'MB', 'NB', 'NL', 'NT', 'NS', 'NU', 'ON', 'PE', 'QC', 'SK', 'YT'];

    const usStates = allStates.rows.filter(s => !canadianAbbrs.includes(s.abbreviation));
    const canadianProvinces = allStates.rows.filter(s => canadianAbbrs.includes(s.abbreviation));

    // Get overall stats
    const stats = await db.query(`
      SELECT
        COUNT(DISTINCT d.id) as total_dispensaries,
        COUNT(DISTINCT c.id) as total_counties,
        COUNT(DISTINCT s.id) as total_states,
        AVG(d.google_rating) as avg_rating
      FROM states s
      LEFT JOIN counties c ON s.id = c.state_id
      LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
    `);

    // Get global social proof stats
    const globalStats = await SocialProof.getLocationStats('global', null);

    // Get most viewed dispensaries today (top 5)
    const trendingToday = await db.query(`
      SELECT
        d.id,
        d.name,
        d.slug,
        d.city,
        s.abbreviation as state_abbr,
        s.slug as state_slug,
        COUNT(DISTINCT pv.ip_hash) as views_today
      FROM dispensaries d
      JOIN counties c ON d.county_id = c.id
      JOIN states s ON c.state_id = s.id
      LEFT JOIN page_views pv ON d.id = pv.dispensary_id
        AND pv.created_at >= CURRENT_DATE
      WHERE d.is_active = true
      GROUP BY d.id, d.name, d.slug, d.city, s.abbreviation, s.slug
      HAVING COUNT(DISTINCT pv.ip_hash) >= 5
      ORDER BY views_today DESC
      LIMIT 5
    `);

    // Get US-specific stats
    const usStats = await db.query(`
      SELECT COUNT(DISTINCT d.id) as count
      FROM states s
      LEFT JOIN counties c ON s.id = c.state_id
      LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
      WHERE s.abbreviation NOT IN ('AB', 'BC', 'MB', 'NB', 'NL', 'NT', 'NS', 'NU', 'ON', 'PE', 'QC', 'SK', 'YT')
    `);

    // Get Canada-specific stats
    const canadaStats = await db.query(`
      SELECT COUNT(DISTINCT d.id) as count
      FROM states s
      LEFT JOIN counties c ON s.id = c.state_id
      LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
      WHERE s.abbreviation IN ('AB', 'BC', 'MB', 'NB', 'NL', 'NT', 'NS', 'NU', 'ON', 'PE', 'QC', 'SK', 'YT')
    `);

    res.render('home', {
      title: 'Top Dispensaries 2026 - Find the Best Cannabis Dispensaries in US & Canada',
      usStates,
      canadianProvinces,
      stats: stats.rows[0],
      usCount: usStats.rows[0].count,
      canadaCount: canadaStats.rows[0].count,
      globalStats,
      trendingToday: trendingToday.rows,
      meta: {
        description: 'Discover the top-rated cannabis dispensaries across the United States and Canada. User-voted rankings based on reviews, ratings, and community feedback.',
        keywords: 'cannabis dispensary, marijuana dispensary, weed dispensary, top dispensaries, dispensary rankings, Canada dispensaries'
      }
    });
  } catch (error) {
    console.error('Error loading homepage:', error);
    res.status(500).send('Error loading homepage');
  }
});

module.exports = router;
