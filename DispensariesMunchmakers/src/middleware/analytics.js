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
  // Only track on dispensary detail pages
  if (req.path.startsWith('/dispensary/') && req.method === 'GET') {
    try {
      const slug = req.params.slug;

      // Get dispensary ID from slug
      const result = await db.query(
        'SELECT id FROM dispensaries WHERE slug = $1',
        [slug]
      );

      if (result.rows.length > 0) {
        const dispensaryId = result.rows[0].id;
        const ip = getClientIP(req);
        const ipHash = hashIP(ip);

        // Track page view asynchronously (don't wait)
        db.query(
          `INSERT INTO page_views (dispensary_id, ip_hash, referrer, user_agent)
           VALUES ($1, $2, $3, $4)`,
          [
            dispensaryId,
            ipHash,
            req.get('Referrer') || req.get('Referer'),
            req.get('User-Agent')
          ]
        ).catch(err => {
          console.error('Error tracking page view:', err);
        });
      }
    } catch (error) {
      console.error('Error in trackPageView middleware:', error);
    }
  }

  next();
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
