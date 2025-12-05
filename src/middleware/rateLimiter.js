const rateLimit = require('express-rate-limit');
const { getRedisClient } = require('../config/redis');

// General API rate limiter
const apiLimiter = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 100, // limit each IP to 100 requests per windowMs
  message: 'Too many requests from this IP, please try again later.',
  standardHeaders: true,
  legacyHeaders: false,
});

// Voting rate limiter (stricter)
const voteLimiter = rateLimit({
  windowMs: 24 * 60 * 60 * 1000, // 24 hours
  max: 20, // 20 votes per day per IP
  message: 'You have reached your daily voting limit. Please try again tomorrow.',
  standardHeaders: true,
  legacyHeaders: false,
  skipSuccessfulRequests: false,
});

// Lead form rate limiter
const leadLimiter = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 5, // 5 submissions per hour
  message: 'Too many form submissions. Please try again later.',
  standardHeaders: true,
  legacyHeaders: false,
});

// Admin login rate limiter
const loginLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 5, // 5 attempts
  message: 'Too many login attempts. Please try again later.',
  standardHeaders: true,
  legacyHeaders: false,
});

module.exports = {
  apiLimiter,
  voteLimiter,
  leadLimiter,
  loginLimiter
};
