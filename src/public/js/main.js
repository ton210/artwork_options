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

// Geolocation utilities are defined in map.js

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
