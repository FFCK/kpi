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
// Generate unique build ID based on timestamp to force cache invalidation
// This ensures browser cache is invalidated when deploying new builds
const buildId = `v${Date.now()}`

export default defineNuxtConfig({
  app: {
    buildAssetsDir: `/_nuxt/${buildId}/`,
    // ...
  },
  pwa: {
    workbox: {
      // Include buildId in cache name to force SW update on new builds
      cacheId: `kpi-app2-${buildId}`,
      // Immediately activate new Service Worker
      skipWaiting: true,
      clientsClaim: true,
      cleanupOutdatedCaches: true,
      // ...
    }
  }
})
```

### How It Works

1. **Every build generates a unique timestamp**
   - Example: `v1766333103611`

2. **Assets are stored in versioned directories**
   - Old: `/_nuxt/BZu7b7xY.js`
   - New: `/_nuxt/v1766333103611/BZu7b7xY.js`

3. **HTML references new paths**
   ```html
   <!-- Old build -->
   <script src="/_nuxt/v1766333103611/BZu7b7xY.js"></script>

   <!-- New build -->
   <script src="/_nuxt/v1766334567890/BZu7b7xY.js"></script>
   ```

4. **Service Worker updates immediately**
   - `cacheId` includes buildId → new cache storage created
   - `skipWaiting: true` → new SW activates immediately without waiting
   - `clientsClaim: true` → new SW takes control of all pages immediately
   - `cleanupOutdatedCaches: true` → old caches are deleted automatically

5. **Browser sees different URLs → bypasses cache**

## Benefits

### ✅ Automatic Cache Invalidation
- No manual cache clearing needed
- Each deployment creates unique asset URLs
- Browsers automatically fetch new files

### ✅ Zero Configuration
- Works automatically on every `make run_generate_*` command
- No version file to maintain
- No build number tracking needed

### ✅ Service Worker Auto-Update
- Each build gets a unique Service Worker cache ID
- Old Service Worker immediately replaced (no waiting for tab close)
- Stale caches automatically cleaned up
- Users always get the latest version without manual cache clearing

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

After deployment, check browser DevTools:

1. **Network tab**: Verify new asset paths
   ```
   /_nuxt/v1766335678901/BZu7b7xY.js  ← New timestamp
   ```

2. **Console**: Check runtime config
   ```javascript
   window.__NUXT__.config.public.apiBaseUrl
   // Should show: "https://preprod.kayak-polo.info/api"
   ```

3. **Application tab**: Clear Service Workers if needed
   - Unregister old Service Workers
   - Hard reload (Ctrl+Shift+R)

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
