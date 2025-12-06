const express = require('express');
const router = express.Router();
const { State } = require('../models/State');
const db = require('../config/database');

// Homepage
router.get('/', async (req, res) => {
  try {
    // Get all states with dispensary counts
    const states = await db.query(`
      SELECT s.*, COUNT(DISTINCT d.id) as dispensary_count
      FROM states s
      LEFT JOIN counties c ON s.id = c.state_id
      LEFT JOIN dispensaries d ON c.id = d.county_id AND d.is_active = true
      GROUP BY s.id
      ORDER BY s.name ASC
    `);

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

    res.render('home', {
      title: 'Top Dispensaries 2026 - Find the Best Cannabis Dispensaries',
      states: states.rows,
      stats: stats.rows[0],
      meta: {
        description: 'Discover the top-rated cannabis dispensaries across 24+ legal states. User-voted rankings based on reviews, ratings, and community feedback.',
        keywords: 'cannabis dispensary, marijuana dispensary, weed dispensary, top dispensaries, dispensary rankings'
      }
    });
  } catch (error) {
    console.error('Error loading homepage:', error);
    res.status(500).send('Error loading homepage');
  }
});

module.exports = router;
