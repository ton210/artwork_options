const axios = require('axios');
const config = require('../config/google');

class GoogleSearchService {
  constructor() {
    this.apiKey = config.customSearchApiKey;
    this.searchEngineId = config.searchEngineId;
    this.baseUrl = config.customSearchBaseUrl;
  }

  async searchForListing(dispensaryName, city, state) {
    try {
      const queries = [
        `${dispensaryName} ${city} ${state} Leafly`,
        `${dispensaryName} ${city} ${state} Weedmaps`,
        `${dispensaryName} ${city} dispensary reviews`
      ];

      const externalListings = {
        leafly: null,
        weedmaps: null,
        other: []
      };

      for (const query of queries) {
        await this.delay(500); // Rate limit

        const response = await axios.get(this.baseUrl, {
          params: {
            key: this.apiKey,
            cx: this.searchEngineId,
            q: query,
            num: 5
          }
        });

        if (response.data.items) {
          response.data.items.forEach(item => {
            const link = item.link.toLowerCase();

            if (link.includes('leafly.com') && !externalListings.leafly) {
              externalListings.leafly = {
                url: item.link,
                title: item.title,
                snippet: item.snippet
              };
            } else if (link.includes('weedmaps.com') && !externalListings.weedmaps) {
              externalListings.weedmaps = {
                url: item.link,
                title: item.title,
                snippet: item.snippet
              };
            } else if (!link.includes('google.com') &&
                       !link.includes('yelp.com') &&
                       externalListings.other.length < 3) {
              externalListings.other.push({
                url: item.link,
                title: item.title,
                snippet: item.snippet
              });
            }
          });
        }
      }

      return externalListings;
    } catch (error) {
      console.error('Error searching for external listings:', error.message);
      return { leafly: null, weedmaps: null, other: [] };
    }
  }

  async searchForMenu(dispensaryName, city, state) {
    try {
      const query = `${dispensaryName} ${city} ${state} menu prices`;

      const response = await axios.get(this.baseUrl, {
        params: {
          key: this.apiKey,
          cx: this.searchEngineId,
          q: query,
          num: 3
        }
      });

      if (response.data.items && response.data.items.length > 0) {
        return response.data.items.map(item => ({
          url: item.link,
          title: item.title,
          snippet: item.snippet
        }));
      }

      return [];
    } catch (error) {
      console.error('Error searching for menu:', error.message);
      return [];
    }
  }

  async searchForLicense(dispensaryName, state) {
    try {
      const query = `${dispensaryName} ${state} cannabis license number`;

      const response = await axios.get(this.baseUrl, {
        params: {
          key: this.apiKey,
          cx: this.searchEngineId,
          q: query,
          num: 3
        }
      });

      if (response.data.items && response.data.items.length > 0) {
        // Try to extract license number from snippets
        for (const item of response.data.items) {
          const licenseMatch = item.snippet.match(/license[:\s]+([A-Z0-9-]+)/i);
          if (licenseMatch) {
            return licenseMatch[1];
          }
        }
      }

      return null;
    } catch (error) {
      console.error('Error searching for license:', error.message);
      return null;
    }
  }

  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

module.exports = new GoogleSearchService();
