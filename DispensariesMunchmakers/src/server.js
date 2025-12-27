require('dotenv').config();
const express = require('express');
const session = require('express-session');
const RedisStore = require('connect-redis').default;
const helmet = require('helmet');
const compression = require('compression');
const morgan = require('morgan');
const cookieParser = require('cookie-parser');
const path = require('path');
const { getRedisClient } = require('./config/redis');
const { trackPageView } = require('./middleware/analytics');
const { serveTranslatedPage } = require('./middleware/serveTranslated');
const { detectLanguage } = require('./middleware/language');
const { autoTranslateMiddleware } = require('./middleware/autoTranslate');

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

// Cookie parsing
app.use(cookieParser());

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
      saveUninitialized: true, // Changed to true to create session on first request
      cookie: {
        secure: process.env.NODE_ENV === 'production',
        httpOnly: true,
        maxAge: 1000 * 60 * 60 * 24 * 7 // 1 week
      }
    }));

    console.log('✓ Session middleware configured with Redis');
  } catch (error) {
    console.error('Error setting up Redis session, falling back to memory store:', error);
    // Fallback to memory store if Redis fails
    app.use(session({
      secret: process.env.SESSION_SECRET || 'your-secret-key-change-in-production',
      resave: false,
      saveUninitialized: true, // Create session on first request
      cookie: {
        secure: process.env.NODE_ENV === 'production',
        httpOnly: true,
        maxAge: 1000 * 60 * 60 * 24 * 7
      }
    }));
    console.log('✓ Session middleware configured with memory store (fallback)');
  }
}

// Session middleware - Use memory store (simple and reliable)
app.use(session({
  secret: process.env.SESSION_SECRET || 'your-secret-key-change-in-production',
  resave: true, // Save session even if unmodified
  saveUninitialized: true,
  name: 'dispensary.sid', // Custom session name
  cookie: {
    secure: false, // Changed to false - Heroku handles SSL termination
    httpOnly: true,
    sameSite: 'lax',
    maxAge: 1000 * 60 * 60 * 24 * 7
  }
}));
console.log('✓ Session middleware configured (memory store)');

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
const blogRoutes = require('./routes/blog');
const faqRoutes = require('./routes/faq');
const pagesRoutes = require('./routes/pages');
const sitemapRoutes = require('./routes/sitemap');
const apiRoutes = require('./routes/api');
const reviewRoutes = require('./routes/reviews');
const authRoutes = require('./routes/auth');
const adminRoutes = require('./routes/admin');
const leadRoutes = require('./routes/leads');
const languageRoutes = require('./routes/language');

// API routes FIRST - before any language/translation middleware
console.log('Registering API routes...');
app.use('/api', apiRoutes);
console.log('API routes registered at /api');
app.use('/api/reviews', reviewRoutes);
app.use('/auth', authRoutes);
app.use('/admin', adminRoutes);
app.use('/leads', leadRoutes);
console.log('All API/Auth routes registered');

// Language routes (catch /es/, /fr/, etc. and rewrite)
app.use('/', languageRoutes);

// Then language detection
app.use(detectLanguage);

// Then serve pre-translated cached pages (if available)
app.use(serveTranslatedPage);

// Then auto-translate middleware (translates on-the-fly if not cached)
app.use(autoTranslateMiddleware);

// Then normal routes
app.use('/', indexRoutes);
app.use('/dispensaries', dispensaryRoutes);
app.use('/dispensary', dispensaryRoutes); // Alternative singular route
app.use('/brands', brandsRoutes);
app.use('/blog', blogRoutes);
app.use('/faq', faqRoutes);
app.use('/', pagesRoutes);
app.use('/', sitemapRoutes);

// Near Me page route
app.get('/near-me', (req, res) => {
  res.render('near-me', {
    title: 'Dispensaries Near Me - Find Cannabis Dispensaries Nearby'
  });
});

// Search page route
app.get('/search', (req, res) => {
  res.render('search', {
    title: 'Search Dispensaries - Top Dispensaries 2026'
  });
});

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
