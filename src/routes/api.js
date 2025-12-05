const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const Vote = require('../models/Vote');
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
        return res.status(400).json({ success: false, errors: errors.array() });
      }

      const { dispensaryId, voteType } = req.body;
      const clientIP = getClientIP(req);
      const sessionId = req.session.id;

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

module.exports = router;
