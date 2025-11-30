# Migration QR Code Library

## Overview

Successfully migrated from the legacy QR code library (v0.99 from 2010) to the modern **endroid/qr-code** library (v5.0) via Composer.

## Changes Made

### 1. Composer Dependency

Added `endroid/qr-code` to `sources/composer.json`:

```json
"endroid/qr-code": "^5.0"
```

### 2. New Wrapper Class

Created `sources/commun/QRcode.php` - a backward-compatible wrapper that:
- Uses endroid/qr-code internally
- Maintains the same API as the old library
- Supports all existing methods used in the codebase
- Provides better error handling and logging
- Compatible with PHP 8.4

### 3. Updated Files

Removed `require_once('lib/qrcode/qrcode.class.php');` from all PHP files and replaced with autoloading:

**Core files:**
- `kpqr.php`
- `frame_qr.php`

**PDF generation files:**
- `PdfQrCodeApp.php`
- `PdfQrCodes.php`
- `PdfCltChpt.php`
- `PdfCltChptDetail.php`
- `PdfCltNiveau.php`
- `PdfCltNiveauDetail.php`
- `PdfCltNiveauJournee.php`
- `PdfCltNiveauPhase.php`
- `PdfListeMatchs.php`
- `PdfListeMatchsEN.php`
- `PdfListeMatchs4Terrains.php`
- `PdfListeMatchs4TerrainsEn.php`
- `PdfListeMatchs4TerrainsEn2.php`
- `PdfListeMatchs4TerrainsEn3.php`
- `PdfListeMatchs4TerrainsEn4.php`

### 4. Removed Legacy Library

The old QR code library in `sources/lib/qrcode/` has been removed as it is no longer needed.

## API Compatibility

The new wrapper class maintains full backward compatibility with the old library:

```php
// Old library usage (still works with new wrapper)
$qrcode = new QRcode($data, 'H'); // error level : L, M, Q, H

// Create PNG image
$image = $qrcode->createPNG(500);

// Add logo
$image = $qrcode->addLogo($image, 'img/logo.jpg', 0.3);

// Get base64 data URL
$dataUrl = $qrcode->getBase64Url($image);

// Display in PDF (mPDF/FPDF)
$qrcode->displayFPDF($pdf, 115, 85, 62);

// Display as PNG
$qrcode->displayPNG();

// Display as HTML
$qrcode->displayHTML();
```

## Installation

After pulling these changes, run:

```bash
make composer_install
```

Or manually in the PHP container:

```bash
docker exec kpi_php bash -c "cd /var/www/html && composer install"
```

## Benefits

1. **Modern Library**: Active maintenance and PHP 8+ compatibility
2. **Better Quality**: Higher quality QR codes with modern encoding
3. **Security**: Regular security updates from the maintainers
4. **Features**: Access to additional features if needed in the future
5. **Composer Integration**: Managed dependencies via Composer
6. **No Code Changes**: Existing code continues to work without modifications

## Testing

Test the following functionality:

1. **Web QR Codes**: Access `kpqr.php` and `frame_qr.php` to verify QR code generation
2. **PDF Generation**: Generate PDFs with QR codes (competition listings, match sheets, etc.)
3. **Logo Integration**: Verify that logos are properly overlaid on QR codes
4. **Error Levels**: Test different error correction levels (L, M, Q, H)

## Rollback

If issues occur, the old library files are available in the git history and can be restored. However, the new implementation should work identically to the old one.

## Future Improvements

With the new library, we can easily add:
- Custom colors for QR codes
- Different output formats (SVG, EPS, etc.)
- Better logo positioning and styling
- Custom error correction levels per use case
- Performance optimizations

## Migration Date

**Date**: 2025-11-16
**Version**: 2.0
**Library**: endroid/qr-code v5.0
