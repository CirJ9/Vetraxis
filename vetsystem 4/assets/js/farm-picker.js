/* Farm location picker. Keep map setup and click handling here for easy debugging. */
(() => {
  'use strict';
  const mapElement = document.getElementById('pickMap');
  if (!mapElement) return;
  if (typeof L === 'undefined') { console.error('[VetSystem] Leaflet did not load. Check internet/CDN access.'); return; }
  const map = L.map(mapElement).setView([14.3181, 120.7680], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
  let marker = null;
  map.on('click', (event) => {
    document.getElementById('latitude').value = event.latlng.lat.toFixed(7);
    document.getElementById('longitude').value = event.latlng.lng.toFixed(7);
    if (marker) map.removeLayer(marker);
    marker = L.marker(event.latlng).addTo(map).bindPopup('Selected farm location').openPopup();
  });
  window.setTimeout(() => map.invalidateSize(), 250);
})();
