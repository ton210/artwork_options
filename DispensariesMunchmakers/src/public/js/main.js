// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then(registration => {
        console.log('Service Worker registered:', registration.scope);
      })
      .catch(error => {
        console.log('Service Worker registration failed:', error);
      });
  });
}

// Geolocation utilities
let userLocation = null;

function getUserLocation() {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Geolocation not supported'));
      return;
    }

    // Check if we have cached location (within last hour)
    const cached = localStorage.getItem('userLocation');
    if (cached) {
      const { lat, lng, timestamp } = JSON.parse(cached);
      if (Date.now() - timestamp < 3600000) { // 1 hour
        userLocation = { lat, lng };
        resolve(userLocation);
        return;
      }
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        userLocation = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        localStorage.setItem('userLocation', JSON.stringify({
          ...userLocation,
          timestamp: Date.now()
        }));
        resolve(userLocation);
      },
      (error) => {
        console.error('Geolocation error:', error);
        reject(error);
      },
      {
        enableHighAccuracy: false,
        timeout: 5000,
        maximumAge: 3600000
      }
    );
  });
}

function calculateDistance(lat1, lng1, lat2, lng2) {
  const R = 3959; // Radius of Earth in miles
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLng / 2) * Math.sin(dLng / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

// Track click events
async function trackClick(dispensaryId, eventType) {
  try {
    await fetch('/api/track/click', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ dispensaryId, eventType })
    });
  } catch (error) {
    console.error('Error tracking click:', error);
  }
}

// Add click tracking to all external links
document.addEventListener('DOMContentLoaded', function() {
  // Track phone clicks
  document.querySelectorAll('a[href^="tel:"]').forEach(link => {
    link.addEventListener('click', function() {
      const dispensaryId = this.dataset.dispensaryId;
      if (dispensaryId) {
        trackClick(dispensaryId, 'phone');
      }
    });
  });

  // Track website clicks
  document.querySelectorAll('.website-link').forEach(link => {
    link.addEventListener('click', function() {
      const dispensaryId = this.dataset.dispensaryId;
      if (dispensaryId) {
        trackClick(dispensaryId, 'website');
      }
    });
  });

  // Track directions clicks
  document.querySelectorAll('.directions-link').forEach(link => {
    link.addEventListener('click', function() {
      const dispensaryId = this.dataset.dispensaryId;
      if (dispensaryId) {
        trackClick(dispensaryId, 'directions');
      }
    });
  });
});
