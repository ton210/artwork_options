const express = require('express');
const router = express.Router();
const Review = require('../models/Review');

// Rate limiting middleware
const rateLimit = {};
const RATE_LIMIT_WINDOW = 24 * 60 * 60 * 1000; // 24 hours
const MAX_REVIEWS_PER_DAY = 3;

function checkRateLimit(ip) {
  const now = Date.now();
  if (!rateLimit[ip]) {
    rateLimit[ip] = [];
  }

  // Remove old entries
  rateLimit[ip] = rateLimit[ip].filter(timestamp => now - timestamp < RATE_LIMIT_WINDOW);

  return rateLimit[ip].length < MAX_REVIEWS_PER_DAY;
}

function recordReview(ip) {
  if (!rateLimit[ip]) {
    rateLimit[ip] = [];
  }
  rateLimit[ip].push(Date.now());
}

/**
 * Submit a new review
 * POST /api/reviews/submit
 */
router.post('/submit', async (req, res) => {
  try {
    const { dispensaryId, authorName, authorEmail, rating, reviewText } = req.body;
    const ipAddress = req.ip || req.connection.remoteAddress;

    // Validation
    if (!dispensaryId || !authorName || !rating || !reviewText) {
      return res.status(400).json({ error: 'Missing required fields' });
    }

    if (rating < 1 || rating > 5) {
      return res.status(400).json({ error: 'Rating must be between 1 and 5' });
    }

    if (reviewText.length < 50) {
      return res.status(400).json({ error: 'Review must be at least 50 characters' });
    }

    if (reviewText.length > 1000) {
      return res.status(400).json({ error: 'Review cannot exceed 1000 characters' });
    }

    // Check rate limit
    if (!checkRateLimit(ipAddress)) {
      return res.status(429).json({ error: 'Rate limit exceeded. Maximum 3 reviews per day.' });
    }

    // Check if user can review this dispensary
    const canReview = await Review.canReview(dispensaryId, ipAddress);
    if (!canReview) {
      return res.status(400).json({ error: 'You have already reviewed this dispensary' });
    }

    // Check for spam
    if (Review.isSpam(reviewText)) {
      return res.status(400).json({ error: 'Review flagged as spam' });
    }

    // Create review
    const review = await Review.create({
      dispensaryId,
      authorName,
      authorEmail,
      rating,
      reviewText,
      ipAddress
    });

    recordReview(ipAddress);

    res.json({
      success: true,
      review: {
        id: review.id,
        authorName: review.author_name,
        rating: review.rating,
        reviewText: review.review_text,
        createdAt: review.created_at
      }
    });

  } catch (error) {
    console.error('Error submitting review:', error);
    res.status(500).json({ error: 'Failed to submit review' });
  }
});

/**
 * Get reviews for a dispensary
 * GET /api/reviews/dispensary/:id
 */
router.get('/dispensary/:id', async (req, res) => {
  try {
    const dispensaryId = parseInt(req.params.id);
    const limit = parseInt(req.query.limit) || 20;
    const offset = parseInt(req.query.offset) || 0;
    const sortBy = req.query.sortBy || 'recent'; // recent, highest, helpful

    const reviews = await Review.findByDispensary(dispensaryId, { limit, offset, sortBy });
    const stats = await Review.getStats(dispensaryId);
    const total = await Review.getCount(dispensaryId);

    res.json({
      reviews,
      stats,
      total,
      hasMore: total > (offset + reviews.length)
    });

  } catch (error) {
    console.error('Error fetching reviews:', error);
    res.status(500).json({ error: 'Failed to fetch reviews' });
  }
});

/**
 * Mark review as helpful/not helpful
 * POST /api/reviews/:id/helpful
 */
router.post('/:id/helpful', async (req, res) => {
  try {
    const reviewId = parseInt(req.params.id);
    const { helpful } = req.body; // true or false
    const ipAddress = req.ip || req.connection.remoteAddress;

    if (typeof helpful !== 'boolean') {
      return res.status(400).json({ error: 'Invalid helpful value' });
    }

    const result = await Review.markHelpful(reviewId, ipAddress, helpful);

    if (result.success) {
      res.json({ success: true });
    } else {
      res.status(400).json({ error: result.error });
    }

  } catch (error) {
    console.error('Error marking review helpful:', error);
    res.status(500).json({ error: 'Failed to mark review' });
  }
});

/**
 * Check if user can review a dispensary
 * GET /api/reviews/can-review/:dispensaryId
 */
router.get('/can-review/:dispensaryId', async (req, res) => {
  try {
    const dispensaryId = parseInt(req.params.dispensaryId);
    const ipAddress = req.ip || req.connection.remoteAddress;

    const canReview = await Review.canReview(dispensaryId, ipAddress);
    const withinRateLimit = checkRateLimit(ipAddress);

    res.json({
      canReview: canReview && withinRateLimit,
      reason: !canReview ? 'already_reviewed' : (!withinRateLimit ? 'rate_limit' : null)
    });

  } catch (error) {
    console.error('Error checking review eligibility:', error);
    res.status(500).json({ error: 'Failed to check eligibility' });
  }
});

module.exports = router;
