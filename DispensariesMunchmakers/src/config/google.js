require('dotenv').config();

module.exports = {
  placesApiKey: process.env.GOOGLE_PLACES_API_KEY,
  customSearchApiKey: process.env.GOOGLE_CUSTOM_SEARCH_API_KEY,
  searchEngineId: process.env.GOOGLE_SEARCH_ENGINE_ID,

  // API endpoints
  placesBaseUrl: 'https://maps.googleapis.com/maps/api/place',
  geocodingBaseUrl: 'https://maps.googleapis.com/maps/api/geocode',
  customSearchBaseUrl: 'https://www.googleapis.com/customsearch/v1',

  // Rate limiting
  scrapeDelayMs: parseInt(process.env.SCRAPE_DELAY_MS) || 1000,
  maxResultsPerLocation: parseInt(process.env.MAX_RESULTS_PER_LOCATION) || 60,

  // Validation
  validate() {
    const required = [
      'GOOGLE_PLACES_API_KEY',
      'GOOGLE_CUSTOM_SEARCH_API_KEY',
      'GOOGLE_SEARCH_ENGINE_ID'
    ];

    const missing = required.filter(key => !process.env[key]);

    if (missing.length > 0) {
      throw new Error(`Missing required Google API configuration: ${missing.join(', ')}`);
    }

    return true;
  }
};
