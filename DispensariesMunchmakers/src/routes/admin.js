const express = require('express');
const router = express.Router();
const { isAuthenticated, checkAdminCredentials } = require('../middleware/auth');
const { loginLimiter } = require('../middleware/rateLimiter');
const db = require('../config/database');
const scraper = require('../services/scraper');
const rankingCalculator = require('../services/rankingCalculator');
const { State } = require('../models/State');
const Dispensary = require('../models/Dispensary');

// Login page
router.get('/login', (req, res) => {
  if (req.session && req.session.isAdmin) {
    return res.redirect('/admin');
  }

  res.render('admin/login', {
    title: 'Admin Login - Dispensary Rankings',
    error: null
  });
});

// Login POST
router.post('/login', async (req, res) => {
  try {
    const { username, password } = req.body;

    console.log('Admin login attempt:', { username, hasPassword: !!password });

    if (!username || !password) {
      return res.render('admin/login', {
        title: 'Admin Login - Dispensary Rankings',
        error: 'Username and password are required'
      });
    }

    const isValid = await checkAdminCredentials(username, password);

    console.log('Admin login validation result:', isValid);
    console.log('Session object exists:', !!req.session);

    if (isValid) {
      // Ensure session exists
      if (!req.session) {
        return res.render('admin/login', {
          title: 'Admin Login - Dispensary Rankings',
          error: 'Session not available. Please try refreshing the page.'
        });
      }

      req.session.isAdmin = true;
      req.session.username = username;

      // Save session before redirect
      req.session.save((err) => {
        if (err) {
          console.error('Session save error:', err);
          return res.render('admin/login', {
            title: 'Admin Login - Dispensary Rankings',
            error: 'Failed to save session. Please try again.'
          });
        }
        console.log('Admin login successful, redirecting');
        return res.redirect('/admin');
      });
      return;
    }

    res.render('admin/login', {
      title: 'Admin Login - Dispensary Rankings',
      error: 'Invalid username or password'
    });

  } catch (error) {
    console.error('Login error:', error);
    res.render('admin/login', {
      title: 'Admin Login - Dispensary Rankings',
      error: `Error: ${error.message}`
    });
  }
});

// Logout
router.get('/logout', (req, res) => {
  req.session.destroy();
  res.redirect('/admin/login');
});

// All routes below require authentication
router.use(isAuthenticated);

// Dashboard
router.get('/', async (req, res) => {
  try {
    // Get overall stats with US filter
    const stats = await db.query(`
      SELECT
        (SELECT COUNT(*) FROM dispensaries WHERE is_active = true) as total_dispensaries,
        (SELECT COUNT(*) FROM votes WHERE DATE(created_at) = CURRENT_DATE) as votes_today,
        (SELECT COUNT(*) FROM page_views WHERE DATE(created_at) = CURRENT_DATE AND (country = 'US' OR country IS NULL)) as views_today_us,
        (SELECT COUNT(*) FROM page_views WHERE DATE(created_at) = CURRENT_DATE) as views_today_all,
        (SELECT COUNT(*) FROM leads WHERE is_contacted = false) as uncontacted_leads,
        (SELECT COUNT(*) FROM states) as total_states,
        (SELECT COUNT(*) FROM counties) as total_counties,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM reviews WHERE is_approved = true) as total_reviews,
        (SELECT COUNT(*) FROM business_claims WHERE is_approved = false) as pending_claims
    `);

    // Get daily stats for last 7 days (US only)
    const dailyStats = await db.query(`
      SELECT
        DATE(created_at) as date,
        COUNT(*) as view_count,
        COUNT(DISTINCT ip_hash) as unique_visitors
      FROM page_views
      WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'
        AND (country = 'US' OR country IS NULL)
      GROUP BY DATE(created_at)
      ORDER BY date DESC
    `);

    // Get top pages by URL (US visitors only)
    const topPages = await db.query(`
      SELECT
        url_path,
        COUNT(*) as view_count,
        COUNT(DISTINCT ip_hash) as unique_visitors
      FROM page_views
      WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
        AND (country = 'US' OR country IS NULL)
        AND url_path IS NOT NULL
      GROUP BY url_path
      ORDER BY view_count DESC
      LIMIT 20
    `);

    // Get top dispensaries by views (US visitors)
    const topDispensaries = await db.query(`
      SELECT
        d.name,
        d.city,
        s.abbreviation as state_abbr,
        d.slug,
        COUNT(pv.id) as view_count,
        COUNT(DISTINCT pv.ip_hash) as unique_visitors
      FROM dispensaries d
      JOIN page_views pv ON d.id = pv.dispensary_id
      LEFT JOIN counties c ON d.county_id = c.id
      LEFT JOIN states s ON c.state_id = s.id
      WHERE pv.created_at >= CURRENT_DATE - INTERVAL '30 days'
        AND (pv.country = 'US' OR pv.country IS NULL)
      GROUP BY d.id, d.name, d.city, s.abbreviation, d.slug
      ORDER BY view_count DESC
      LIMIT 10
    `);

    // Get traffic by country
    const trafficByCountry = await db.query(`
      SELECT
        COALESCE(country, 'Unknown') as country,
        COUNT(*) as view_count,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM page_views WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'), 2) as percentage
      FROM page_views
      WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
      GROUP BY country
      ORDER BY view_count DESC
      LIMIT 10
    `);

    // Get top dispensaries last 24 hours (US visitors)
    const topDispensaries24h = await db.query(`
      SELECT
        d.name,
        d.city,
        s.abbreviation as state_abbr,
        d.slug,
        COUNT(pv.id) as view_count,
        COUNT(DISTINCT pv.ip_hash) as unique_visitors
      FROM dispensaries d
      JOIN page_views pv ON d.id = pv.dispensary_id
      LEFT JOIN counties c ON d.county_id = c.id
      LEFT JOIN states s ON c.state_id = s.id
      WHERE pv.created_at >= NOW() - INTERVAL '24 hours'
        AND (pv.country = 'US' OR pv.country IS NULL)
      GROUP BY d.id, d.name, d.city, s.abbreviation, d.slug
      ORDER BY view_count DESC
      LIMIT 10
    `);

    // Get top states last 24 hours (US visitors)
    const topStates24h = await db.query(`
      SELECT
        s.name as state_name,
        s.slug as state_slug,
        COUNT(pv.id) as view_count,
        COUNT(DISTINCT pv.ip_hash) as unique_visitors
      FROM page_views pv
      JOIN dispensaries d ON pv.dispensary_id = d.id
      JOIN counties c ON d.county_id = c.id
      JOIN states s ON c.state_id = s.id
      WHERE pv.created_at >= NOW() - INTERVAL '24 hours'
        AND (pv.country = 'US' OR pv.country IS NULL)
        AND pv.dispensary_id IS NOT NULL
      GROUP BY s.id, s.name, s.slug
      ORDER BY view_count DESC
      LIMIT 10
    `);

    // Get recent scrape logs
    const scrapeLogs = await db.query(`
      SELECT * FROM scrape_logs
      ORDER BY started_at DESC
      LIMIT 10
    `);

    // Get recent leads
    const recentLeads = await db.query(`
      SELECT * FROM leads
      ORDER BY created_at DESC
      LIMIT 5
    `);

    res.render('admin/dashboard', {
      title: 'Admin Dashboard - Dispensary Rankings',
      stats: stats.rows[0],
      dailyStats: dailyStats.rows,
      topPages: topPages.rows,
      topDispensaries: topDispensaries.rows,
      topDispensaries24h: topDispensaries24h.rows,
      topStates24h: topStates24h.rows,
      trafficByCountry: trafficByCountry.rows,
      scrapeLogs: scrapeLogs.rows,
      recentLeads: recentLeads.rows
    });

  } catch (error) {
    console.error('Error loading dashboard:', error);
    res.status(500).render('admin/login', {
      title: 'Admin Dashboard Error',
      error: `Dashboard error: ${error.message}. The analytics migration may need to be run: heroku run "node src/db/add-analytics-country.js"`
    });
  }
});

// Dispensaries list
router.get('/dispensaries', async (req, res) => {
  try {
    const page = parseInt(req.query.page) || 1;
    const limit = 50;
    const offset = (page - 1) * limit;

    const dispensaries = await db.query(`
      SELECT d.*, c.name as county_name, s.name as state_name, s.abbreviation as state_abbr
      FROM dispensaries d
      LEFT JOIN counties c ON d.county_id = c.id
      LEFT JOIN states s ON c.state_id = s.id
      WHERE d.is_active = true
      ORDER BY d.updated_at DESC
      LIMIT $1 OFFSET $2
    `, [limit, offset]);

    const countResult = await db.query(
      'SELECT COUNT(*) FROM dispensaries WHERE is_active = true'
    );

    const totalPages = Math.ceil(countResult.rows[0].count / limit);

    res.render('admin/dispensaries', {
      title: 'Manage Dispensaries - Admin',
      dispensaries: dispensaries.rows,
      currentPage: page,
      totalPages
    });

  } catch (error) {
    console.error('Error loading dispensaries:', error);
    res.status(500).send('Error loading dispensaries');
  }
});

// Edit dispensary
router.get('/dispensary/:id/edit', async (req, res) => {
  try {
    const dispensary = await Dispensary.findById(req.params.id);
    const states = await State.findAll();

    res.render('admin/edit-dispensary', {
      title: `Edit ${dispensary.name} - Admin`,
      dispensary,
      states
    });

  } catch (error) {
    console.error('Error loading dispensary:', error);
    res.status(500).send('Error loading dispensary');
  }
});

// Update dispensary
router.post('/dispensary/:id/update', async (req, res) => {
  try {
    await Dispensary.update(req.params.id, req.body);
    res.redirect('/admin/dispensaries');
  } catch (error) {
    console.error('Error updating dispensary:', error);
    res.status(500).send('Error updating dispensary');
  }
});

// Delete dispensary
router.post('/dispensary/:id/delete', async (req, res) => {
  try {
    await Dispensary.delete(req.params.id);
    res.redirect('/admin/dispensaries');
  } catch (error) {
    console.error('Error deleting dispensary:', error);
    res.status(500).send('Error deleting dispensary');
  }
});

// Scraping interface
router.get('/scrape', async (req, res) => {
  try {
    const states = await State.findAll();

    res.render('admin/scrape', {
      title: 'Scrape Dispensaries - Admin',
      states
    });

  } catch (error) {
    console.error('Error loading scrape page:', error);
    res.status(500).send('Error loading scrape page');
  }
});

// Trigger scrape
router.post('/scrape/state', async (req, res) => {
  try {
    const { stateId } = req.body;

    // Run scrape in background (in production, use Bull queue)
    scraper.scrapeState(stateId).then(() => {
      console.log(`Scrape completed for state ${stateId}`);
    }).catch(err => {
      console.error(`Scrape failed for state ${stateId}:`, err);
    });

    res.json({
      success: true,
      message: 'Scraping started. Check logs for progress.'
    });

  } catch (error) {
    console.error('Error starting scrape:', error);
    res.status(500).json({ success: false, message: 'Error starting scrape' });
  }
});

// Calculate rankings
router.post('/rankings/calculate', async (req, res) => {
  try {
    rankingCalculator.calculateAllRankings().then(result => {
      console.log('Rankings calculation completed:', result);
    }).catch(err => {
      console.error('Rankings calculation failed:', err);
    });

    res.json({
      success: true,
      message: 'Rankings calculation started. Check logs for progress.'
    });

  } catch (error) {
    console.error('Error starting rankings calculation:', error);
    res.status(500).json({ success: false, message: 'Error starting calculation' });
  }
});

// Leads management
router.get('/leads', async (req, res) => {
  try {
    const leads = await db.query(`
      SELECT * FROM leads
      ORDER BY created_at DESC
      LIMIT 100
    `);

    res.render('admin/leads', {
      title: 'Manage Leads - Admin',
      leads: leads.rows
    });

  } catch (error) {
    console.error('Error loading leads:', error);
    res.status(500).send('Error loading leads');
  }
});

// Mark lead as contacted
router.post('/lead/:id/contacted', async (req, res) => {
  try {
    await db.query(
      'UPDATE leads SET is_contacted = true WHERE id = $1',
      [req.params.id]
    );

    res.json({ success: true });

  } catch (error) {
    console.error('Error updating lead:', error);
    res.status(500).json({ success: false });
  }
});

module.exports = router;
