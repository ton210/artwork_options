require('dotenv').config();
const db = require('../config/database');

async function addUsersAndClaims() {
  console.log('Creating users and business claims tables...\n');

  try {
    // Create users table
    await db.query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        is_verified BOOLEAN DEFAULT false,
        verification_token VARCHAR(255),
        reset_token VARCHAR(255),
        reset_token_expires TIMESTAMP,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
      )
    `);
    console.log('✓ Created users table');

    await db.query(`
      CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
      CREATE INDEX IF NOT EXISTS idx_users_verification_token ON users(verification_token);
    `);
    console.log('✓ Created users indexes');

    // Create user_favorites table
    await db.query(`
      CREATE TABLE IF NOT EXISTS user_favorites (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        dispensary_id INTEGER REFERENCES dispensaries(id) ON DELETE CASCADE,
        created_at TIMESTAMP DEFAULT NOW(),
        UNIQUE(user_id, dispensary_id)
      )
    `);
    console.log('✓ Created user_favorites table');

    // Create business_claims table
    await db.query(`
      CREATE TABLE IF NOT EXISTS business_claims (
        id SERIAL PRIMARY KEY,
        dispensary_id INTEGER REFERENCES dispensaries(id) ON DELETE CASCADE,
        user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
        contact_name VARCHAR(100) NOT NULL,
        contact_email VARCHAR(255) NOT NULL,
        contact_phone VARCHAR(50),
        verification_method VARCHAR(50) DEFAULT 'email',
        verification_code VARCHAR(100),
        is_verified BOOLEAN DEFAULT false,
        is_approved BOOLEAN DEFAULT false,
        admin_notes TEXT,
        claimed_at TIMESTAMP,
        verified_at TIMESTAMP,
        approved_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
      )
    `);
    console.log('✓ Created business_claims table');

    await db.query(`
      CREATE INDEX IF NOT EXISTS idx_claims_dispensary ON business_claims(dispensary_id);
      CREATE INDEX IF NOT EXISTS idx_claims_email ON business_claims(contact_email);
      CREATE INDEX IF NOT EXISTS idx_claims_status ON business_claims(is_verified, is_approved);
    `);
    console.log('✓ Created business_claims indexes');

    // Add claimed status to dispensaries table
    await db.query(`
      ALTER TABLE dispensaries
      ADD COLUMN IF NOT EXISTS is_claimed BOOLEAN DEFAULT false,
      ADD COLUMN IF NOT EXISTS claimed_by_user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
      ADD COLUMN IF NOT EXISTS claimed_at TIMESTAMP;
    `);
    console.log('✓ Added claim columns to dispensaries table');

    console.log('\n=== Users and business claims tables created successfully! ===');

  } catch (error) {
    console.error('Error creating tables:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  addUsersAndClaims()
    .then(() => process.exit(0))
    .catch(err => {
      console.error(err);
      process.exit(1);
    });
}

module.exports = { addUsersAndClaims };
