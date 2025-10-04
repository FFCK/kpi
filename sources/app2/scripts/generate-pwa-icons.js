#!/usr/bin/env node

/**
 * Script to generate PWA icons from the source logo
 * Usage: node scripts/generate-pwa-icons.js
 */

import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

const publicDir = path.join(__dirname, '..', 'public')
const sourceLogo = path.join(publicDir, 'img', 'logo_kp.png')

console.log('PWA Icons Generation Script')
console.log('===========================\n')

// Check if source logo exists
if (!fs.existsSync(sourceLogo)) {
  console.error('❌ Source logo not found at:', sourceLogo)
  process.exit(1)
}

console.log('✅ Source logo found:', sourceLogo)

// For now, we'll just copy the logo to the required sizes
// In production, you would use a library like 'sharp' to resize the images
const icons = [
  { src: sourceLogo, dest: path.join(publicDir, 'pwa-512x512.png'), size: '512x512' },
  { src: sourceLogo, dest: path.join(publicDir, 'pwa-192x192.png'), size: '192x192' },
  { src: sourceLogo, dest: path.join(publicDir, 'apple-touch-icon.png'), size: '180x180' }
]

console.log('\nNote: This script copies the 512x512 logo.')
console.log('For production, consider using imagemagick or sharp to resize properly.\n')

icons.forEach(icon => {
  try {
    fs.copyFileSync(icon.src, icon.dest)
    console.log(`✅ Created ${icon.size}: ${path.basename(icon.dest)}`)
  } catch (err) {
    console.error(`❌ Failed to create ${icon.size}:`, err.message)
  }
})

console.log('\n✨ PWA icons generation completed!')
console.log('\nTo properly resize icons, install imagemagick and run:')
console.log('  convert public/img/logo_kp.png -resize 192x192 public/pwa-192x192.png')
console.log('  convert public/img/logo_kp.png -resize 180x180 public/apple-touch-icon.png')
