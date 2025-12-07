const express = require('express');
const router = express.Router();
const User = require('../models/User');
const BusinessClaim = require('../models/BusinessClaim');

/**
 * Register new user
 * POST /auth/register
 */
router.post('/register', async (req, res) => {
  try {
    const { email, password, name } = req.body;

    // Validation
    if (!email || !password || !name) {
      return res.status(400).json({ error: 'Missing required fields' });
    }

    if (password.length < 8) {
      return res.status(400).json({ error: 'Password must be at least 8 characters' });
    }

    // Check if email exists
    const existingUser = await User.findByEmail(email);
    if (existingUser) {
      return res.status(400).json({ error: 'Email already registered' });
    }

    // Create user
    const { user, verificationToken } = await User.create(email, password, name);

    // Set session
    req.session.userId = user.id;
    req.session.userEmail = user.email;

    res.json({
      success: true,
      user: {
        id: user.id,
        email: user.email,
        name: user.name,
        isVerified: user.is_verified
      },
      message: 'Registration successful'
    });

  } catch (error) {
    console.error('Error registering user:', error);
    res.status(500).json({ error: 'Registration failed' });
  }
});

/**
 * Login
 * POST /auth/login
 */
router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({ error: 'Missing email or password' });
    }

    const user = await User.verifyPassword(email, password);

    if (!user) {
      return res.status(401).json({ error: 'Invalid email or password' });
    }

    // Set session
    req.session.userId = user.id;
    req.session.userEmail = user.email;

    res.json({
      success: true,
      user: {
        id: user.id,
        email: user.email,
        name: user.name,
        isVerified: user.is_verified
      }
    });

  } catch (error) {
    console.error('Error logging in:', error);
    res.status(500).json({ error: 'Login failed' });
  }
});

/**
 * Logout
 * POST /auth/logout
 */
router.post('/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      return res.status(500).json({ error: 'Logout failed' });
    }
    res.json({ success: true });
  });
});

/**
 * Get current user
 * GET /auth/me
 */
router.get('/me', async (req, res) => {
  try {
    if (!req.session.userId) {
      return res.status(401).json({ error: 'Not authenticated' });
    }

    const user = await User.findById(req.session.userId);

    if (!user) {
      return res.status(404).json({ error: 'User not found' });
    }

    res.json({
      success: true,
      user: {
        id: user.id,
        email: user.email,
        name: user.name,
        isVerified: user.is_verified
      }
    });

  } catch (error) {
    console.error('Error fetching user:', error);
    res.status(500).json({ error: 'Failed to fetch user' });
  }
});

/**
 * Toggle favorite dispensary
 * POST /auth/favorites/:dispensaryId
 */
router.post('/favorites/:dispensaryId', async (req, res) => {
  try {
    if (!req.session.userId) {
      return res.status(401).json({ error: 'Authentication required' });
    }

    const dispensaryId = parseInt(req.params.dispensaryId);
    const isFavorited = await User.isFavorited(req.session.userId, dispensaryId);

    if (isFavorited) {
      await User.removeFavorite(req.session.userId, dispensaryId);
      res.json({ success: true, favorited: false });
    } else {
      await User.addFavorite(req.session.userId, dispensaryId);
      res.json({ success: true, favorited: true });
    }

  } catch (error) {
    console.error('Error toggling favorite:', error);
    res.status(500).json({ error: 'Failed to update favorite' });
  }
});

/**
 * Get user's favorites
 * GET /auth/favorites
 */
router.get('/favorites', async (req, res) => {
  try {
    if (!req.session.userId) {
      return res.status(401).json({ error: 'Authentication required' });
    }

    const favorites = await User.getFavorites(req.session.userId);

    res.json({
      success: true,
      favorites
    });

  } catch (error) {
    console.error('Error fetching favorites:', error);
    res.status(500).json({ error: 'Failed to fetch favorites' });
  }
});

/**
 * Submit business claim
 * POST /auth/claim
 */
router.post('/claim', async (req, res) => {
  try {
    const { dispensaryId, contactName, contactEmail, contactPhone } = req.body;

    // Validation
    if (!dispensaryId || !contactName || !contactEmail) {
      return res.status(400).json({ error: 'Missing required fields' });
    }

    // Check if already claimed
    const isClaimed = await BusinessClaim.isClaimed(dispensaryId);
    if (isClaimed) {
      return res.status(400).json({ error: 'This dispensary has already been claimed' });
    }

    // Check if active claim exists
    const hasActiveClaim = await BusinessClaim.hasActiveClaim(dispensaryId);
    if (hasActiveClaim) {
      return res.status(400).json({ error: 'A claim is already pending for this dispensary' });
    }

    // Create claim
    const claim = await BusinessClaim.create({
      dispensaryId,
      userId: req.session.userId || null,
      contactName,
      contactEmail,
      contactPhone
    });

    // Try auto-verification by email domain
    const autoVerified = await BusinessClaim.autoVerifyByDomain(claim.id);

    res.json({
      success: true,
      claim: {
        id: claim.id,
        verificationCode: claim.verification_code,
        autoVerified
      },
      message: autoVerified
        ? 'Claim verified and approved automatically!'
        : 'Claim submitted. Check your email for verification code.'
    });

  } catch (error) {
    console.error('Error submitting claim:', error);
    res.status(500).json({ error: 'Failed to submit claim' });
  }
});

/**
 * Verify claim with code
 * POST /auth/claim/verify
 */
router.post('/claim/verify', async (req, res) => {
  try {
    const { verificationCode } = req.body;

    if (!verificationCode) {
      return res.status(400).json({ error: 'Verification code required' });
    }

    const claim = await BusinessClaim.findByVerificationCode(verificationCode);

    if (!claim) {
      return res.status(404).json({ error: 'Invalid verification code' });
    }

    await BusinessClaim.verify(claim.id);

    res.json({
      success: true,
      message: 'Claim verified! Awaiting admin approval.'
    });

  } catch (error) {
    console.error('Error verifying claim:', error);
    res.status(500).json({ error: 'Verification failed' });
  }
});

module.exports = router;
