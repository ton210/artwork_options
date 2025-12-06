const db = require('../config/database');

async function addBrandsTable() {
  console.log('Adding brands table to database...');

  try {
    await db.query(`
      -- Brands table (for franchise/multi-location brands)
      CREATE TABLE IF NOT EXISTS brands (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) UNIQUE NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        logo_url VARCHAR(500),
        website VARCHAR(500),
        description TEXT,
        location_count INTEGER DEFAULT 0,
        average_rating DECIMAL(2,1),
        total_reviews INTEGER DEFAULT 0,
        is_franchise BOOLEAN DEFAULT false,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
      );

      -- Add brand_id to dispensaries table
      ALTER TABLE dispensaries
      ADD COLUMN IF NOT EXISTS brand_id INTEGER REFERENCES brands(id) ON DELETE SET NULL;

      -- Create indexes
      CREATE INDEX IF NOT EXISTS idx_dispensaries_brand ON dispensaries(brand_id);
      CREATE INDEX IF NOT EXISTS idx_brands_slug ON brands(slug);
      CREATE INDEX IF NOT EXISTS idx_brands_franchise ON brands(is_franchise);

      -- Create trigger for updating brand updated_at
      CREATE OR REPLACE FUNCTION update_brand_updated_at()
      RETURNS TRIGGER AS $$
      BEGIN
        NEW.updated_at = NOW();
        RETURN NEW;
      END;
      $$ language 'plpgsql';

      DROP TRIGGER IF EXISTS update_brands_updated_at ON brands;

      CREATE TRIGGER update_brands_updated_at
        BEFORE UPDATE ON brands
        FOR EACH ROW
        EXECUTE FUNCTION update_brand_updated_at();
    `);

    console.log('✓ Brands table and indexes created successfully');

  } catch (error) {
    console.error('Migration failed:', error);
    throw error;
  } finally {
    await db.pool.end();
  }
}

if (require.main === module) {
  addBrandsTable()
    .then(() => {
      console.log('Brands migration completed');
      process.exit(0);
    })
    .catch((err) => {
      console.error('Brands migration failed:', err);
      process.exit(1);
    });
}

module.exports = { addBrandsTable };
