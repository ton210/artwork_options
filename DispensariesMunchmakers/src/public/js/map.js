/**
 * Google Maps Integration
 * Handles interactive maps for state/county/dispensary pages
 */

let map;
let markers = [];
let infoWindow;

// Initialize map for county page
async function initCountyMap(stateSlug, countySlug) {
  const mapContainer = document.getElementById('map');
  if (!mapContainer) return;

  try {
    const response = await fetch(`/api/map/county/${stateSlug}/${countySlug}`);
    const data = await response.json();

    if (!data.success || data.dispensaries.length === 0) {
      mapContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No dispensaries with location data available</div>';
      return;
    }

    const center = calculateCenter(data.dispensaries);
    const zoom = calculateZoom(data.dispensaries.length);

    map = new google.maps.Map(mapContainer, {
      center,
      zoom,
      styles: getMapStyles()
    });

    infoWindow = new google.maps.InfoWindow();

    data.dispensaries.forEach(dispensary => {
      addMarker(dispensary);
    });

  } catch (error) {
    console.error('Error loading map:', error);
    mapContainer.innerHTML = '<div class="p-4 text-center text-gray-500">Map failed to load</div>';
  }
}

// Initialize map for state page
async function initStateMap(stateSlug) {
  const mapContainer = document.getElementById('map');
  if (!mapContainer) return;

  try {
    const response = await fetch(`/api/map/state/${stateSlug}`);
    const data = await response.json();

    if (!data.success || data.dispensaries.length === 0) {
      mapContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No dispensaries with location data available</div>';
      return;
    }

    const center = calculateCenter(data.dispensaries);
    const zoom = calculateZoom(data.dispensaries.length, 'state');

    map = new google.maps.Map(mapContainer, {
      center,
      zoom,
      styles: getMapStyles()
    });

    infoWindow = new google.maps.InfoWindow();

    // Use marker clustering for state maps (many dispensaries)
    const markerObjects = data.dispensaries.map(dispensary => createMarker(dispensary));

    if (typeof MarkerClusterer !== 'undefined' && data.dispensaries.length > 20) {
      new MarkerClusterer(map, markerObjects, {
        imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
      });
    } else {
      markerObjects.forEach(marker => marker.setMap(map));
    }

  } catch (error) {
    console.error('Error loading state map:', error);
    mapContainer.innerHTML = '<div class="p-4 text-center text-gray-500">Map failed to load</div>';
  }
}

// Initialize map for single dispensary page
async function initDispensaryMap(dispensaryId, lat, lng, name, address) {
  const mapContainer = document.getElementById('dispensary-map');
  if (!mapContainer) return;

  const location = { lat: parseFloat(lat), lng: parseFloat(lng) };

  map = new google.maps.Map(mapContainer, {
    center: location,
    zoom: 15,
    styles: getMapStyles()
  });

  const marker = new google.maps.Marker({
    position: location,
    map,
    title: name,
    icon: {
      url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
        <svg width="40" height="50" viewBox="0 0 40 50" xmlns="http://www.w3.org/2000/svg">
          <path d="M20 0C9 0 0 9 0 20c0 15 20 30 20 30s20-15 20-30C40 9 31 0 20 0z" fill="#16a34a"/>
          <circle cx="20" cy="20" r="8" fill="white"/>
        </svg>
      `),
      scaledSize: new google.maps.Size(40, 50)
    }
  });

  infoWindow = new google.maps.InfoWindow({
    content: `
      <div class="p-2">
        <h3 class="font-bold text-lg mb-1">${name}</h3>
        <p class="text-sm text-gray-600">${address}</p>
        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}"
           target="_blank"
           class="inline-block mt-2 text-blue-600 hover:underline text-sm">
          Get Directions →
        </a>
      </div>
    `
  });

  marker.addListener('click', () => {
    infoWindow.open(map, marker);
  });
}

// Helper: Add marker to map
function addMarker(dispensary) {
  const marker = createMarker(dispensary);
  marker.setMap(map);
  markers.push(marker);
}

// Helper: Create marker object
function createMarker(dispensary) {
  const marker = new google.maps.Marker({
    position: { lat: dispensary.lat, lng: dispensary.lng },
    title: dispensary.name,
    icon: {
      url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
        <svg width="30" height="40" viewBox="0 0 30 40" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 0C7 0 0 7 0 15c0 11 15 25 15 25s15-14 15-25C30 7 23 0 15 0z" fill="#16a34a"/>
          <circle cx="15" cy="15" r="6" fill="white"/>
        </svg>
      `),
      scaledSize: new google.maps.Size(30, 40)
    }
  });

  marker.addListener('click', () => {
    const rating = dispensary.rating ? `⭐ ${dispensary.rating}/5 (${dispensary.reviewCount || 0})` : '';

    infoWindow.setContent(`
      <div class="p-3" style="min-width: 200px;">
        <h3 class="font-bold text-base mb-1">${dispensary.name}</h3>
        ${rating ? `<div class="text-sm text-gray-600 mb-2">${rating}</div>` : ''}
        <p class="text-sm text-gray-600 mb-3">${dispensary.address}</p>
        <div class="flex gap-2">
          <a href="/dispensary/${dispensary.slug}"
             class="inline-block bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
            View Details
          </a>
          <a href="https://www.google.com/maps/dir/?api=1&destination=${dispensary.lat},${dispensary.lng}"
             target="_blank"
             class="inline-block bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
            Directions
          </a>
        </div>
      </div>
    `);
    infoWindow.open(map, marker);
  });

  return marker;
}

// Helper: Calculate map center from dispensaries
function calculateCenter(dispensaries) {
  if (dispensaries.length === 1) {
    return { lat: dispensaries[0].lat, lng: dispensaries[0].lng };
  }

  const sum = dispensaries.reduce((acc, d) => ({
    lat: acc.lat + d.lat,
    lng: acc.lng + d.lng
  }), { lat: 0, lng: 0 });

  return {
    lat: sum.lat / dispensaries.length,
    lng: sum.lng / dispensaries.length
  };
}

// Helper: Calculate appropriate zoom level
function calculateZoom(count, area = 'county') {
  if (count === 1) return 14;
  if (area === 'state') {
    if (count < 20) return 7;
    if (count < 50) return 6;
    return 5;
  }
  // County level
  if (count < 5) return 11;
  if (count < 15) return 10;
  if (count < 30) return 9;
  return 8;
}

// Helper: Custom map styles (clean, minimal)
function getMapStyles() {
  return [
    {
      featureType: 'poi',
      elementType: 'labels',
      stylers: [{ visibility: 'off' }]
    },
    {
      featureType: 'transit',
      elementType: 'labels',
      stylers: [{ visibility: 'off' }]
    }
  ];
}

// Make functions globally available
window.initCountyMap = initCountyMap;
window.initStateMap = initStateMap;
window.initDispensaryMap = initDispensaryMap;
