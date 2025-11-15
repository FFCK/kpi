# Move features to GestionOperations and add image upload/rename functionality

## Summary
This PR consolidates administrative features into GestionOperations and adds new image management capabilities.

## Major Changes

### 1. Feature Migration from ImportPCE to GestionOperations
Moved three import features from ImportPCE page to centralize operations:
- **Mise à jour Licenciés** (profile ≤ 6): License update from federal database
- **Mise à jour Calendrier fédéral** (profile ≤ 2): Calendar CSV import
- **Import vers mode local** (profile ≤ 3): Cross-server event import/export

**Files modified:**
- Backend: `GestionOperations.php` - Added `uploadLicenceZip()`, `uploadCalendrierCsv()` methods and POST handlers
- Template: `GestionOperations.tpl` - Added three new sections in blocRight
- JavaScript: `GestionOperations.js` - Added JSON import/export handlers
- Cleanup: Simplified ImportPCE files with redirect notice

### 2. Secure Image Upload Functionality
New image upload feature supporting 5 image types with automatic resizing:

**Image Types:**
- Logo compétition: JPG, 1000x1000 max, `L-{code}-{saison}.jpg` → `sources/img/logo/`
- Bandeau compétition: JPG, 2480x250 max, `B-{code}-{saison}.jpg` → `sources/img/logo/`
- Sponsor compétition: JPG, 2480x250 max, `S-{code}-{saison}.jpg` → `sources/img/logo/`
- Logo club: PNG, 200x200 max, `{numero}-logo.png` → `sources/img/KPI/logo/`
- Logo nation: PNG, 200x200 max, `{NATION}.png` → `sources/img/Nations/`

**Security features:**
- MIME type validation using `finfo_file()`
- File extension checking
- Dimension validation
- Auto-resize with aspect ratio preservation (GD library)
- PNG transparency preservation
- Duplicate detection with user notification

**Implementation:**
- Backend: `uploadImage()` method with comprehensive validation
- Template: Dynamic form with conditional fields based on image type
- JavaScript: Real-time filename preview and form validation

### 3. Image Rename Functionality
Allows renaming existing images with free-form text input:

**Features:**
- Free text input for new filename (no pattern constraint)
- Extension automatically preserved from original file
- Real-time preview of complete filename
- Duplicate file detection prevents conflicts
- Confirmation dialog before rename

**User flow:**
1. Select image type (determines destination folder)
2. Enter current filename
3. Enter new base name (without extension)
4. Preview shows: `{newBaseName}.{originalExtension}`
5. Confirm and rename

**Validation:**
- Extension must remain identical
- New filename must not already exist
- Clear error messages guide user

### 4. jQuery Compatibility Fixes
Fixed compatibility issues with legacy jQuery version (< 1.6):

**Method replacements:**
- `.on()` → `.bind()` / `.change()` / `.click()`
- `.prop('disabled', false)` → `.removeAttr('disabled')`
- `.prop('disabled', true)` → `.attr('disabled', 'disabled')`

All event handlers now compatible with jQuery < 1.6.

### 5. Template Structure Fixes
- Merged duplicate `blocLeft` and `blocRight` sections
- Fixed null array access warnings with `isset()` checks
- Improved form submission handling

## Technical Details

### Backend (GestionOperations.php)
- Added `m_duplicate_file` class variable for pre-fill on duplicate detection
- Added `uploadImage()`: 170 lines with config array, validation, GD processing
- Added `renameImage()`: Extension validation and filesystem operations
- POST handlers in `__construct()` switch statement

### Frontend (GestionOperations.tpl)
- Upload form in blocLeft: Dynamic fields, file input, preview
- Rename form in blocLeft: Type selector, current/new name inputs
- Hidden fields: `json_data`, `Control`, `newImageName`
- Pre-fill support via `{$duplicate_file}` Smarty variable

### JavaScript (GestionOperations.js)
- `updateImageFields()`: Show/hide fields by type
- `updateFilenamePreview()`: Real-time upload filename generation
- `updateUploadButton()`: Enable button when all fields valid
- `updateRenamePreview()`: Extract extension, concatenate with base name
- `updateRenameButton()`: Validate rename form completeness

## Test Plan
- [ ] Upload logo compétition with valid dimensions
- [ ] Upload oversized image - verify auto-resize
- [ ] Upload wrong format (PNG for competition) - verify rejection
- [ ] Upload duplicate filename - verify error + pre-fill rename form
- [ ] Rename image with free text - verify extension preserved
- [ ] Attempt rename with different extension - verify rejection
- [ ] Import licenses from federal database
- [ ] Import calendar CSV
- [ ] Cross-server event import/export

## Files Changed
- `sources/admin/GestionOperations.php`: +425 lines
- `sources/smarty/templates/GestionOperations.tpl`: +120 lines
- `sources/js/GestionOperations.js`: +200 lines
- `sources/admin/ImportPCE.php`: -70 lines (cleanup)
- `sources/smarty/templates/importPCE.tpl`: -180 lines (cleanup)
- `sources/js/importPCE.js`: -120 lines (cleanup)

## Dependencies
- PHP GD library (for image resizing)
- PclZip library (already present)
- jQuery < 1.6 compatibility maintained

## Breaking Changes
None. All existing functionality preserved.

## Migration Notes
Users accessing ImportPCE features will see a redirect notice to GestionOperations.

## Commits Included
- feda6a0 Fix: typo
- 8e648cd Fix: rename button type and form submission
- 66477bb Fix: typo et operation order
- 5aacbd3 Simplify image rename: free text input with enforced extension
- a86f91c Fix duplicate pre-fill and allow extension-only rename
- 51b425a Fix: typo et operation order
- 3713c85 Fix jQuery compatibility: replace .prop() with .attr() and .removeAttr()
- 4cf3e30 Fix jQuery compatibility: replace .on() with legacy methods
- 4be52f5 Fix upload button and add image rename functionality
- 6a9ec03 Add secure image upload functionality to GestionOperations
- dd06daf Feat: operations reorganisations
- d6000ee Move ImportPCE features to GestionOperations
