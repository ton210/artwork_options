require('dotenv').config();
const axios = require('axios');

async function testGooglePlacesAPI() {
  console.log('\n=== Testing Google Places API ===');
  const apiKey = process.env.GOOGLE_PLACES_API_KEY;

  if (!apiKey) {
    console.error('❌ GOOGLE_PLACES_API_KEY not found in .env');
    return false;
  }

  console.log('API Key:', apiKey.substring(0, 10) + '...');
  console.log('API Key length:', apiKey.length);
  console.log('Full API Key:', apiKey);

  try {
    // Simple text search for dispensaries in California
    const response = await axios.get('https://maps.googleapis.com/maps/api/place/textsearch/json', {
      params: {
        query: 'restaurant in New York',
        key: apiKey
      }
    });

    console.log('Status:', response.data.status);

    if (response.data.status === 'OK') {
      console.log('✅ Google Places API is working!');
      console.log(`Found ${response.data.results.length} results`);
      if (response.data.results.length > 0) {
        console.log('Sample result:', response.data.results[0].name);
      }
      return true;
    } else if (response.data.status === 'ZERO_RESULTS') {
      console.log('✅ API key works, but no results found (this is OK)');
      return true;
    } else {
      console.log('❌ API Error:', response.data.status);
      if (response.data.error_message) {
        console.log('Error message:', response.data.error_message);
      }
      return false;
    }
  } catch (error) {
    console.error('❌ Error testing Places API:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
    }
    return false;
  }
}

async function testGoogleCustomSearchAPI() {
  console.log('\n=== Testing Google Custom Search API ===');
  const apiKey = process.env.GOOGLE_CUSTOM_SEARCH_API_KEY;
  const searchEngineId = process.env.GOOGLE_SEARCH_ENGINE_ID;

  if (!apiKey) {
    console.error('❌ GOOGLE_CUSTOM_SEARCH_API_KEY not found in .env');
    return false;
  }

  if (!searchEngineId) {
    console.error('❌ GOOGLE_SEARCH_ENGINE_ID not found in .env');
    return false;
  }

  console.log('API Key:', apiKey.substring(0, 10) + '...');
  console.log('Search Engine ID:', searchEngineId);

  try {
    const response = await axios.get('https://www.googleapis.com/customsearch/v1', {
      params: {
        key: apiKey,
        cx: searchEngineId,
        q: 'cannabis dispensary',
        num: 3
      }
    });

    if (response.data.items) {
      console.log('✅ Google Custom Search API is working!');
      console.log(`Found ${response.data.items.length} results`);
      if (response.data.items.length > 0) {
        console.log('Sample result:', response.data.items[0].title);
      }
      return true;
    } else {
      console.log('⚠️ API works but no results found');
      return true;
    }
  } catch (error) {
    console.error('❌ Error testing Custom Search API:', error.message);
    if (error.response) {
      console.error('Response status:', error.response.status);
      console.error('Response data:', error.response.data);
    }
    return false;
  }
}

async function runTests() {
  console.log('Starting API Tests...\n');

  const placesResult = await testGooglePlacesAPI();
  const searchResult = await testGoogleCustomSearchAPI();

  console.log('\n=== Test Summary ===');
  console.log('Google Places API:', placesResult ? '✅ PASS' : '❌ FAIL');
  console.log('Google Custom Search API:', searchResult ? '✅ PASS' : '❌ FAIL');

  if (placesResult && searchResult) {
    console.log('\n🎉 All API keys are working correctly!');
  } else {
    console.log('\n⚠️ Some API keys need attention');
  }
}

runTests().catch(console.error);
