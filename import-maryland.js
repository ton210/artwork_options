require('dotenv').config();
const fs = require('fs');
const db = require('./src/config/database');
const googlePlaces = require('./src/services/googlePlaces');
const googleSearch = require('./src/services/googleSearch');
const slugify = require('slugify');

const FRANCHISE_BRANDS = ['Zen Leaf', 'Culta', 'Verilife', 'Green Goods', 'Liberty', 'Rise', 'Curaleaf'];

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function identifyBrand(name) {
  for (const franchise of FRANCHISE_BRANDS) {
    if (name.toLowerCase().includes(franchise.toLowerCase())) {
      return { name: franchise, isFranchise: true };
    }
  }
  return { name, isFranchise: false };
}

async function findOrCreateBrand(brandName, isFranchise) {
  const slug = slugify(brandName, { lower: true, strict: true });
  const existing = await db.query('SELECT * FROM brands WHERE slug = $1', [slug]);
  if (existing.rows.length > 0) return existing.rows[0];

  const result = await db.query(
    'INSERT INTO brands (name, slug, is_franchise) VALUES ($1, $2, $3) RETURNING *',
    [brandName, slug, isFranchise]
  );
  console.log(`  ✓ Created brand: ${brandName}`);
  return result.rows[0];
}

async function searchAndEnrichDispensary(info) {
  const { name, address, city, zipCode } = info;
  console.log(`\n=== Processing: ${name} ===`);
  console.log(`Address: ${address || 'N/A'}, ${city}, MD ${zipCode || ''}`);

  try {
    const brandInfo = await identifyBrand(name);
    const brand = await findOrCreateBrand(brandInfo.name, brandInfo.isFranchise);
    await delay(1000);

    const searchQuery = address
      ? `${name} ${address} ${city} Maryland dispensary`
      : `${name} ${city} Maryland dispensary`;

    const searchResults = await googlePlaces.searchDispensaries(searchQuery);
    if (searchResults.results.length === 0) {
      console.log(`⚠️  No results found`);
      return null;
    }

    const place = searchResults.results[0];
    console.log(`Found: ${place.name}`);
    await delay(1000);

    const details = await googlePlaces.getPlaceDetails(place.place_id);
    const addressInfo = googlePlaces.parseAddressComponents(details.address_components);
    await delay(1000);

    const externalListings = await googleSearch.searchForListing(details.name, addressInfo.city, addressInfo.state);

    const photos = [];
    if (details.photos && details.photos.length > 0) {
      for (let i = 0; i < Math.min(details.photos.length, 5); i++) {
        photos.push(await googlePlaces.getPhotoUrl(details.photos[i].photo_reference));
      }
    }

    const countyResult = await db.query(
      `SELECT id FROM counties WHERE state_id = (SELECT id FROM states WHERE abbreviation = 'MD')
       AND LOWER(name) = LOWER($1) LIMIT 1`,
      [addressInfo.county || city]
    );

    const slug = slugify(`${details.name} ${addressInfo.city}`, { lower: true, strict: true });

    const dispensaryData = {
      google_place_id: details.place_id,
      name: details.name, slug, brand_id: brand.id,
      address_street: addressInfo.street || address || '',
      city: addressInfo.city || city,
      county_id: countyResult.rows[0]?.id || null,
      zip: addressInfo.zip || zipCode || '',
      lat: details.geometry.location.lat,
      lng: details.geometry.location.lng,
      phone: details.formatted_phone_number || details.international_phone_number,
      website: details.website,
      logo_url: photos[0] || null,
      photos, hours: details.opening_hours,
      google_rating: details.rating || 0,
      google_review_count: details.user_ratings_total || 0,
      external_listings: externalListings,
      is_active: true
    };

    const existing = await db.query('SELECT id FROM dispensaries WHERE google_place_id = $1', [details.place_id]);

    if (existing.rows.length > 0) {
      await db.query(
        `UPDATE dispensaries SET name = $1, brand_id = $2, address_street = $3, city = $4, county_id = $5,
          zip = $6, lat = $7, lng = $8, phone = $9, website = $10, logo_url = $11, photos = $12,
          hours = $13, google_rating = $14, google_review_count = $15, external_listings = $16, updated_at = NOW()
         WHERE google_place_id = $17`,
        [dispensaryData.name, dispensaryData.brand_id, dispensaryData.address_street, dispensaryData.city,
         dispensaryData.county_id, dispensaryData.zip, dispensaryData.lat, dispensaryData.lng,
         dispensaryData.phone, dispensaryData.website, dispensaryData.logo_url, JSON.stringify(dispensaryData.photos),
         JSON.stringify(dispensaryData.hours), dispensaryData.google_rating, dispensaryData.google_review_count,
         JSON.stringify(dispensaryData.external_listings), details.place_id]
      );
      console.log(`✓ Updated: ${details.name} - ${details.rating}/5 (${details.user_ratings_total})`);
    } else {
      await db.query(
        `INSERT INTO dispensaries (google_place_id, name, slug, brand_id, address_street, city, county_id,
          zip, lat, lng, phone, website, logo_url, photos, hours, google_rating, google_review_count,
          external_listings, is_active)
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19)`,
        [dispensaryData.google_place_id, dispensaryData.name, dispensaryData.slug, dispensaryData.brand_id,
         dispensaryData.address_street, dispensaryData.city, dispensaryData.county_id, dispensaryData.zip,
         dispensaryData.lat, dispensaryData.lng, dispensaryData.phone, dispensaryData.website,
         dispensaryData.logo_url, JSON.stringify(dispensaryData.photos), JSON.stringify(dispensaryData.hours),
         dispensaryData.google_rating, dispensaryData.google_review_count,
         JSON.stringify(dispensaryData.external_listings), dispensaryData.is_active]
      );
      console.log(`✓ Added: ${details.name} - ${details.rating}/5 (${details.user_ratings_total})`);
    }

    await db.query(
      `UPDATE brands SET location_count = (SELECT COUNT(*) FROM dispensaries WHERE brand_id = $1),
        average_rating = (SELECT AVG(google_rating) FROM dispensaries WHERE brand_id = $1),
        total_reviews = (SELECT SUM(google_review_count) FROM dispensaries WHERE brand_id = $1)
       WHERE id = $1`,
      [brand.id]
    );

    return { success: true };
  } catch (error) {
    console.error(`❌ Error:`, error.message);
    return { success: false };
  }
}

async function importMarylandDispensaries() {
  console.log('Starting Maryland dispensaries import...\n');

  try {
    const csv = fs.readFileSync('./maryland-dispensaries.csv', 'utf8');
    const lines = csv.split('\n').filter(line => line.trim());

    const data = lines.slice(1).map(line => {
      const matches = line.match(/(".*?"|[^,]+)(?=\s*,|\s*$)/g);
      if (!matches || matches.length < 3) return null;

      return {
        name: matches[0]?.replace(/"/g, '').trim(),
        address: matches[1]?.replace(/"/g, '').trim(),
        city: matches[2]?.replace(/"/g, '').trim(),
        zipCode: matches[3]?.replace(/"/g, '').trim(),
        type: matches[4]?.replace(/"/g, '').trim()
      };
    }).filter(d => d && d.name);

    console.log(`Found ${data.length} Maryland dispensaries\n`);

    let added = 0, failed = 0;

    for (let i = 0; i < data.length; i++) {
      console.log(`[${i + 1}/${data.length}]`);
      const result = await searchAndEnrichDispensary(data[i]);
      if (result?.success) added++; else failed++;
      await delay(2000);
    }

    console.log('\n=== Maryland Import Summary ===');
    console.log(`Total: ${data.length}`);
    console.log(`Success: ${added}`);
    console.log(`Failed: ${failed}`);

  } catch (error) {
    console.error('Import failed:', error);
  } finally {
    await db.pool.end();
  }
}

importMarylandDispensaries();
