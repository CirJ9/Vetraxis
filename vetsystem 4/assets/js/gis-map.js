/*
 * VetSystem GIS alert logic.
 * Easy-to-change settings are grouped at the top of this file.
 * Yellow neighboring-barangay alert is proximity-based: it highlights farms
 * in a DIFFERENT barangay within ALERT_RADIUS_METERS of ANY critical farm.
 * Critical means 8+ deaths in the latest monitoring record. It does not prove
 * that two barangays share a legal boundary or that disease has spread.
 */
(() => {
  'use strict';

  const CONFIG = {
    mapCenter: [14.3181, 120.7680],
    initialZoom: 12,
    criticalDeaths: 8,
    warningDeaths: 4,
    defaultAlertRadiusMeters: 2000,
    colors: { good: '#23965a', warning: '#e5b323', critical: '#d94343', neighbor: '#f2c744' }
  };

  const farms = Array.isArray(window.VETSYSTEM_FARM_POINTS) ? window.VETSYSTEM_FARM_POINTS : [];
  const mapNode = document.getElementById('gisMap');
  if (!mapNode) return;
  if (typeof L === 'undefined') { console.error('[VetSystem GIS] Leaflet is unavailable.'); mapNode.textContent = 'Map library unavailable. Check your internet connection.'; return; }

  const map = L.map(mapNode).setView(CONFIG.mapCenter, CONFIG.initialZoom);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
  const layerGroup = L.featureGroup().addTo(map);
  const searchInput = document.getElementById('ownerSearch');
  const resultCount = document.getElementById('resultCount');
  const alertSummary = document.getElementById('alertSummary');
  const radiusInput = document.getElementById('alertRadius');
  const radiusValue = document.getElementById('radiusValue');

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[char]));
  const normalize = value => String(value ?? '').trim().toLocaleLowerCase();
  // Treat every farm with 8+ latest-record deaths as a critical alert source.
  // This ensures a nearby farm is still highlighted even if the source's animal
  // type was entered with a different label (or is not Poultry).
  const isCriticalFarm = farm => Number(farm.deaths) >= CONFIG.criticalDeaths;
  const distanceMeters = (a, b) => map.distance([a.lat, a.lng], [b.lat, b.lng]);

  function getAlertState(radiusMeters) {
    const criticalSources = farms.filter(isCriticalFarm);
    const neighborIds = new Set();
    const sourceMatches = new Map();
    farms.forEach(candidate => {
      for (const source of criticalSources) {
        const differentBarangay = normalize(candidate.barangay) !== normalize(source.barangay);
        if (candidate.id !== source.id && differentBarangay && distanceMeters(candidate, source) <= radiusMeters) {
          neighborIds.add(candidate.id);
          if (!sourceMatches.has(candidate.id)) sourceMatches.set(candidate.id, []);
          sourceMatches.get(candidate.id).push(source);
        }
      }
    });
    return { criticalSources, neighborIds, sourceMatches };
  }

  function popupContent(farm, isNeighbor, sources) {
    const status = isCriticalFarm(farm) ? 'CRITICAL FARM' : String(farm.level).toUpperCase();
    const neighborNote = isNeighbor
      ? `<hr><b>YELLOW NEIGHBOR ALERT</b><br>Within alert radius of critical poultry farm(s): ${sources.map(s => escapeHtml(s.farm + ' (' + s.barangay + ')')).join(', ')}`
      : '';
    return `<b>${escapeHtml(farm.owner)}</b><br>${escapeHtml(farm.farm)}<br>${escapeHtml(farm.type)}${farm.detail ? ' · ' + escapeHtml(farm.detail) : ''} · ${Number(farm.count)} animals<br>Barangay: ${escapeHtml(farm.barangay)}<br>Latest deaths: ${Number(farm.deaths)}<br>Status: <b>${status}</b>${farm.date ? '<br>Observation date: ' + escapeHtml(farm.date) : ''}${neighborNote}`;
  }

  function render() {
    const query = normalize(searchInput?.value);
    const radiusMeters = Number(radiusInput?.value || CONFIG.defaultAlertRadiusMeters);
    const { criticalSources, neighborIds, sourceMatches } = getAlertState(radiusMeters);
    const visible = farms.filter(farm => !query || [farm.owner, farm.farm, farm.barangay].some(value => normalize(value).includes(query)));
    layerGroup.clearLayers();

    // Draw source rings first so the colored farm markers remain visible above them.
    criticalSources.filter(source => visible.some(item => item.id === source.id)).forEach(source => {
      L.circle([source.lat, source.lng], { radius: radiusMeters, color: CONFIG.colors.critical, weight: 1.5, dashArray: '6 6', fillColor: CONFIG.colors.critical, fillOpacity: 0.045 })
        .bindPopup(`<b>Critical farm alert area</b><br>${escapeHtml(source.farm)} · ${escapeHtml(source.barangay)}<br>Screening radius: ${(radiusMeters / 1000).toFixed(1)} km`)
        .addTo(layerGroup);
    });

    visible.forEach(farm => {
      const neighbor = neighborIds.has(farm.id) && !isCriticalFarm(farm);
      const color = neighbor ? CONFIG.colors.neighbor : CONFIG.colors[farm.level] || CONFIG.colors.good;
      const marker = L.circleMarker([farm.lat, farm.lng], { radius: neighbor ? 11 : 9, color: neighbor ? '#8b6900' : '#fff', weight: 2, fillColor: color, fillOpacity: 0.98 }).addTo(layerGroup);
      marker.bindPopup(popupContent(farm, neighbor, sourceMatches.get(farm.id) || []));
    });

    const visibleCritical = criticalSources.filter(source => visible.some(item => item.id === source.id));
    const visibleNeighborCount = visible.filter(farm => neighborIds.has(farm.id) && !isCriticalFarm(farm)).length;
    if (alertSummary) {
      alertSummary.hidden = visibleCritical.length === 0 && visibleNeighborCount === 0;
      alertSummary.textContent = visibleCritical.length || visibleNeighborCount
        ? `⚠ ${visibleCritical.length} visible critical farm source(s). ${visibleNeighborCount} farm(s) in other barangays are highlighted yellow within ${(radiusMeters / 1000).toFixed(1)} km.`
        : '';
    }
    if (resultCount) resultCount.textContent = `${visible.length} matching farm(s) · ${visibleCritical.length} critical farm source(s) · ${visibleNeighborCount} nearby-barangay alert(s)`;
    if (visible.length) {
      const bounds = L.latLngBounds(visible.map(farm => [farm.lat, farm.lng]));
      map.fitBounds(bounds.pad(0.18), { maxZoom: 14 });
    } else {
      map.setView(CONFIG.mapCenter, CONFIG.initialZoom);
    }
    if (radiusValue) radiusValue.value = `${(radiusMeters / 1000).toFixed(1)} km`;
  }

  document.getElementById('searchBtn')?.addEventListener('click', render);
  document.getElementById('resetBtn')?.addEventListener('click', () => { if (searchInput) searchInput.value = ''; render(); });
  searchInput?.addEventListener('keydown', event => { if (event.key === 'Enter') render(); });
  searchInput?.addEventListener('input', render);
  radiusInput?.addEventListener('input', render);
  render();
  window.setTimeout(() => map.invalidateSize(), 250);
  console.info(`[VetSystem GIS] Loaded ${farms.length} mapped farms.`);
})();
