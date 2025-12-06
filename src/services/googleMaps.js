/**
 * Google Maps Service
 * Handles map URL generation, distance calculations, and location utilities
 */

class GoogleMapsService {
  constructor(apiKey) {
    this.apiKey = apiKey;
  }

  /**
   * Generate Google Maps directions URL
   */
  generateDirectionsUrl(lat, lng, destinationName = '') {
    const destination = destinationName
      ? encodeURIComponent(destinationName)
      : `${lat},${lng}`;
    return `https://www.google.com/maps/dir/?api=1&destination=${destination}`;
  }

  /**
   * Generate static map image URL
   */
  generateStaticMapUrl(lat, lng, zoom = 15, width = 600, height = 400) {
    const markers = `markers=color:green|${lat},${lng}`;
    const size = `size=${width}x${height}`;
    const center = `center=${lat},${lng}`;
    const zoomParam = `zoom=${zoom}`;

    return `https://maps.googleapis.com/maps/api/staticmap?${center}&${zoomParam}&${size}&${markers}&key=${this.apiKey}`;
  }

  /**
   * Calculate distance between two coordinates using Haversine formula
   * Returns distance in miles
   */
  getDistanceFromCoordinates(lat1, lng1, lat2, lng2) {
    const R = 3959; // Radius of Earth in miles
    const dLat = this.toRadians(lat2 - lat1);
    const dLng = this.toRadians(lng2 - lng1);

    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
      Math.sin(dLng / 2) * Math.sin(dLng / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;

    return Math.round(distance * 10) / 10; // Round to 1 decimal
  }

  /**
   * Convert degrees to radians
   */
  toRadians(degrees) {
    return degrees * (Math.PI / 180);
  }

  /**
   * Calculate center point for multiple locations (for map centering)
   */
  getCenterPoint(locations) {
    if (!locations || locations.length === 0) {
      return { lat: 39.8283, lng: -98.5795 }; // Center of USA
    }

    if (locations.length === 1) {
      return { lat: locations[0].lat, lng: locations[0].lng };
    }

    const total = locations.reduce((acc, loc) => ({
      lat: acc.lat + parseFloat(loc.lat),
      lng: acc.lng + parseFloat(loc.lng)
    }), { lat: 0, lng: 0 });

    return {
      lat: total.lat / locations.length,
      lng: total.lng / locations.length
    };
  }

  /**
   * Calculate appropriate zoom level based on dispensary density
   */
  getAppropriateZoom(dispensaryCount, area = 'county') {
    if (dispensaryCount === 1) return 14;
    if (area === 'state') {
      if (dispensaryCount < 20) return 7;
      if (dispensaryCount < 50) return 6;
      return 5;
    }
    // County level
    if (dispensaryCount < 5) return 11;
    if (dispensaryCount < 15) return 10;
    if (dispensaryCount < 30) return 9;
    return 8;
  }

  /**
   * Format distance for display
   */
  formatDistance(miles) {
    if (miles < 1) {
      return `${Math.round(miles * 5280)} ft`;
    }
    return `${miles.toFixed(1)} mi`;
  }
}

// Export singleton instance
const apiKey = process.env.GOOGLE_PLACES_API_KEY || process.env.GOOGLE_MAPS_API_KEY;
module.exports = new GoogleMapsService(apiKey);
