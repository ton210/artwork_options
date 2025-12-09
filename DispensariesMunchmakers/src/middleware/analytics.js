const db = require('../config/database');
const crypto = require('crypto');

// Common bot User-Agent patterns
const BOT_PATTERNS = [
  /googlebot/i,
  /bingbot/i,
  /slurp/i,           // Yahoo
  /duckduckbot/i,
  /baiduspider/i,
  /yandexbot/i,
  /sogou/i,
  /exabot/i,
  /facebot/i,
  /facebookexternalhit/i,
  /ia_archiver/i,
  /mj12bot/i,
  /semrushbot/i,
  /ahrefsbot/i,
  /dotbot/i,
  /petalbot/i,
  /bytespider/i,
  /applebot/i,
  /twitterbot/i,
  /linkedinbot/i,
  /pinterest/i,
  /screaming frog/i,
  /sitebulb/i,
  /crawler/i,
  /spider/i,
  /bot\b/i,
  /headless/i,
  /puppet/i,
  /phantom/i,
  /selenium/i,
  /wget/i,
  /curl/i,
  /python-requests/i,
  /python-urllib/i,
  /go-http-client/i,
  /java\//i,
  /libwww/i,
  /httpunit/i,
  /nutch/i,
  /httrack/i,
  /apache-httpclient/i,
  /guzzlehttp/i,
  /node-fetch/i,
  /axios/i,
  /postmanruntime/i,
  /^$/                // Empty user agent
];

// Paths to exclude from tracking
const EXCLUDED_PATHS = [
  '/sitemap.xml',
  '/sitemap',
  '/robots.txt',
  '/favicon.ico',
  '/apple-touch-icon',
  '/manifest.json',
  '/.well-known',
  '/health',
  '/ping'
];

function hashIP(ip) {
  return crypto.createHash('sha256').update(ip).digest('hex');
}

function getClientIP(req) {
  return req.headers['x-forwarded-for']?.split(',')[0] ||
         req.connection.remoteAddress ||
         req.socket.remoteAddress ||
         req.ip;
}

function isBot(userAgent) {
  if (!userAgent) return true; // No user agent = likely bot
  return BOT_PATTERNS.some(pattern => pattern.test(userAgent));
}

function isExcludedPath(path) {
  return EXCLUDED_PATHS.some(excluded => path.startsWith(excluded));
}

async function trackPageView(req, res, next) {
  // Track page views for real visitors only
  if (req.method === 'GET' &&
      !req.path.startsWith('/api/') &&
      !req.path.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|json|xml|txt|woff|woff2|ttf|eot|map)$/) &&
      !isExcludedPath(req.path)) {

    const userAgent = req.get('User-Agent') || '';
    const isBotVisit = isBot(userAgent);

    // Skip bots entirely - don't track them at all
    if (isBotVisit) {
      return next();
    }

    try {
      const ip = getClientIP(req);
      const ipHash = hashIP(ip);
      const country = getCountryFromRequest(req);
      const urlPath = req.path;

      let dispensaryId = null;

      // If it's a dispensary page, get the dispensary ID
      if (req.path.startsWith('/dispensary/')) {
        const slug = req.path.split('/')[2];
        const result = await db.query(
          'SELECT id FROM dispensaries WHERE slug = $1',
          [slug]
        );
        if (result.rows.length > 0) {
          dispensaryId = result.rows[0].id;
        }
      }

      // Track page view asynchronously (don't wait)
      db.query(
        `INSERT INTO page_views (dispensary_id, ip_hash, referrer, user_agent, url_path, country)
         VALUES ($1, $2, $3, $4, $5, $6)`,
        [
          dispensaryId,
          ipHash,
          req.get('Referrer') || req.get('Referer'),
          userAgent,
          urlPath,
          country
        ]
      ).catch(err => {
        console.error('Error tracking page view:', err);
      });
    } catch (error) {
      console.error('Error in trackPageView middleware:', error);
    }
  }

  next();
}

function getCountryFromRequest(req) {
  // Check for Cloudflare country header
  const cfCountry = req.headers['cf-ipcountry'];
  if (cfCountry) return cfCountry;

  // Check for other geo headers
  const xCountry = req.headers['x-country-code'];
  if (xCountry) return xCountry;

  // Default to US (most Heroku traffic is US)
  return 'US';
}

async function trackClickEvent(dispensaryId, eventType, req) {
  try {
    const ip = getClientIP(req);
    const ipHash = hashIP(ip);

    await db.query(
      `INSERT INTO click_events (dispensary_id, event_type, ip_hash)
       VALUES ($1, $2, $3)`,
      [dispensaryId, eventType, ipHash]
    );

    return true;
  } catch (error) {
    console.error('Error tracking click event:', error);
    return false;
  }
}

module.exports = {
  trackPageView,
  trackClickEvent,
  getClientIP,
  hashIP,
  isBot,
  BOT_PATTERNS
};
