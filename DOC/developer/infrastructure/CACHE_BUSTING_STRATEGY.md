# Cache Busting Strategy - App2 & App3

**Date**: 2025-12-21
**Status**: ✅ Implemented
**Scope**: app2, app3 (Nuxt SSG)

## Problem Statement

When deploying new builds to preprod/production, browsers may cache:
- JavaScript files with old environment variables
- HTML with inline configuration (apiBaseUrl, backendBaseUrl)
- Service Worker with outdated routes

This causes users to see old URLs or configuration even after deployment.

## Solution: Timestamp-Based Build ID

### Implementation

**Files Modified**:
- `sources/app2/nuxt.config.ts`
- `sources/app3/nuxt.config.ts`

**Code**:
```typescript
// nuxt.config.ts
const buildId = `v${Date.now()}`

export default defineNuxtConfig({
  app: {
    buildAssetsDir: `/_nuxt/${buildId}/`,
  },
  pwa: {
    injectRegister: false, // Manual registration in plugin
    workbox: {
      // Disable precaching - use runtime caching only
      globPatterns: [],
      cacheId: `kpi-app2-${buildId}`,
      skipWaiting: true,
      clientsClaim: true,
      cleanupOutdatedCaches: true,
      runtimeCaching: [
        {
          urlPattern: ({ request }) => request.mode === 'navigate',
          handler: 'NetworkFirst'
        },
        {
          urlPattern: /\/_nuxt\/.*\.(js|css)$/,
          handler: 'NetworkFirst' // Check network for each request
        }
      ]
    }
  }
})
```

```typescript
// plugins/pwa.client.ts
export default defineNuxtPlugin(() => {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js', {
      updateViaCache: 'none' // Bypass HTTP cache for SW file
    }).then((registration) => {
      // Force update check on every page load
      registration.update()

      // Auto-reload when new SW takes control
      navigator.serviceWorker.addEventListener('controllerchange', () => {
        window.location.reload()
      })
    })
  }
})
```

### How It Works

1. **Every build generates a unique timestamp**
   - Example: `v1766333103611`
   - Assets path: `/_nuxt/v1766333103611/`

2. **Service Worker update check on every page load**
   - `updateViaCache: 'none'` → SW file bypasses browser cache
   - `registration.update()` → forces immediate update check
   - New SW detected → downloads in background

3. **No precaching - everything uses NetworkFirst**
   - `globPatterns: []` → no files are precached
   - HTML: always fetched from network (timeout 3s)
   - JS/CSS: always checked on network first (timeout 3s)
   - Fallback to cache only if network unavailable

4. **Immediate activation and takeover**
   - `skipWaiting: true` → new SW activates immediately
   - `clientsClaim: true` → takes control of all pages
   - `controllerchange` event → auto-reload page

5. **Automatic cache cleanup**
   - New `cacheId` → new cache storage created
   - `cleanupOutdatedCaches: true` → old caches deleted
   - Only current version cached

6. **Result: Simple F5 gets new version**
   - Page loads → SW update check runs
   - New SW found → downloads and activates
   - Page reloads → new assets fetched
   - **No Ctrl+F5 needed!**

## Benefits

### ✅ Automatic Cache Invalidation
- No manual cache clearing needed
- Each deployment creates unique asset URLs
- Browsers automatically fetch new files

### ✅ Zero Configuration
- Works automatically on every `make run_generate_*` command
- No version file to maintain
- No build number tracking needed

### ✅ Service Worker Auto-Update (No Ctrl+F5 Required!)
- HTML never precached → simple F5 fetches new version
- Each build gets a unique Service Worker cache ID
- Old Service Worker immediately replaced (no waiting for tab close)
- Stale caches automatically cleaned up
- **Users get updates with simple page refresh - no hard reload needed**

### ✅ Environment-Specific Builds
- Dev build: `v1766333103611` with `kpi.localhost/api`
- Preprod build: `v1766334567890` with `preprod.kayak-polo.info/api`
- Prod build: `v1766335678901` with `kayak-polo.info/api`

### ✅ Old Assets Cleanup
- Old `/_nuxt/v*` directories can be safely deleted after deployment
- Only the latest build's directory is needed

## Nginx Configuration

No changes needed! Nginx serves all `/_nuxt/*` paths:

```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    expires 1y;  # Can use aggressive caching
    add_header Cache-Control "public, immutable";
}
```

**Why it works**: Each build has a unique path, so aggressive caching is safe.

## Deployment Workflow

### Development
```bash
make run_generate_dev
# Assets: /_nuxt/v1766333103611/
# Config: kpi.localhost/api
```

### Pre-production
```bash
make run_generate_preprod
docker restart kpi_preprod_nginx_app2
# Assets: /_nuxt/v1766334567890/
# Config: preprod.kayak-polo.info/api
```

### Production
```bash
make run_generate_prod
docker restart kpi_nginx_app2
# Assets: /_nuxt/v1766335678901/
# Config: kayak-polo.info/api
```

## Verification

After deployment, verify the update mechanism works:

1. **Deploy new build**:
   ```bash
   make run_generate_preprod
   docker restart kpi_preprod_nginx_app2
   ```

2. **Simple refresh test** (F5 or Cmd+R):
   - Open app in browser
   - Note current buildId in Network tab: `/_nuxt/v1766335678901/`
   - Deploy new build on server
   - Press F5 (NOT Ctrl+F5)
   - Should see new buildId: `/_nuxt/v1766999999999/`

3. **DevTools verification**:
   - **Network tab**: Verify new asset paths with new timestamp
   - **Console**: Check runtime config
     ```javascript
     window.__NUXT__.config.public.apiBaseUrl
     // Should show: "https://preprod.kayak-polo.info/api"
     ```
   - **Application → Service Workers**: Should see new SW activating
   - **Application → Cache Storage**: Old caches should be cleaned automatically

## Alternative Approaches (Not Used)

### ❌ Git Commit Hash
```typescript
const buildId = execSync('git rev-parse --short HEAD').toString().trim()
```
**Cons**: Requires git in Docker container, same hash for multiple deploys

### ❌ package.json version
```typescript
const buildId = `v${packageJson.version}`
```
**Cons**: Requires manual version bumps before each deploy

### ❌ Environment variable
```typescript
const buildId = process.env.BUILD_ID || 'dev'
```
**Cons**: Requires passing BUILD_ID to every build command

### ✅ Timestamp (Current Solution)
```typescript
const buildId = `v${Date.now()}`
```
**Pros**: Automatic, unique, no dependencies, no manual work

## Cleanup Old Builds

After successful deployment, you can remove old build directories:

```bash
# Keep only the latest 3 builds
cd sources/app2/.output/public/_nuxt/
ls -dt v* | tail -n +4 | xargs rm -rf

# Or keep only current build
cd sources/app2/.output/public/_nuxt/
find . -type d -name "v*" ! -name "$(ls -t | head -1)" -exec rm -rf {} +
```

**Note**: This is optional - old directories don't affect functionality.

## Troubleshooting

### Issue: Users still see old URLs

**Diagnosis**:
1. Check generated HTML: `grep apiBaseUrl sources/app2/.output/public/index.html`
2. Verify buildAssetsDir in source: `grep buildAssetsDir sources/app2/.output/public/index.html`

**Solution**:
- Clear browser cache: Ctrl+Shift+Delete
- Hard reload: Ctrl+Shift+R
- Restart nginx: `docker restart kpi_nginx_app2`

### Issue: Service Worker caching old files

**This should no longer occur** with the current configuration (`skipWaiting: true` + `clientsClaim: true`).

If it still happens:

**Diagnosis**: Check Application tab → Service Workers
- Look for multiple Service Workers registered
- Check cache storage for old cacheId entries

**Solution**:
1. The new Service Worker should automatically activate and take control
2. Old caches should be automatically cleaned
3. If problem persists, manually unregister old Service Workers in DevTools
4. Clear all site data and hard reload

### Issue: Build fails with "Cannot read buildAssetsDir"

**Diagnosis**: Nuxt version too old

**Solution**: Ensure Nuxt 3.0+ is installed:
```bash
npm list nuxt
```

## Related Documentation

- [Nginx Static App Deployment](NGINX_STATIC_APP_DEPLOYMENT.md)
- [Nuxt buildAssetsDir Documentation](https://nuxt.com/docs/api/nuxt-config#buildassetsdir)
- [HTTP Caching Best Practices](https://web.dev/http-cache/)

## Monitoring

### Metrics to Track

1. **Cache Hit Rate**: Monitor nginx logs for 304 vs 200 responses
2. **Asset 404s**: Alert on missing `/_nuxt/v*/` paths (indicates incomplete deployment)
3. **Build ID rotation**: Track how many builds are deployed per day

### Alerts to Set Up

- **Old builds accumulating**: Alert if more than 10 `v*` directories exist
- **Deployment failures**: Alert if nginx restart fails after build
- **Mixed builds**: Alert if users report seeing old URLs after deployment

## Future Improvements

1. **Automated cleanup**: Add Makefile command to clean old builds
2. **Build manifest**: Generate JSON file tracking deployed builds
3. **Rollback capability**: Keep last N builds for quick rollback
4. **Build metrics**: Log build times and sizes for performance tracking
