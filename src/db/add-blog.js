require('dotenv').config();
const db = require('../config/database');

async function addBlogTables() {
  console.log('Creating blog tables...\n');

  try {
    // Create blog_posts table
    await db.query(`
      CREATE TABLE IF NOT EXISTS blog_posts (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        excerpt TEXT,
        content TEXT NOT NULL,
        author VARCHAR(100) DEFAULT 'Admin',
        featured_image VARCHAR(500),
        meta_description TEXT,
        meta_keywords JSONB DEFAULT '[]',
        category VARCHAR(50) DEFAULT 'General',
        tags JSONB DEFAULT '[]',
        view_count INTEGER DEFAULT 0,
        is_published BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
      )
    `);
    console.log('✓ Created blog_posts table');

    // Create indexes
    await db.query(`
      CREATE INDEX IF NOT EXISTS idx_blog_slug ON blog_posts(slug);
      CREATE INDEX IF NOT EXISTS idx_blog_published ON blog_posts(is_published);
      CREATE INDEX IF NOT EXISTS idx_blog_category ON blog_posts(category);
      CREATE INDEX IF NOT EXISTS idx_blog_created ON blog_posts(created_at DESC);
    `);
    console.log('✓ Created blog indexes');

    console.log('\n=== Blog tables created successfully! ===');

  } catch (error) {
    console.error('Error creating blog tables:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  addBlogTables()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { addBlogTables };
