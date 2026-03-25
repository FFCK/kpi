<script setup lang="ts">
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import type { ClubMapItem } from '~/types/clubs'

const props = defineProps<{
  clubs: ClubMapItem[]
  selectedClubCode?: string | null
}>()

const emit = defineEmits<{
  'select-club': [code: string]
}>()

const mapContainer = ref<HTMLElement | null>(null)
const map = shallowRef<L.Map | null>(null)
const markersLayer = shallowRef<L.LayerGroup | null>(null)
const searchMarker = shallowRef<L.Marker | null>(null)
const clubMarkers = shallowRef<Map<string, L.Marker>>(new Map())

// Custom icons
const clubIcon = L.icon({
  iconUrl: '/admin2/img/Map-Marker-Ball-Right-Azure-icon.png',
  iconSize: [14, 25],
  iconAnchor: [12, 25],
  popupAnchor: [0, -25]
})

const searchIcon = L.icon({
  iconUrl: '/admin2/img/Map-Marker-Ball-Left-Bronze-icon.png',
  iconSize: [14, 25],
  iconAnchor: [12, 25],
  popupAnchor: [0, -25]
})

function initMap() {
  if (!mapContainer.value || map.value) return

  map.value = L.map(mapContainer.value).setView([46.85, 1.75], 5)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 18
  }).addTo(map.value)

  markersLayer.value = L.layerGroup().addTo(map.value)

  buildMarkers()

  // Center on selected club if already set before map was ready
  if (props.selectedClubCode) {
    const marker = clubMarkers.value.get(props.selectedClubCode)
    if (marker) {
      map.value.setView(marker.getLatLng(), 12)
      marker.openPopup()
    }
  }
}

onMounted(() => {
  initMap()
})

watch(mapContainer, (el) => {
  if (el) initMap()
})

onBeforeUnmount(() => {
  if (map.value) {
    map.value.remove()
    map.value = null
  }
})

function buildMarkers() {
  if (!markersLayer.value) return
  markersLayer.value.clearLayers()
  const newMarkers = new Map<string, L.Marker>()

  for (const club of props.clubs) {
    const coords = parseCoord(club.coord)
    if (!coords) continue

    const marker = L.marker([coords.lat, coords.lng], { icon: clubIcon })
      .bindPopup(`<strong>${club.libelle}</strong><br>${club.code}`)
      .on('click', () => {
        emit('select-club', club.code)
      })

    markersLayer.value.addLayer(marker)
    newMarkers.set(club.code, marker)
  }

  clubMarkers.value = newMarkers
}

function parseCoord(coord: string): { lat: number; lng: number } | null {
  if (!coord) return null
  const parts = coord.split(',').map(s => parseFloat(s.trim()))
  if (parts.length < 2 || isNaN(parts[0]!) || isNaN(parts[1]!)) return null
  return { lat: parts[0]!, lng: parts[1]! }
}

// Highlight and center on selected club
watch(() => props.selectedClubCode, (code) => {
  if (!code || !map.value) return
  const marker = clubMarkers.value.get(code)
  if (marker) {
    map.value.setView(marker.getLatLng(), 12)
    marker.openPopup()
  }
})

// Rebuild markers when clubs change
watch(() => props.clubs, () => {
  if (!map.value) {
    initMap()
  } else {
    buildMarkers()
    nextTick(() => map.value?.invalidateSize())
  }
}, { deep: true })

// Geocode an address and place a search marker
function geocode(address: string): Promise<{ lat: number; lng: number; displayName: string } | null> {
  return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&addressdetails=1`)
    .then(r => r.json())
    .then((results: Array<{ lat: string; lon: string; display_name: string }>) => {
      if (!results.length || !map.value) return null

      const lat = parseFloat(results[0]!.lat)
      const lng = parseFloat(results[0]!.lon)
      const displayName = results[0]!.display_name
      const coordStr = `${lat},${lng}`

      // Remove previous search marker
      if (searchMarker.value && map.value) {
        map.value.removeLayer(searchMarker.value)
      }

      const popupContent = `
        <div style="min-width:200px">
          <div style="font-weight:600;margin-bottom:6px">${displayName}</div>
          <div style="display:flex;align-items:center;gap:6px;background:#f3f4f6;padding:4px 8px;border-radius:4px;font-family:monospace;font-size:12px">
            <span style="user-select:all">${coordStr}</span>
            <button onclick="navigator.clipboard.writeText('${coordStr}')" title="Copier" style="border:none;background:none;cursor:pointer;padding:2px;line-height:1;color:#6b7280">
              <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M7 3.5A1.5 1.5 0 018.5 2h3.879a1.5 1.5 0 011.06.44l3.122 3.12A1.5 1.5 0 0117 6.622V12.5a1.5 1.5 0 01-1.5 1.5h-1v-3.379a3 3 0 00-.879-2.121L10.5 5.379A3 3 0 008.379 4.5H7v-1z"/><path d="M4.5 6A1.5 1.5 0 003 7.5v9A1.5 1.5 0 004.5 18h7a1.5 1.5 0 001.5-1.5v-5.879a1.5 1.5 0 00-.44-1.06L9.44 6.439A1.5 1.5 0 008.378 6H4.5z"/></svg>
            </button>
          </div>
        </div>`

      searchMarker.value = L.marker([lat, lng], { icon: searchIcon })
        .addTo(map.value!)
        .bindPopup(popupContent, { maxWidth: 300 })
        .openPopup()

      map.value!.setView([lat, lng], 12)

      return { lat, lng, displayName }
    })
    .catch(() => null)
}

// Center on a club by code (for external calls)
function centerOnClub(code: string) {
  const marker = clubMarkers.value.get(code)
  if (marker && map.value) {
    map.value.setView(marker.getLatLng(), 12)
    marker.openPopup()
  }
}

// Update a single marker position after club update
function updateMarkerPosition(code: string, coord: string) {
  const coords = parseCoord(coord)
  const existing = clubMarkers.value.get(code)

  if (coords && existing) {
    existing.setLatLng([coords.lat, coords.lng])
  } else if (coords && markersLayer.value) {
    // Club now has coordinates, add marker
    const club = props.clubs.find(c => c.code === code)
    if (club) {
      const marker = L.marker([coords.lat, coords.lng], { icon: clubIcon })
        .bindPopup(`<strong>${club.libelle}</strong><br>${club.code}`)
        .on('click', () => emit('select-club', code))
      markersLayer.value.addLayer(marker)
      const updated = new Map(clubMarkers.value)
      updated.set(code, marker)
      clubMarkers.value = updated
    }
  }
}

defineExpose({ geocode, centerOnClub, updateMarkerPosition })
</script>

<template>
  <div ref="mapContainer" class="w-full h-100 sm:h-125 rounded-lg border border-header-200 z-0" />
</template>
