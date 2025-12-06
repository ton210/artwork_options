require('dotenv').config();
const googlePlaces = require('./src/services/googlePlaces');
const googleSearch = require('./src/services/googleSearch');

async function testScraping() {
  console.log('Testing Dispensary Scraping...\n');

  try {
    // Test 1: Search for dispensaries in a legal state
    console.log('=== Test 1: Searching for dispensaries in Los Angeles, CA ===');
    const searchResults = await googlePlaces.searchDispensaries('Los Angeles, California');

    console.log(`✅ Found ${searchResults.results.length} dispensaries`);

    if (searchResults.results.length > 0) {
      const firstResult = searchResults.results[0];
      console.log('\nSample Dispensary:');
      console.log('- Name:', firstResult.name);
      console.log('- Address:', firstResult.formatted_address);
      console.log('- Rating:', firstResult.rating || 'N/A');
      console.log('- Total Ratings:', firstResult.user_ratings_total || 'N/A');

      // Test 2: Get place details
      if (firstResult.place_id) {
        console.log('\n=== Test 2: Getting detailed information ===');
        const details = await googlePlaces.getPlaceDetails(firstResult.place_id);
        console.log('✅ Got detailed information');
        console.log('- Phone:', details.formatted_phone_number || 'N/A');
        console.log('- Website:', details.website || 'N/A');
        console.log('- Business Status:', details.business_status || 'N/A');

        // Test 3: Search for external listings
        if (details.address_components) {
          const parsed = googlePlaces.parseAddressComponents(details.address_components);
          console.log('\n=== Test 3: Searching for external listings ===');
          const listings = await googleSearch.searchForListing(
            details.name,
            parsed.city,
            parsed.state
          );
          console.log('✅ External listings found:');
          console.log('- Leafly:', listings.leafly ? '✓' : '✗');
          console.log('- Weedmaps:', listings.weedmaps ? '✓' : '✗');
          console.log('- Other sources:', listings.other.length);
        }
      }
    }

    console.log('\n🎉 All scraping tests passed!');
    console.log('\n✅ The application is ready to start building the database!');

  } catch (error) {
    console.error('❌ Error during scraping test:', error.message);
    if (error.response) {
      console.error('Response:', error.response.data);
    }
  }
}

testScraping();
