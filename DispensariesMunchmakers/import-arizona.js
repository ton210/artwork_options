require('dotenv').config();
const fs = require('fs');
const db = require('./src/config/database');
const googlePlaces = require('./src/services/googlePlaces');
const googleSearch = require('./src/services/googleSearch');
const axios = require('axios');
const slugify = require('slugify');

// Known franchise brands
const FRANCHISE_BRANDS = [
  'Zen Leaf', 'Curaleaf', 'Trulieve', 'MedMen', 'Cresco', 'Jars',
  'The Mint', 'Harvest', 'Nature\'s Wonder', 'Arizona Organix',
  'Nirvana Center', 'Sol Flower', 'The Giving Tree', 'Territory'
];

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

async function identifyBrand(dbaName) {
  // Check if it matches a known franchise
  for (const franchise of FRANCHISE_BRANDS) {
    if (dbaName.toLowerCase().includes(franchise.toLowerCase())) {
      return { name: franchise, isFranchise: true };
    }
  }
  // Otherwise, use the DBA name as the brand
  return { name: dbaName, isFranchise: false };
}

async function findOrCreateBrand(brandName, isFranchise) {
  const slug = slugify(brandName, { lower: true, strict: true });

  // Check if brand exists
  const existing = await db.query(
    'SELECT * FROM brands WHERE slug = $1',
    [slug]
  );

  if (existing.rows.length > 0) {
    return existing.rows[0];
  }

  // Create new brand
  const result = await db.query(
    `INSERT INTO brands (name, slug, is_franchise, location_count)
     VALUES ($1, $2, $3, 0)
     RETURNING *`,
    [brandName, slug, isFranchise]
  );

  console.log(`  ✓ Created brand: ${brandName} (Franchise: ${isFranchise})`);
  return result.rows[0];
}

async function searchAndEnrichDispensary(dispensaryInfo) {
  const { dbaName, address, city, zipCode, legalEntity } = dispensaryInfo;

  console.log(`\n=== Processing: ${dbaName} ===`);
  console.log(`Address: ${address}, ${city}, AZ ${zipCode}`);

  try {
    // Step 1: Identify/create brand
    const brandInfo = await identifyBrand(dbaName);
    const brand = await findOrCreateBrand(brandInfo.name, brandInfo.isFranchise);

    await delay(1000);

    // Step 2: Search Google Places
    const searchQuery = `${dbaName} ${address} ${city} Arizona dispensary`;
    console.log(`Searching: ${searchQuery}`);

    const searchResults = await googlePlaces.searchDispensaries(searchQuery);

    if (searchResults.results.length === 0) {
      console.log(`⚠️  No results found for ${dbaName}`);
      return null;
    }

    // Get the first result (most relevant)
    const place = searchResults.results[0];
    console.log(`Found: ${place.name} (confidence: high)`);

    await delay(1000);

    // Step 3: Get detailed place information
    const details = await googlePlaces.getPlaceDetails(place.place_id);
    const addressInfo = googlePlaces.parseAddressComponents(details.address_components);

    // Step 4: Get external listings
    await delay(1000);
    const externalListings = await googleSearch.searchForListing(
      details.name,
      addressInfo.city,
      addressInfo.state
    );

    // Step 5: Process photos
    const photos = [];
    if (details.photos && details.photos.length > 0) {
      for (let i = 0; i < Math.min(details.photos.length, 5); i++) {
        photos.push(await googlePlaces.getPhotoUrl(details.photos[i].photo_reference));
      }
    }

    const logoUrl = photos.length > 0 ? photos[0] : null;

    // Step 6: Find county
    const countyResult = await db.query(
      `SELECT id FROM counties
       WHERE state_id = (SELECT id FROM states WHERE abbreviation = 'AZ')
       AND LOWER(name) = LOWER($1)
       LIMIT 1`,
      [addressInfo.county || city]
    );

    const countyId = countyResult.rows[0]?.id || null;

    // Step 7: Create dispensary record
    const slug = slugify(`${details.name} ${addressInfo.city}`, { lower: true, strict: true });

    const dispensaryData = {
      google_place_id: details.place_id,
      name: details.name,
      slug: slug,
      brand_id: brand.id,
      address_street: addressInfo.street || address,
      city: addressInfo.city || city,
      county_id: countyId,
      zip: addressInfo.zip || zipCode.toString(),
      lat: details.geometry.location.lat,
      lng: details.geometry.location.lng,
      phone: details.formatted_phone_number || details.international_phone_number,
      website: details.website,
      logo_url: logoUrl,
      photos: photos,
      hours: details.opening_hours,
      google_rating: details.rating || 0,
      google_review_count: details.user_ratings_total || 0,
      external_listings: externalListings,
      is_active: true
    };

    // Check if already exists
    const existing = await db.query(
      'SELECT id FROM dispensaries WHERE google_place_id = $1',
      [details.place_id]
    );

    let dispensaryId;

    if (existing.rows.length > 0) {
      // Update existing
      await db.query(
        `UPDATE dispensaries SET
          name = $1, brand_id = $2, address_street = $3, city = $4, county_id = $5,
          zip = $6, lat = $7, lng = $8, phone = $9, website = $10,
          logo_url = $11, photos = $12, hours = $13, google_rating = $14,
          google_review_count = $15, external_listings = $16, updated_at = NOW()
         WHERE google_place_id = $17`,
        [
          dispensaryData.name, dispensaryData.brand_id, dispensaryData.address_street,
          dispensaryData.city, dispensaryData.county_id, dispensaryData.zip,
          dispensaryData.lat, dispensaryData.lng, dispensaryData.phone,
          dispensaryData.website, dispensaryData.logo_url, JSON.stringify(dispensaryData.photos),
          JSON.stringify(dispensaryData.hours), dispensaryData.google_rating,
          dispensaryData.google_review_count, JSON.stringify(dispensaryData.external_listings),
          details.place_id
        ]
      );
      dispensaryId = existing.rows[0].id;
      console.log(`✓ Updated: ${details.name} - Rating: ${details.rating}/5 (${details.user_ratings_total} reviews)`);
    } else {
      // Insert new
      const result = await db.query(
        `INSERT INTO dispensaries (
          google_place_id, name, slug, brand_id, address_street, city, county_id,
          zip, lat, lng, phone, website, logo_url, photos, hours,
          google_rating, google_review_count, external_listings, is_active
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19)
        RETURNING id`,
        [
          dispensaryData.google_place_id, dispensaryData.name, dispensaryData.slug,
          dispensaryData.brand_id, dispensaryData.address_street, dispensaryData.city,
          dispensaryData.county_id, dispensaryData.zip, dispensaryData.lat, dispensaryData.lng,
          dispensaryData.phone, dispensaryData.website, dispensaryData.logo_url,
          JSON.stringify(dispensaryData.photos), JSON.stringify(dispensaryData.hours),
          dispensaryData.google_rating, dispensaryData.google_review_count,
          JSON.stringify(dispensaryData.external_listings), dispensaryData.is_active
        ]
      );
      dispensaryId = result.rows[0].id;
      console.log(`✓ Added: ${details.name} - Rating: ${details.rating}/5 (${details.user_ratings_total} reviews)`);
    }

    // Update brand location count
    await db.query(
      `UPDATE brands SET
        location_count = (SELECT COUNT(*) FROM dispensaries WHERE brand_id = $1),
        average_rating = (SELECT AVG(google_rating) FROM dispensaries WHERE brand_id = $1),
        total_reviews = (SELECT SUM(google_review_count) FROM dispensaries WHERE brand_id = $1)
       WHERE id = $1`,
      [brand.id]
    );

    return { success: true, dispensaryId, brandId: brand.id };

  } catch (error) {
    console.error(`❌ Error processing ${dbaName}:`, error.message);
    return { success: false, error: error.message };
  }
}

async function importArizonaDispensaries() {
  console.log('Starting Arizona dispensaries import...\n');

  try {
    // Load the data
    const data = JSON.parse(fs.readFileSync('./arizona-dispensaries.json', 'utf8'));

    console.log(`Found ${data.length} dispensaries to process`);

    let added = 0;
    let updated = 0;
    let failed = 0;

    for (let i = 0; i < data.length; i++) {
      const record = data[i];

      const dispensaryInfo = {
        dbaName: record['DBA / Brand Name'],
        legalEntity: record['Legal Entity Name'],
        address: record.Address,
        city: record.City,
        zipCode: record['Zip Code'],
        type: record.Type
      };

      console.log(`\n[${i + 1}/${data.length}] Processing...`);

      const result = await searchAndEnrichDispensary(dispensaryInfo);

      if (result && result.success) {
        added++;
      } else {
        failed++;
      }

      // Rate limiting
      await delay(2000);
    }

    console.log('\n=== Import Summary ===');
    console.log(`Total processed: ${data.length}`);
    console.log(`Successfully added/updated: ${added}`);
    console.log(`Failed: ${failed}`);

    // Show brand summary
    const brands = await db.query(
      'SELECT name, location_count, average_rating, is_franchise FROM brands ORDER BY location_count DESC'
    );

    console.log('\n=== Brand Summary ===');
    brands.rows.forEach(brand => {
      const franchise = brand.is_franchise ? ' (Franchise)' : '';
      console.log(`${brand.name}${franchise}: ${brand.location_count} locations, Avg Rating: ${brand.average_rating?.toFixed(1) || 'N/A'}`);
    });

  } catch (error) {
    console.error('Import failed:', error);
  } finally {
    await db.pool.end();
  }
}

// Run import
importArizonaDispensaries();
