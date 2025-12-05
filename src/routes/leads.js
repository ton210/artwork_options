const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const db = require('../config/database');
const { leadLimiter } = require('../middleware/rateLimiter');

// Submit lead form
router.post('/submit',
  leadLimiter,
  [
    body('email').isEmail().normalizeEmail(),
    body('contactName').trim().isLength({ min: 2, max: 255 }),
    body('dispensaryName').optional().trim(),
    body('phone').optional().trim(),
    body('message').optional().trim(),
    body('source').isIn(['listing_page', 'sidebar', 'claim', 'footer']),
    body('dispensaryId').optional().isInt()
  ],
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({
          success: false,
          errors: errors.array()
        });
      }

      const {
        dispensaryId,
        dispensaryName,
        contactName,
        email,
        phone,
        message,
        source
      } = req.body;

      await db.query(
        `INSERT INTO leads (
          dispensary_id, dispensary_name, contact_name,
          email, phone, message, source
        ) VALUES ($1, $2, $3, $4, $5, $6, $7)`,
        [
          dispensaryId || null,
          dispensaryName || null,
          contactName,
          email,
          phone || null,
          message || null,
          source
        ]
      );

      res.json({
        success: true,
        message: 'Thank you! We will contact you soon.'
      });

    } catch (error) {
      console.error('Error submitting lead:', error);
      res.status(500).json({
        success: false,
        message: 'Error submitting form. Please try again.'
      });
    }
  }
);

module.exports = router;
