require('dotenv').config();
const express = require('express');
const session = require('express-session');
const RedisStore = require('connect-redis').default;
const helmet = require('helmet');
const compression = require('compression');
const morgan = require('morgan');
const path = require('path');
const { getRedisClient } = require('./config/redis');
const { trackPageView } = require('./middleware/analytics');

const app = express();
const PORT = process.env.PORT || 3000;

// View engine setup
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Security middleware
app.use(helmet({
  contentSecurityPolicy: false, // Disable for now to allow inline scripts
}));

// Compression
app.use(compression());

// Logging
app.use(morgan(process.env.NODE_ENV === 'production' ? 'combined' : 'dev'));

// Body parsing
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Static files
app.use(express.static(path.join(__dirname, 'public')));

// Session setup (async initialization)
async function setupSession() {
  try {
    const redisClient = await getRedisClient();

    app.use(session({
      store: new RedisStore({ client: redisClient }),
      secret: process.env.SESSION_SECRET || 'your-secret-key-change-in-production',
      resave: false,
      saveUninitialized: false,
      cookie: {
        secure: process.env.NODE_ENV === 'production',
        httpOnly: true,
        maxAge: 1000 * 60 * 60 * 24 * 7 // 1 week
      }
    }));

    console.log('✓ Session middleware configured');
  } catch (error) {
    console.error('Error setting up session:', error);
    // Fallback to memory store in development
    app.use(session({
      secret: process.env.SESSION_SECRET || 'your-secret-key-change-in-production',
      resave: false,
      saveUninitialized: false,
      cookie: {
        secure: false,
        httpOnly: true,
        maxAge: 1000 * 60 * 60 * 24 * 7
      }
    }));
  }
}

// Force HTTPS in production
app.use((req, res, next) => {
  if (process.env.NODE_ENV === 'production' && req.header('x-forwarded-proto') !== 'https') {
    res.redirect(`https://${req.header('host')}${req.url}`);
  } else {
    next();
  }
});

// Analytics tracking middleware
app.use(trackPageView);

// Make environment variables available to views
app.use((req, res, next) => {
  res.locals.MUNCHMAKERS_URL = process.env.MUNCHMAKERS_SITE_URL || 'https://munchmakers.com';
  res.locals.currentPath = req.path;
  res.locals.isAdmin = req.session?.isAdmin || false;
  next();
});

// Routes
const indexRoutes = require('./routes/index');
const dispensaryRoutes = require('./routes/dispensaries');
const brandsRoutes = require('./routes/brands');
const pagesRoutes = require('./routes/pages');
const sitemapRoutes = require('./routes/sitemap');
const apiRoutes = require('./routes/api');
const reviewRoutes = require('./routes/reviews');
const adminRoutes = require('./routes/admin');
const leadRoutes = require('./routes/leads');

app.use('/', indexRoutes);
app.use('/dispensaries', dispensaryRoutes);
app.use('/dispensary', dispensaryRoutes); // Alternative singular route
app.use('/brands', brandsRoutes);
app.use('/', pagesRoutes);
app.use('/', sitemapRoutes);
app.use('/api', apiRoutes);
app.use('/api/reviews', reviewRoutes);
app.use('/admin', adminRoutes);
app.use('/leads', leadRoutes);

// 404 handler
app.use((req, res) => {
  res.status(404).render('404', {
    title: 'Page Not Found - Dispensary Rankings',
    path: req.path
  });
});

// Error handler
app.use((err, req, res, next) => {
  console.error('Error:', err);

  res.status(err.status || 500);

  if (process.env.NODE_ENV === 'production') {
    res.render('error', {
      title: 'Error - Dispensary Rankings',
      message: 'Something went wrong. Please try again later.',
      error: {}
    });
  } else {
    res.render('error', {
      title: 'Error - Dispensary Rankings',
      message: err.message,
      error: err
    });
  }
});

// Start server
async function startServer() {
  try {
    // Setup session first
    await setupSession();

    app.listen(PORT, () => {
      console.log(`
╔═══════════════════════════════════════════════╗
║   Dispensary Rankings Application            ║
║   Server running on port ${PORT}                ║
║   Environment: ${process.env.NODE_ENV || 'development'}                  ║
╚═══════════════════════════════════════════════╝
      `);

      if (process.env.NODE_ENV !== 'production') {
        console.log(`\n🌐 Visit: http://localhost:${PORT}`);
        console.log(`📊 Admin: http://localhost:${PORT}/admin\n`);
      }
    });
  } catch (error) {
    console.error('Failed to start server:', error);
    process.exit(1);
  }
}

// Handle graceful shutdown
process.on('SIGTERM', () => {
  console.log('SIGTERM received, shutting down gracefully...');
  process.exit(0);
});

process.on('SIGINT', () => {
  console.log('SIGINT received, shutting down gracefully...');
  process.exit(0);
});

startServer();

module.exports = app;
