const db = require('../config/database');
const fs = require('fs');
const path = require('path');

async function runMigrations() {
  console.log('Starting database migrations...');

  try {
    // Create tables
    await db.query(`
      -- States table
      CREATE TABLE IF NOT EXISTS states (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        abbreviation CHAR(2) NOT NULL,
        created_at TIMESTAMP DEFAULT NOW()
      );

      -- Counties table
      CREATE TABLE IF NOT EXISTS counties (
        id SERIAL PRIMARY KEY,
        state_id INTEGER REFERENCES states(id) ON DELETE CASCADE,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT NOW(),
        UNIQUE(state_id, slug)
      );

      -- Dispensaries table
      CREATE TABLE IF NOT EXISTS dispensaries (
        id SERIAL PRIMARY KEY,
        google_place_id VARCHAR(255) UNIQUE,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        address_street VARCHAR(255),
        city VARCHAR(100),
        county_id INTEGER REFERENCES counties(id) ON DELETE SET NULL,
        zip VARCHAR(10),
        lat DECIMAL(10, 8),
        lng DECIMAL(11, 8),
        phone VARCHAR(20),
        website VARCHAR(500),
        logo_url VARCHAR(500),
        photos JSONB DEFAULT '[]',
        hours JSONB DEFAULT '{}',
        google_rating DECIMAL(2,1),
        google_review_count INTEGER DEFAULT 0,
        external_listings JSONB DEFAULT '{}',
        license_number VARCHAR(100),
        is_verified BOOLEAN DEFAULT false,
        is_active BOOLEAN DEFAULT true,
        data_completeness_score INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
      );

      -- Votes table
      CREATE TABLE IF NOT EXISTS votes (
        id SERIAL PRIMARY KEY,
        dispensary_id INTEGER REFERENCES dispensaries(id) ON DELETE CASCADE,
        vote_type SMALLINT NOT NULL CHECK (vote_type IN (1, -1)),
        ip_hash VARCHAR(64) NOT NULL,
        session_id VARCHAR(100),
        is_verified_vote BOOLEAN DEFAULT false,
        created_at TIMESTAMP DEFAULT NOW()
      );

      -- Page views table
      CREATE TABLE IF NOT EXISTS page_views (
        id SERIAL PRIMARY KEY,
        dispensary_id INTEGER REFERENCES dispensaries(id) ON DELETE CASCADE,
        ip_hash VARCHAR(64),
        referrer VARCHAR(500),
        user_agent VARCHAR(500),
        created_at TIMESTAMP DEFAULT NOW()
      );

      -- Click events table
      CREATE TABLE IF NOT EXISTS click_events (
        id SERIAL PRIMARY KEY,
        dispensary_id INTEGER REFERENCES dispensaries(id) ON DELETE CASCADE,
        event_type VARCHAR(50) NOT NULL CHECK (event_type IN ('website', 'phone', 'directions', 'claim')),
        ip_hash VARCHAR(64),
        created_at TIMESTAMP DEFAULT NOW()
      );

      -- Rankings table
      CREATE TABLE IF NOT EXISTS rankings (
        id SERIAL PRIMARY KEY,
        dispensary_id INTEGER REFERENCES dispensaries(id) ON DELETE CASCADE,
        location_type VARCHAR(20) NOT NULL CHECK (location_type IN ('state', 'county')),
        location_id INTEGER NOT NULL,
        composite_score DECIMAL(5,2) NOT NULL DEFAULT 0,
        rank INTEGER,
        previous_rank INTEGER,
        calculated_at TIMESTAMP DEFAULT NOW(),
        UNIQUE(dispensary_id, location_type, location_id)
      );

      -- Leads table
      CREATE TABLE IF NOT EXISTS leads (
        id SERIAL PRIMARY KEY,
        dispensary_id INTEGER REFERENCES dispensaries(id) ON DELETE SET NULL,
        dispensary_name VARCHAR(255),
        contact_name VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        message TEXT,
        source VARCHAR(50) NOT NULL CHECK (source IN ('listing_page', 'sidebar', 'claim', 'footer')),
        is_contacted BOOLEAN DEFAULT false,
        created_at TIMESTAMP DEFAULT NOW()
      );

      -- Scrape logs table
      CREATE TABLE IF NOT EXISTS scrape_logs (
        id SERIAL PRIMARY KEY,
        job_type VARCHAR(50) NOT NULL,
        location VARCHAR(100),
        dispensaries_found INTEGER DEFAULT 0,
        dispensaries_added INTEGER DEFAULT 0,
        dispensaries_updated INTEGER DEFAULT 0,
        errors JSONB DEFAULT '[]',
        started_at TIMESTAMP,
        completed_at TIMESTAMP,
        status VARCHAR(20) DEFAULT 'running' CHECK (status IN ('running', 'completed', 'failed'))
      );

      -- Create indexes for performance
      CREATE INDEX IF NOT EXISTS idx_dispensaries_county ON dispensaries(county_id);
      CREATE INDEX IF NOT EXISTS idx_dispensaries_active ON dispensaries(is_active);
      CREATE INDEX IF NOT EXISTS idx_dispensaries_slug ON dispensaries(slug);
      CREATE INDEX IF NOT EXISTS idx_dispensaries_google_place_id ON dispensaries(google_place_id);

      CREATE INDEX IF NOT EXISTS idx_votes_dispensary ON votes(dispensary_id);
      CREATE INDEX IF NOT EXISTS idx_votes_created ON votes(created_at);
      CREATE INDEX IF NOT EXISTS idx_votes_ip_hash ON votes(ip_hash);
      CREATE UNIQUE INDEX IF NOT EXISTS idx_votes_unique_per_day ON votes(dispensary_id, ip_hash, DATE(created_at));

      CREATE INDEX IF NOT EXISTS idx_page_views_dispensary ON page_views(dispensary_id);
      CREATE INDEX IF NOT EXISTS idx_page_views_created ON page_views(created_at);

      CREATE INDEX IF NOT EXISTS idx_click_events_dispensary ON click_events(dispensary_id);
      CREATE INDEX IF NOT EXISTS idx_click_events_type ON click_events(event_type);

      CREATE INDEX IF NOT EXISTS idx_rankings_location ON rankings(location_type, location_id);
      CREATE INDEX IF NOT EXISTS idx_rankings_score ON rankings(composite_score DESC);
      CREATE INDEX IF NOT EXISTS idx_rankings_dispensary ON rankings(dispensary_id);

      CREATE INDEX IF NOT EXISTS idx_leads_created ON leads(created_at);
      CREATE INDEX IF NOT EXISTS idx_leads_contacted ON leads(is_contacted);

      CREATE INDEX IF NOT EXISTS idx_counties_state ON counties(state_id);
      CREATE INDEX IF NOT EXISTS idx_states_slug ON states(slug);
      CREATE INDEX IF NOT EXISTS idx_counties_slug ON counties(slug);
    `);

    console.log('✓ All tables and indexes created successfully');

    // Create trigger for updating updated_at timestamp
    await db.query(`
      CREATE OR REPLACE FUNCTION update_updated_at_column()
      RETURNS TRIGGER AS $$
      BEGIN
        NEW.updated_at = NOW();
        RETURN NEW;
      END;
      $$ language 'plpgsql';

      DROP TRIGGER IF EXISTS update_dispensaries_updated_at ON dispensaries;

      CREATE TRIGGER update_dispensaries_updated_at
        BEFORE UPDATE ON dispensaries
        FOR EACH ROW
        EXECUTE FUNCTION update_updated_at_column();
    `);

    console.log('✓ Triggers created successfully');
    console.log('✓ Database migration completed!');

  } catch (error) {
    console.error('Migration failed:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

// Run migrations if called directly
if (require.main === module) {
  runMigrations()
    .then(() => {
      console.log('Migration script finished');
      process.exit(0);
    })
    .catch((err) => {
      console.error('Migration script failed:', err);
      process.exit(1);
    });
}

module.exports = { runMigrations };
