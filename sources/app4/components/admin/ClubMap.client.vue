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

onMounted(() => {
  if (!mapContainer.value) return

  map.value = L.map(mapContainer.value).setView([46.85, 1.75], 5)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 18
  }).addTo(map.value)

  markersLayer.value = L.layerGroup().addTo(map.value)

  buildMarkers()
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
  buildMarkers()
}, { deep: true })

// Geocode an address and place a search marker
function geocode(address: string): Promise<{ lat: number; lng: number } | null> {
  return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
    .then(r => r.json())
    .then((results: Array<{ lat: string; lon: string }>) => {
      if (!results.length || !map.value) return null

      const lat = parseFloat(results[0]!.lat)
      const lng = parseFloat(results[0]!.lon)

      // Remove previous search marker
      if (searchMarker.value && map.value) {
        map.value.removeLayer(searchMarker.value)
      }

      searchMarker.value = L.marker([lat, lng], { icon: searchIcon })
        .addTo(map.value!)
        .bindPopup(address)
        .openPopup()

      map.value!.setView([lat, lng], 12)

      return { lat, lng }
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
  <div ref="mapContainer" class="w-full h-100 sm:h-125 rounded-lg border border-gray-200 z-0" />
</template>
