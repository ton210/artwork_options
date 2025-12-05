const axios = require('axios');
const config = require('../config/google');

class GooglePlacesService {
  constructor() {
    this.baseUrl = config.placesBaseUrl;
    this.apiKey = config.placesApiKey;
    this.geocodingBaseUrl = config.geocodingBaseUrl;
  }

  async searchDispensaries(location, radius = 50000) {
    try {
      const response = await axios.get(`${this.baseUrl}/textsearch/json`, {
        params: {
          query: `cannabis dispensary in ${location}`,
          key: this.apiKey,
          type: 'store'
        }
      });

      if (response.data.status !== 'OK' && response.data.status !== 'ZERO_RESULTS') {
        throw new Error(`Google Places API error: ${response.data.status}`);
      }

      return {
        results: response.data.results || [],
        nextPageToken: response.data.next_page_token
      };
    } catch (error) {
      console.error('Error searching dispensaries:', error.message);
      throw error;
    }
  }

  async getPlaceDetails(placeId) {
    try {
      const response = await axios.get(`${this.baseUrl}/details/json`, {
        params: {
          place_id: placeId,
          key: this.apiKey,
          fields: [
            'place_id',
            'name',
            'formatted_address',
            'address_components',
            'geometry',
            'formatted_phone_number',
            'international_phone_number',
            'website',
            'opening_hours',
            'photos',
            'rating',
            'user_ratings_total',
            'reviews',
            'types',
            'business_status',
            'url'
          ].join(',')
        }
      });

      if (response.data.status !== 'OK') {
        throw new Error(`Place Details API error: ${response.data.status}`);
      }

      return response.data.result;
    } catch (error) {
      console.error('Error fetching place details:', error.message);
      throw error;
    }
  }

  async getPhotoUrl(photoReference, maxWidth = 400) {
    return `${this.baseUrl}/photo?maxwidth=${maxWidth}&photo_reference=${photoReference}&key=${this.apiKey}`;
  }

  async geocodeAddress(address) {
    try {
      const response = await axios.get(`${this.geocodingBaseUrl}/json`, {
        params: {
          address: address,
          key: this.apiKey
        }
      });

      if (response.data.status !== 'OK') {
        throw new Error(`Geocoding API error: ${response.data.status}`);
      }

      return response.data.results[0];
    } catch (error) {
      console.error('Error geocoding address:', error.message);
      throw error;
    }
  }

  parseAddressComponents(addressComponents) {
    const parsed = {
      street: '',
      city: '',
      county: '',
      state: '',
      stateAbbr: '',
      zip: '',
      country: ''
    };

    addressComponents.forEach(component => {
      const types = component.types;

      if (types.includes('street_number')) {
        parsed.street = component.long_name + ' ';
      }
      if (types.includes('route')) {
        parsed.street += component.long_name;
      }
      if (types.includes('locality')) {
        parsed.city = component.long_name;
      }
      if (types.includes('administrative_area_level_2')) {
        parsed.county = component.long_name.replace(' County', '');
      }
      if (types.includes('administrative_area_level_1')) {
        parsed.state = component.long_name;
        parsed.stateAbbr = component.short_name;
      }
      if (types.includes('postal_code')) {
        parsed.zip = component.long_name;
      }
      if (types.includes('country')) {
        parsed.country = component.long_name;
      }
    });

    return parsed;
  }

  async nearbySearch(lat, lng, radius = 50000) {
    try {
      const response = await axios.get(`${this.baseUrl}/nearbysearch/json`, {
        params: {
          location: `${lat},${lng}`,
          radius: radius,
          keyword: 'cannabis dispensary',
          key: this.apiKey
        }
      });

      if (response.data.status !== 'OK' && response.data.status !== 'ZERO_RESULTS') {
        throw new Error(`Nearby Search API error: ${response.data.status}`);
      }

      return {
        results: response.data.results || [],
        nextPageToken: response.data.next_page_token
      };
    } catch (error) {
      console.error('Error in nearby search:', error.message);
      throw error;
    }
  }

  async getNextPage(nextPageToken) {
    if (!nextPageToken) {
      return { results: [], nextPageToken: null };
    }

    // Google requires a short delay before using next_page_token
    await new Promise(resolve => setTimeout(resolve, 2000));

    try {
      const response = await axios.get(`${this.baseUrl}/textsearch/json`, {
        params: {
          pagetoken: nextPageToken,
          key: this.apiKey
        }
      });

      if (response.data.status !== 'OK' && response.data.status !== 'ZERO_RESULTS') {
        throw new Error(`Next Page API error: ${response.data.status}`);
      }

      return {
        results: response.data.results || [],
        nextPageToken: response.data.next_page_token
      };
    } catch (error) {
      console.error('Error fetching next page:', error.message);
      throw error;
    }
  }

  isValidDispensary(place) {
    // Filter out closed businesses
    if (place.business_status === 'CLOSED_PERMANENTLY' || place.business_status === 'CLOSED_TEMPORARILY') {
      return false;
    }

    // Check if it's actually a dispensary
    const name = place.name.toLowerCase();
    const types = place.types || [];

    const dispensaryKeywords = ['dispensary', 'cannabis', 'marijuana', 'weed', 'collective'];
    const hasKeyword = dispensaryKeywords.some(keyword => name.includes(keyword));

    return hasKeyword || types.includes('store');
  }

  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

module.exports = new GooglePlacesService();
