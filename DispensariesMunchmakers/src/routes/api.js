const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const Vote = require('../models/Vote');
const Dispensary = require('../models/Dispensary');
const { trackClickEvent, getClientIP } = require('../middleware/analytics');
const { voteLimiter, apiLimiter } = require('../middleware/rateLimiter');

// Apply general API rate limiting
router.use(apiLimiter);

// Vote endpoint
router.post('/vote',
  voteLimiter,
  [
    body('dispensaryId').isInt(),
    body('voteType').isIn(['1', '-1', 1, -1])
  ],
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        console.log('Vote validation errors:', errors.array(), 'body:', req.body);
        return res.status(400).json({ success: false, errors: errors.array() });
      }

      const { dispensaryId, voteType } = req.body;
      const clientIP = getClientIP(req);
      const sessionId = req.session?.id || req.sessionID || null;

      console.log('Vote request received - dispensaryId:', dispensaryId, 'type:', typeof dispensaryId, 'voteType:', voteType);

      // Validate dispensary exists
      const dispensary = await Dispensary.findById(parseInt(dispensaryId));
      console.log('Dispensary lookup result:', dispensary ? `Found: ${dispensary.name}` : 'NOT FOUND');
      if (!dispensary) {
        console.error('Dispensary not found for ID:', dispensaryId);
        return res.status(404).json({
          success: false,
          message: 'Dispensary not found'
        });
      }

      // Check if user can vote
      const canVote = await Vote.canVote(dispensaryId, clientIP);

      if (!canVote) {
        return res.status(429).json({
          success: false,
          message: 'You have already voted for this dispensary today'
        });
      }

      // Create vote
      const vote = await Vote.create(
        parseInt(dispensaryId),
        parseInt(voteType),
        clientIP,
        sessionId
      );

      // Get updated vote counts
      const voteCounts = await Vote.getVoteCounts(dispensaryId);

      res.json({
        success: true,
        vote,
        voteCounts
      });

    } catch (error) {
      console.error('Error processing vote:', error);
      res.status(500).json({
        success: false,
        message: error.message || 'Error processing vote'
      });
    }
  }
);

// Track click event
router.post('/track/click',
  [
    body('dispensaryId').isInt(),
    body('eventType').isIn(['website', 'phone', 'directions', 'claim'])
  ],
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({ success: false, errors: errors.array() });
      }

      const { dispensaryId, eventType } = req.body;

      await trackClickEvent(dispensaryId, eventType, req);

      res.json({ success: true });

    } catch (error) {
      console.error('Error tracking click:', error);
      res.status(500).json({ success: false });
    }
  }
);

// Get vote status for a dispensary
router.get('/vote-status/:dispensaryId', async (req, res) => {
  try {
    const dispensaryId = parseInt(req.params.dispensaryId);
    const clientIP = getClientIP(req);

    const canVote = await Vote.canVote(dispensaryId, clientIP);
    const voteCounts = await Vote.getVoteCounts(dispensaryId);
    const recentVotes = await Vote.getRecentVotes(dispensaryId, 7);

    res.json({
      success: true,
      canVote,
      voteCounts,
      recentVotes
    });

  } catch (error) {
    console.error('Error getting vote status:', error);
    res.status(500).json({ success: false });
  }
});

// Map API endpoints
// Get map data for county dispensaries
router.get('/map/county/:stateSlug/:countySlug', async (req, res) => {
  try {
    const { County } = require('../models/State');
    const Dispensary = require('../models/Dispensary');

    const county = await County.findBySlug(req.params.stateSlug, req.params.countySlug);

    if (!county) {
      return res.status(404).json({ error: 'County not found' });
    }

    const dispensaries = await Dispensary.getMapData(county.id);

    res.json({
      success: true,
      dispensaries: dispensaries.map(d => ({
        id: d.id,
        name: d.name,
        slug: d.slug,
        lat: parseFloat(d.lat),
        lng: parseFloat(d.lng),
        rating: d.google_rating,
        reviewCount: d.google_review_count,
        address: `${d.address_street}, ${d.city}, ${d.state_abbr} ${d.zip}`,
        logoUrl: d.logo_url
      }))
    });
  } catch (error) {
    console.error('Error fetching map data:', error);
    res.status(500).json({ error: 'Failed to load map data' });
  }
});

// Get map data for state dispensaries
router.get('/map/state/:stateSlug', async (req, res) => {
  try {
    const State = require('../models/State');
    const db = require('../config/database');

    const state = await State.findBySlug(req.params.stateSlug);

    if (!state) {
      return res.status(404).json({ error: 'State not found' });
    }

    const result = await db.query(`
      SELECT d.id, d.name, d.slug, d.lat, d.lng, d.google_rating,
             d.google_review_count, d.address_street, d.city, d.zip, d.logo_url,
             s.abbreviation as state_abbr
      FROM dispensaries d
      LEFT JOIN counties c ON d.county_id = c.id
      LEFT JOIN states s ON c.state_id = s.id
      WHERE c.state_id = $1 AND d.is_active = true AND d.lat IS NOT NULL
      ORDER BY d.google_rating DESC
      LIMIT 500
    `, [state.id]);

    res.json({
      success: true,
      dispensaries: result.rows.map(d => ({
        id: d.id,
        name: d.name,
        slug: d.slug,
        lat: parseFloat(d.lat),
        lng: parseFloat(d.lng),
        rating: d.google_rating,
        reviewCount: d.google_review_count,
        address: `${d.address_street}, ${d.city}, ${d.state_abbr} ${d.zip}`,
        logoUrl: d.logo_url
      }))
    });
  } catch (error) {
    console.error('Error fetching state map data:', error);
    res.status(500).json({ error: 'Failed to load map data' });
  }
});

// Get single dispensary location
router.get('/map/dispensary/:id', async (req, res) => {
  try {
    const Dispensary = require('../models/Dispensary');
    const dispensary = await Dispensary.findById(parseInt(req.params.id));

    if (!dispensary) {
      return res.status(404).json({ error: 'Dispensary not found' });
    }

    res.json({
      success: true,
      lat: parseFloat(dispensary.lat),
      lng: parseFloat(dispensary.lng),
      name: dispensary.name,
      address: `${dispensary.address_street}, ${dispensary.city}`
    });
  } catch (error) {
    console.error('Error fetching dispensary location:', error);
    res.status(500).json({ error: 'Failed to load location' });
  }
});

// Get all active dispensaries (for Near Me feature)
router.get('/dispensaries/all', async (req, res) => {
  try {
    const db = require('../config/database');
    const result = await db.query(
      `SELECT d.id, d.name, d.slug, d.lat, d.lng, d.city, d.google_rating, d.google_review_count,
              d.address_street, d.zip, d.phone, d.logo_url, s.name as state_name, s.abbreviation as state_abbr
       FROM dispensaries d
       LEFT JOIN counties c ON d.county_id = c.id
       LEFT JOIN states s ON c.state_id = s.id
       WHERE d.is_active = true AND d.lat IS NOT NULL AND d.lng IS NOT NULL
       ORDER BY d.google_rating DESC NULLS LAST
       LIMIT 3000`
    );

    res.json({
      success: true,
      dispensaries: result.rows
    });

  } catch (error) {
    console.error('Error fetching all dispensaries:', error);
    res.status(500).json({ success: false, error: 'Failed to fetch dispensaries' });
  }
});

module.exports = router;
