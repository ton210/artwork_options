require('dotenv').config();
const db = require('../config/database');

async function addReviewsTables() {
  console.log('Creating reviews tables...\n');

  try {
    // Create reviews table
    await db.query(`
      CREATE TABLE IF NOT EXISTS reviews (
        id SERIAL PRIMARY KEY,
        dispensary_id INTEGER REFERENCES dispensaries(id) ON DELETE CASCADE,
        author_name VARCHAR(100) NOT NULL,
        author_email VARCHAR(255),
        rating INTEGER CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT NOT NULL,
        helpful_count INTEGER DEFAULT 0,
        not_helpful_count INTEGER DEFAULT 0,
        ip_hash VARCHAR(64) NOT NULL,
        is_verified BOOLEAN DEFAULT false,
        is_approved BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
      )
    `);
    console.log('✓ Created reviews table');

    // Create indexes for reviews
    await db.query(`
      CREATE INDEX IF NOT EXISTS idx_reviews_dispensary ON reviews(dispensary_id);
      CREATE INDEX IF NOT EXISTS idx_reviews_approved ON reviews(is_approved);
      CREATE INDEX IF NOT EXISTS idx_reviews_created ON reviews(created_at DESC);
    `);
    console.log('✓ Created reviews indexes');

    // Create review_helpfulness table
    await db.query(`
      CREATE TABLE IF NOT EXISTS review_helpfulness (
        id SERIAL PRIMARY KEY,
        review_id INTEGER REFERENCES reviews(id) ON DELETE CASCADE,
        ip_hash VARCHAR(64) NOT NULL,
        helpful BOOLEAN NOT NULL,
        created_at TIMESTAMP DEFAULT NOW(),
        UNIQUE(review_id, ip_hash)
      )
    `);
    console.log('✓ Created review_helpfulness table');

    // Add user review columns to dispensaries table
    await db.query(`
      ALTER TABLE dispensaries
      ADD COLUMN IF NOT EXISTS user_review_count INTEGER DEFAULT 0,
      ADD COLUMN IF NOT EXISTS user_average_rating DECIMAL(2,1) DEFAULT 0;
    `);
    console.log('✓ Added user review columns to dispensaries table');

    console.log('\n=== Reviews tables created successfully! ===');

  } catch (error) {
    console.error('Error creating reviews tables:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  addReviewsTables()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { addReviewsTables };
