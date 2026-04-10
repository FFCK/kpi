# CORS Configuration - Global PHP Auto-Prepend

**Date**: 2025-12-21
**Status**: ✅ Implemented
**Scope**: All PHP endpoints (API, custom files, api2)

## Overview

This document describes the global CORS (Cross-Origin Resource Sharing) configuration implemented via PHP auto-prepend mechanism to ensure consistent CORS headers across all PHP endpoints.

## Problem Statement

### Before (Problematic)

**Issue**: Duplicate CORS headers causing browser errors

```http
access-control-allow-origin: *
access-control-allow-origin: https://app.kpi.localhost
access-control-allow-methods: GET, POST, PUT, DELETE, OPTIONS, PATCH
access-control-allow-methods: GET, PUT, POST, DELETE, OPTIONS
```

**Root Causes**:
1. Apache setting wildcard CORS headers in `000-default.conf`
2. PHP setting specific origin headers in `api/config/headers.php`
3. Headers only set for `/api/*` endpoints, not for custom files or `/api2/`

### After (Solution)

**Result**: Single CORS headers set dynamically for all PHP files

```http
access-control-allow-origin: https://app.kpi.localhost
access-control-allow-credentials: true
access-control-allow-methods: GET, PUT, POST, DELETE, OPTIONS, PATCH
access-control-allow-headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization, Cache-Control, Pragma, Expires
access-control-max-age: 1000
```

## Architecture

### PHP Auto-Prepend Mechanism

PHP's `auto_prepend_file` directive automatically includes a file before every PHP script execution.

```
┌─────────────────────────────────────────────────────────────┐
│                      HTTP Request                            │
│              (e.g., https://kpi.localhost/api/...)          │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    Apache/PHP-FPM                            │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│             auto_prepend_file (AUTOMATIC)                    │
│         /var/www/html/commun/auto-prepend-cors.php          │
│                                                              │
│  1. Check if Origin header exists                           │
│  2. Validate origin against whitelist                       │
│  3. Set CORS headers if authorized                          │
│  4. Handle OPTIONS preflight requests                       │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│              Actual PHP Script Execution                     │
│       (api/index.php, custom files, api2/*, etc.)           │
└─────────────────────────────────────────────────────────────┘
```

## Implementation

### File 1: auto-prepend-cors.php

**Location**: `docker/config/auto-prepend-cors.php` → copied to `/var/www/html/commun/auto-prepend-cors.php`

```php
<?php
/**
 * Auto-prepend file for PHP
 * Automatically included before every PHP script execution
 * Handles CORS headers for all endpoints (API, custom files, api2)
 */

// Only set CORS headers for requests with Origin header (AJAX/fetch requests)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];

    // Allow specific origins or any .localhost domain in development
    if (
        $origin === "https://kayak-polo.info" ||
        $origin === "https://www.kayak-polo.info" ||
        $origin === "https://app2.kayak-polo.info" ||
        $origin === "http://localhost:9000" ||
        $origin === "http://localhost:9001" ||
        $origin === "http://localhost:9002" ||
        $origin === "http://localhost:3002" ||
        $origin === "https://kpi-node.localhost" ||
        $origin === "https://app.kpi.localhost" || // Nginx static app
        ($origin && preg_match('/^https?:\/\/.*\.localhost$/', $origin)) // Allow all .localhost domains in dev
    ) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization, Cache-Control, Pragma, Expires');
    }

    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
```

### File 2: php-auto-prepend.ini

**Location**: `docker/config/php-auto-prepend.ini` → copied to `/usr/local/etc/php/conf.d/php-auto-prepend.ini`

```ini
; Auto-prepend CORS configuration file
; This file is automatically loaded before every PHP script
auto_prepend_file = /var/www/html/commun/auto-prepend-cors.php
```

### File 3: Apache Configuration

**Location**: `docker/config/000-default.conf`

```apache
<VirtualHost *:80>
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        # CORS headers are managed dynamically by PHP (see auto-prepend-cors.php)
        # Removed static CORS headers to avoid conflicts with PHP-managed headers
    </Directory>

    # WordPress subdirectory configuration
    <Directory /var/www/html/wordpress>
        # CORS headers removed - managed by PHP globally
    </Directory>
</VirtualHost>
```

**Key Change**: All `Header always set Access-Control-*` directives removed from Apache.

### Dockerfile Integration

#### Development (Dockerfile.dev.web)

```dockerfile
# Copy PHP auto-prepend configuration for CORS
COPY php-auto-prepend.ini /usr/local/etc/php/conf.d/
COPY auto-prepend-cors.php /var/www/html/commun/auto-prepend-cors.php
RUN chmod 644 /var/www/html/commun/auto-prepend-cors.php
```

#### Production (Dockerfile.prod.web)

Same configuration as development.

## Allowed Origins

### Production Origins
```php
"https://kayak-polo.info"           // Main production domain
"https://www.kayak-polo.info"       // WWW variant
"https://app2.kayak-polo.info"      // Legacy app2 domain
```

### Development Origins
```php
"http://localhost:9000"             // Legacy Vue.js app_dev
"http://localhost:9001"             // Legacy Vue.js app_live_dev
"http://localhost:9002"             // Legacy Vue.js app_wsm_dev
"http://localhost:3002"             // Nuxt dev server (app2)
"https://kpi-node.localhost"        // Nuxt dev server via Traefik
"https://app.kpi.localhost"         // Nginx static app (app2)
```

### Pattern-Based Origins
```php
preg_match('/^https?:\/\/.*\.localhost$/', $origin)
```
Matches any subdomain of `.localhost` (e.g., `app3.localhost`, `test.kpi.localhost`)

## CORS Headers Explained

### Access-Control-Allow-Origin
```http
Access-Control-Allow-Origin: https://app.kpi.localhost
```
- **Purpose**: Specifies which origin can access the resource
- **Value**: Exact origin (not wildcard `*`)
- **Why**: Credentials require specific origin, not wildcard

### Access-Control-Allow-Credentials
```http
Access-Control-Allow-Credentials: true
```
- **Purpose**: Allows cookies and authentication headers
- **Required for**: Session-based authentication, JWT tokens in cookies

### Access-Control-Allow-Methods
```http
Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS, PATCH
```
- **Purpose**: Specifies allowed HTTP methods
- **Coverage**: All REST API methods

### Access-Control-Allow-Headers
```http
Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, X-Requested-With, Authorization, Cache-Control, Pragma, Expires
```
- **Purpose**: Specifies allowed request headers
- **Includes**: Custom headers like `X-Auth-Token`, standard headers like `Authorization`

### Access-Control-Max-Age
```http
Access-Control-Max-Age: 1000
```
- **Purpose**: Cache preflight response for 1000 seconds (16.67 minutes)
- **Benefit**: Reduces preflight requests frequency

## Preflight Requests (OPTIONS)

### What is a Preflight Request?

Before sending certain requests (e.g., with custom headers or credentials), browsers send an OPTIONS request to check if the server allows it.

**Example**:
```http
OPTIONS /api/event/getEvents/15 HTTP/1.1
Host: kpi.localhost
Origin: https://app.kpi.localhost
Access-Control-Request-Method: GET
Access-Control-Request-Headers: Authorization
```

**Response**:
```http
HTTP/1.1 200 OK
Access-Control-Allow-Origin: https://app.kpi.localhost
Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS, PATCH
Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, ...
Access-Control-Max-Age: 1000
```

### Handling in auto-prepend-cors.php

```php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
```

This immediately returns 200 OK for OPTIONS requests after setting CORS headers, preventing execution of the actual endpoint.

## Benefits

### 1. Consistency
- **All PHP endpoints** have CORS headers (not just `/api/`)
- No need to remember to include `headers.php` in new files

### 2. Maintainability
- **Single source of truth** for CORS configuration
- Change one file (`auto-prepend-cors.php`) to update all endpoints

### 3. Security
- **Explicit origin matching** (no wildcards in production)
- **Credentials support** with proper origin validation

### 4. Compatibility
- Works with legacy API (`/api/`)
- Works with new API (`/api2/`)
- Works with custom PHP files
- Works with WordPress endpoints

## Migration from Legacy Setup

### Before (Legacy: api/config/headers.php)

```php
// Only used in api/index.php
include_once('config/headers.php');
set_response_headers();
```

**Limitations**:
- Only worked for `/api/*` endpoints
- Required manual inclusion in each file
- Not available for custom files or `/api2/`

### After (Global: auto-prepend-cors.php)

```php
// Automatically loaded before EVERY PHP file
// No manual inclusion needed
```

**Benefits**:
- Works for all PHP files
- Automatic, no developer action required
- Future-proof for new endpoints

### Deprecation of Legacy Headers

The `api/config/headers.php` file is still present but **no longer called** in `api/index.php`. It can be removed in a future cleanup.

## Testing CORS

### Command Line (curl)

```bash
# Test API endpoint
curl -k -I -H "Origin: https://app.kpi.localhost" https://kpi.localhost/api/event/getEvents/15

# Expected headers
access-control-allow-origin: https://app.kpi.localhost
access-control-allow-credentials: true
access-control-allow-methods: GET, PUT, POST, DELETE, OPTIONS, PATCH
```

### Browser DevTools

1. Open Network tab
2. Make AJAX request from `https://app.kpi.localhost`
3. Check response headers for `Access-Control-Allow-Origin`
4. Verify no duplicate headers

### Preflight Request Test

```bash
curl -k -X OPTIONS -I \
  -H "Origin: https://app.kpi.localhost" \
  -H "Access-Control-Request-Method: GET" \
  -H "Access-Control-Request-Headers: Authorization" \
  https://kpi.localhost/api/event/getEvents/15

# Expected: HTTP 200 with CORS headers
```

## Adding New Allowed Origins

### Step 1: Edit auto-prepend-cors.php

```php
if (
    $origin === "https://kayak-polo.info" ||
    // ... existing origins ...
    $origin === "https://new-domain.com" ||  // Add here
    ($origin && preg_match('/^https?:\/\/.*\.localhost$/', $origin))
) {
```

### Step 2: Rebuild Docker Image

```bash
# Development
make docker_dev_rebuild

# Production
make docker_prod_rebuild
```

### Step 3: Verify

```bash
curl -k -I -H "Origin: https://new-domain.com" https://kpi.localhost/api/test
```

## Troubleshooting

### Issue: CORS headers not appearing

**Diagnosis**:
```bash
docker exec kpi_php php -i | grep auto_prepend_file
```

**Expected Output**:
```
auto_prepend_file => /var/www/html/commun/auto-prepend-cors.php
```

**Solution**: Rebuild Docker image with `make docker_dev_rebuild`

### Issue: Duplicate CORS headers

**Diagnosis**:
```bash
curl -k -I -H "Origin: https://app.kpi.localhost" https://kpi.localhost/api/test | grep -i access-control
```

**Cause**: Apache still setting headers
**Solution**: Check `000-default.conf` for `Header always set Access-Control-*` and remove them

### Issue: OPTIONS requests return 405 Method Not Allowed

**Cause**: Apache rewrite rules or .htaccess blocking OPTIONS
**Solution**: Ensure Apache allows OPTIONS method:

```apache
<Directory /var/www/html>
    # Handle preflight OPTIONS requests
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ $1 [R=200,L]
</Directory>
```

### Issue: Origin not matching

**Diagnosis**:
```bash
# Check exact origin value
docker exec kpi_php tail -f /var/log/apache2/access.log
```

**Solution**: Add exact origin string to whitelist in `auto-prepend-cors.php`

## Performance Considerations

### Minimal Overhead

- **Only executes if Origin header present**: Regular browser navigation doesn't trigger CORS logic
- **Simple string comparison**: No complex regex (except for .localhost pattern)
- **Early exit for OPTIONS**: Preflight requests don't execute endpoint code

### Caching

- `Access-Control-Max-Age: 1000` reduces preflight frequency
- Browsers cache preflight responses for 16.67 minutes

## Security Considerations

### Explicit Origin Matching

```php
// ✅ GOOD: Explicit origins
$origin === "https://app.kpi.localhost"

// ❌ BAD: Wildcard (disabled credentials)
header("Access-Control-Allow-Origin: *");
```

### Pattern Matching Risk

```php
// ⚠️ CAUTION: Only in development
preg_match('/^https?:\/\/.*\.localhost$/', $origin)
```

This pattern allows ANY `.localhost` subdomain. This is acceptable for development but should be removed or tightened in production.

### Credentials Flag

```php
header('Access-Control-Allow-Credentials: true');
```

This allows cookies/sessions. Only enable for trusted origins.

## Future Improvements

1. **Environment-Specific Whitelists**: Separate dev and prod origin lists
2. **Database-Driven Origins**: Store allowed origins in database for dynamic management
3. **Logging**: Log blocked CORS requests for security auditing
4. **Rate Limiting**: Limit preflight requests per origin

## References

- [MDN: CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
- [MDN: Preflight Requests](https://developer.mozilla.org/en-US/docs/Glossary/Preflight_request)
- [PHP auto_prepend_file](https://www.php.net/manual/en/ini.core.php#ini.auto-prepend-file)
- [Apache Header Directive](https://httpd.apache.org/docs/2.4/mod/mod_headers.html)

## Related Documentation

- [Nginx Static App Deployment](NGINX_STATIC_APP_DEPLOYMENT.md)
- [Docker Infrastructure](DOCKER_INFRASTRUCTURE.md)
- [API Reference](../reference/KPI_FUNCTIONALITY_INVENTORY.md)
