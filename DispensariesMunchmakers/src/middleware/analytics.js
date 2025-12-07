const db = require('../config/database');
const crypto = require('crypto');

function hashIP(ip) {
  return crypto.createHash('sha256').update(ip).digest('hex');
}

function getClientIP(req) {
  return req.headers['x-forwarded-for']?.split(',')[0] ||
         req.connection.remoteAddress ||
         req.socket.remoteAddress ||
         req.ip;
}

async function trackPageView(req, res, next) {
  // Track all page views, not just dispensary pages
  if (req.method === 'GET' && !req.path.startsWith('/api/') && !req.path.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|json)$/)) {
    try {
      const ip = getClientIP(req);
      const ipHash = hashIP(ip);
      const country = getCountryFromRequest(req); // Default to 'US' for Heroku
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
          req.get('User-Agent'),
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
  hashIP
};
