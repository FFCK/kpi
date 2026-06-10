<?php

namespace App\Service;

use App\Exception\FileExistsException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image Operations Service
 *
 * Handles image upload, resize and rename operations for logos, banners and sponsors.
 */
class ImageOperationsService
{
    private array $imageConfig = [
        'logo_competition' => [
            'prefix' => 'L-',
            'extension' => '.jpg',
            'extensionByMime' => ['image/png' => '.png', 'image/jpeg' => '.jpg', 'image/jpg' => '.jpg'],
            'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
            'maxWidth' => 1000,
            'maxHeight' => 1000,
            'destination' => '/img/logo/',
            'nameFields' => ['codeCompetition', 'saison'],
            'label' => 'Logo compétition',
            'formatHint' => 'JPG ou PNG, max 1000x1000px',
        ],
        'bandeau_competition' => [
            'prefix' => 'B-',
            'extension' => '.jpg',
            'extensionByMime' => ['image/png' => '.png', 'image/jpeg' => '.jpg', 'image/jpg' => '.jpg'],
            'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
            'maxWidth' => 2480,
            'maxHeight' => 250,
            'destination' => '/img/logo/',
            'nameFields' => ['codeCompetition', 'saison'],
            'label' => 'Bandeau compétition',
            'formatHint' => 'JPG ou PNG, max 2480x250px',
        ],
        'sponsor_competition' => [
            'prefix' => 'S-',
            'extension' => '.jpg',
            'extensionByMime' => ['image/png' => '.png', 'image/jpeg' => '.jpg', 'image/jpg' => '.jpg'],
            'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
            'maxWidth' => 2480,
            'maxHeight' => 250,
            'destination' => '/img/logo/',
            'nameFields' => ['codeCompetition', 'saison'],
            'label' => 'Sponsor compétition',
            'formatHint' => 'JPG ou PNG, max 2480x250px',
        ],
        'logo_club' => [
            'prefix' => '',
            'extension' => '-logo.png',
            'mimeTypes' => ['image/png'],
            'maxWidth' => 200,
            'maxHeight' => 200,
            'destination' => '/img/KIP/logo/',
            'nameFields' => ['numeroClub'],
            'label' => 'Logo club',
            'formatHint' => 'PNG uniquement, max 200x200px',
        ],
        'logo_nation' => [
            'prefix' => '',
            'extension' => '.png',
            'mimeTypes' => ['image/png'],
            'maxWidth' => 200,
            'maxHeight' => 200,
            'destination' => '/img/Nations/',
            'nameFields' => ['codeNation'],
            'label' => 'Logo nation',
            'formatHint' => 'PNG uniquement, max 200x200px',
        ],
        'photo_equipe' => [
            'prefix' => '',
            'extension' => '-team.jpg',
            'mimeTypes' => ['image/jpeg', 'image/jpg'],
            'maxWidth' => 1920,
            'maxHeight' => 1080,
            'destination' => '/img/KIP/teams/',
            'nameFields' => ['numeroEquipe', 'saison'],
            'label' => 'Photo équipe',
            'formatHint' => 'JPG uniquement, max 1920x1080px',
        ],
    ];

    private string $documentRoot;

    public function __construct()
    {
        // Get document root from server or use default
        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 4);
    }

    /**
     * Upload an image with automatic resizing.
     * If the file already exists and $overwrite is false, throws FileExistsException with the proposed archive name.
     * If $overwrite is true, renames the existing file to <basename>_YYYY-MM-DD.<ext> before saving.
     */
    public function uploadImage(string $imageType, UploadedFile $file, array $params, bool $overwrite = false): array
    {
        if (!isset($this->imageConfig[$imageType])) {
            throw new \Exception('Invalid image type');
        }

        $config = $this->imageConfig[$imageType];

        // Validate MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $config['mimeTypes'])) {
            throw new \Exception('Invalid file type. Expected: ' . implode(', ', $config['mimeTypes']));
        }

        // Build filename
        $filenameParts = [];
        foreach ($config['nameFields'] as $field) {
            $value = $params[$field] ?? '';
            if (empty($value)) {
                throw new \Exception("Field $field is required for filename");
            }
            $filenameParts[] = $value;
        }

        // Determine extension based on mime type if extensionByMime is configured
        $extension = $config['extension'];
        if (isset($config['extensionByMime'][$mimeType])) {
            $extension = $config['extensionByMime'][$mimeType];
        }

        $filename = $config['prefix'] . implode('-', $filenameParts) . $extension;
        $destinationDir = $this->documentRoot . $config['destination'];
        $destinationPath = $destinationDir . $filename;

        // Create directory if needed
        if (!is_dir($destinationDir)) {
            if (!mkdir($destinationDir, 0755, true)) {
                throw new \Exception('Cannot create destination directory');
            }
        }

        // Check if file already exists
        if (file_exists($destinationPath)) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $archiveName = $basename . '_' . date('Y-m-d') . '.' . $ext;

            if (!$overwrite) {
                throw new FileExistsException($filename, $archiveName);
            }

            // Archive the existing file before overwriting
            $archivePath = $destinationDir . $archiveName;
            if (!rename($destinationPath, $archivePath)) {
                throw new \Exception("Cannot archive existing file '$filename'");
            }
        }

        // Get image dimensions
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new \Exception('Invalid image file');
        }

        [$width, $height] = $imageInfo;

        // Check if resizing is needed
        $needsResize = ($width > $config['maxWidth'] || $height > $config['maxHeight']);

        if ($needsResize) {
            // Calculate new dimensions
            $ratio = min($config['maxWidth'] / $width, $config['maxHeight'] / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            $isPng = ($mimeType === 'image/png');

            $sourceImage = $isPng
                ? imagecreatefrompng($file->getPathname())
                : imagecreatefromjpeg($file->getPathname());

            if ($sourceImage === false) {
                throw new \Exception('Cannot process source image');
            }

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($isPng) {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefill($resizedImage, 0, 0, $transparent);
            }

            imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $result = $isPng
                ? imagepng($resizedImage, $destinationPath, 9)
                : imagejpeg($resizedImage, $destinationPath, 90);

            if (!$result) {
                throw new \Exception('Cannot save resized image');
            }

            return [
                'message' => 'Image resized and uploaded successfully',
                'filename' => $filename,
                'originalSize' => "{$width}x{$height}",
                'newSize' => "{$newWidth}x{$newHeight}",
                'resized' => true,
            ];
        } else {
            // No resizing needed, just move the file
            $file->move($destinationDir, $filename);

            return [
                'message' => 'Image uploaded successfully',
                'filename' => $filename,
                'size' => "{$width}x{$height}",
                'resized' => false,
            ];
        }
    }

    /**
     * Rename an image file
     */
    public function renameImage(string $imageType, string $currentName, string $newName): void
    {
        if (!isset($this->imageConfig[$imageType])) {
            throw new \Exception('Invalid image type');
        }

        $config = $this->imageConfig[$imageType];
        $directory = $this->documentRoot . $config['destination'];

        $currentPath = $directory . $currentName;
        $newPath = $directory . $newName;

        // Check current file exists
        if (!file_exists($currentPath)) {
            throw new \Exception("File '$currentName' does not exist");
        }

        // Check names are different
        if ($currentName === $newName) {
            throw new \Exception('New name must be different from current name');
        }

        // Check extensions match
        $currentExt = strtolower(pathinfo($currentName, PATHINFO_EXTENSION));
        $newExt = strtolower(pathinfo($newName, PATHINFO_EXTENSION));

        if ($currentExt !== $newExt) {
            throw new \Exception("Extension must remain the same: .$currentExt");
        }

        // Check new name doesn't already exist
        if (file_exists($newPath)) {
            throw new \Exception("File '$newName' already exists");
        }

        // Rename
        if (!rename($currentPath, $newPath)) {
            throw new \Exception('Cannot rename file');
        }
    }

    /**
     * Get list of images for a type
     */
    public function listImages(string $imageType): array
    {
        if (!isset($this->imageConfig[$imageType])) {
            throw new \Exception('Invalid image type');
        }

        $config = $this->imageConfig[$imageType];
        $directory = $this->documentRoot . $config['destination'];

        if (!is_dir($directory)) {
            return [];
        }

        $images = [];
        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory . $file;
            if (!is_file($filePath)) {
                continue;
            }

            // Check extension matches
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $expectedExt = strtolower(str_replace('.', '', pathinfo($config['extension'], PATHINFO_EXTENSION)));

            if ($ext !== $expectedExt) {
                continue;
            }

            // Check prefix matches (if any)
            if (!empty($config['prefix']) && !str_starts_with($file, $config['prefix'])) {
                continue;
            }

            $images[] = [
                'filename' => $file,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
            ];
        }

        usort($images, fn($a, $b) => strcmp($a['filename'], $b['filename']));

        return $images;
    }

    /**
     * List images for a given type, with optional prefix filter
     */
    public function listImagesForType(string $imageType, string $search = '', int $page = 1, int $limit = 50): array
    {
        if (!isset($this->imageConfig[$imageType])) {
            throw new \Exception('Invalid image type');
        }

        $config = $this->imageConfig[$imageType];
        $directory = $this->documentRoot . $config['destination'];

        if (!is_dir($directory)) {
            return ['total' => 0, 'items' => []];
        }

        $prefix = $config['prefix'];
        $allowedExts = [];
        if (isset($config['extensionByMime'])) {
            foreach ($config['extensionByMime'] as $ext) {
                $allowedExts[] = strtolower(ltrim($ext, '.'));
            }
        } else {
            $allowedExts[] = strtolower(ltrim(pathinfo($config['extension'], PATHINFO_EXTENSION), '.'));
        }
        $allowedExts = array_unique($allowedExts);

        $images = [];
        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory . $file;
            if (!is_file($filePath)) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                continue;
            }

            if (!empty($prefix) && !str_starts_with($file, $prefix)) {
                continue;
            }

            if ($search !== '' && stripos($file, $search) === false) {
                continue;
            }

            $images[] = [
                'filename' => $file,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath),
            ];
        }

        usort($images, fn($a, $b) => strcmp($a['filename'], $b['filename']));

        $total = count($images);
        $offset = ($page - 1) * $limit;
        $items = array_slice($images, $offset, $limit);

        return ['total' => $total, 'items' => $items];
    }

    /**
     * Import image from a remote URL: download, validate, resize if needed, save with normalized name
     */
    public function importImageFromUrl(string $imageType, string $url, array $params): array
    {
        if (!isset($this->imageConfig[$imageType])) {
            throw new \Exception('Invalid image type');
        }

        $config = $this->imageConfig[$imageType];

        // Build filename (same logic as uploadImage)
        $filenameParts = [];
        foreach ($config['nameFields'] as $field) {
            $value = $params[$field] ?? '';
            if (empty($value)) {
                throw new \Exception("Field $field is required for filename");
            }
            $filenameParts[] = $value;
        }

        // Download into temp file
        $tmpPath = tempnam(sys_get_temp_dir(), 'kpi_img_');
        if ($tmpPath === false) {
            throw new \Exception('Cannot create temp file');
        }

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 15,
                    'follow_location' => 1,
                    'max_redirects' => 5,
                    'user_agent' => 'KPI-ImageImport/1.0',
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $rawData = @file_get_contents($url, false, $context);
            if ($rawData === false) {
                throw new \Exception('Cannot download image from URL');
            }

            // Size limit: 10 MB
            if (strlen($rawData) > 10 * 1024 * 1024) {
                throw new \Exception('Image too large (max 10 MB)');
            }

            file_put_contents($tmpPath, $rawData);

            // Validate via magic bytes (getimagesize reads actual content)
            $imageInfo = @getimagesize($tmpPath);
            if ($imageInfo === false) {
                throw new \Exception('URL does not point to a valid image');
            }

            $mimeType = $imageInfo['mime'];
            // Normalize image/jpg -> image/jpeg
            if ($mimeType === 'image/jpg') {
                $mimeType = 'image/jpeg';
            }

            if (!in_array($mimeType, $config['mimeTypes'])) {
                throw new \Exception('Invalid image type. Expected: ' . implode(', ', $config['mimeTypes']));
            }

            // Determine extension
            $extension = $config['extension'];
            if (isset($config['extensionByMime'][$mimeType])) {
                $extension = $config['extensionByMime'][$mimeType];
            }

            $filename = $config['prefix'] . implode('-', $filenameParts) . $extension;
            $destinationDir = $this->documentRoot . $config['destination'];
            $destinationPath = $destinationDir . $filename;

            if (!is_dir($destinationDir)) {
                if (!mkdir($destinationDir, 0755, true)) {
                    throw new \Exception('Cannot create destination directory');
                }
            }

            if (file_exists($destinationPath)) {
                throw new \Exception("File '$filename' already exists. Use rename to change the existing file first.");
            }

            [$width, $height] = $imageInfo;
            $needsResize = ($width > $config['maxWidth'] || $height > $config['maxHeight']);
            $isPng = ($mimeType === 'image/png');

            if ($needsResize) {
                $ratio = min($config['maxWidth'] / $width, $config['maxHeight'] / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);

                $sourceImage = $isPng ? imagecreatefrompng($tmpPath) : imagecreatefromjpeg($tmpPath);
                if ($sourceImage === false) {
                    throw new \Exception('Cannot process downloaded image');
                }

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                if ($isPng) {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                    $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                    imagefill($resizedImage, 0, 0, $transparent);
                }

                imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                $result = $isPng
                    ? imagepng($resizedImage, $destinationPath, 9)
                    : imagejpeg($resizedImage, $destinationPath, 90);

                if (!$result) {
                    throw new \Exception('Cannot save resized image');
                }

                imagedestroy($sourceImage);
                imagedestroy($resizedImage);

                return [
                    'message' => 'Image imported and resized successfully',
                    'filename' => $filename,
                    'originalSize' => "{$width}x{$height}",
                    'newSize' => "{$newWidth}x{$newHeight}",
                    'resized' => true,
                ];
            } else {
                copy($tmpPath, $destinationPath);

                return [
                    'message' => 'Image imported successfully',
                    'filename' => $filename,
                    'size' => "{$width}x{$height}",
                    'resized' => false,
                ];
            }
        } finally {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }
    }

    /**
     * Delete an image file after checking it is not referenced in the database.
     *
     * For competition images (logo, bandeau, sponsor) the columns LogoLink / BandeauLink / SponsorLink
     * in kp_competition store just the filename — we refuse the delete when at least one row references it.
     * Club and nation images are identified only by naming convention so no DB check is possible.
     *
     * Returns an array with 'usedBy' key listing competitions that reference the file (empty = safe to delete).
     */
    public function deleteImage(string $imageType, string $filename, \Doctrine\DBAL\Connection $connection): array
    {
        if (!isset($this->imageConfig[$imageType])) {
            throw new \Exception('Invalid image type');
        }

        $config = $this->imageConfig[$imageType];
        $directory = $this->documentRoot . $config['destination'];
        $filePath = $directory . $filename;

        // Security: ensure the resolved path stays inside the destination directory
        $realDir = realpath($directory);
        $realFile = realpath($filePath);
        if ($realDir === false || $realFile === false || !str_starts_with($realFile, $realDir . DIRECTORY_SEPARATOR)) {
            throw new \Exception('Invalid filename');
        }

        if (!file_exists($filePath)) {
            throw new \Exception("File '$filename' does not exist");
        }

        // Check usage in DB for competition image types
        $columnMap = [
            'logo_competition'    => 'LogoLink',
            'bandeau_competition' => 'BandeauLink',
            'sponsor_competition' => 'SponsorLink',
        ];

        if (isset($columnMap[$imageType])) {
            $col = $columnMap[$imageType];
            $stmt = $connection->prepare(
                "SELECT Code, Code_saison FROM kp_competition WHERE $col = ? LIMIT 10"
            );
            $result = $stmt->executeQuery([$filename]);
            $rows = $result->fetchAllAssociative();
            if (!empty($rows)) {
                $usedBy = array_map(fn($r) => $r['Code'] . ' (' . $r['Code_saison'] . ')', $rows);
                return ['deleted' => false, 'usedBy' => $usedBy];
            }
        }

        if (!unlink($filePath)) {
            throw new \Exception('Cannot delete file');
        }

        return ['deleted' => true, 'usedBy' => []];
    }

    /**
     * Get image types configuration for frontend display
     */
    public function getImageTypesConfig(): array
    {
        $types = [];
        foreach ($this->imageConfig as $key => $config) {
            $types[$key] = [
                'label' => $config['label'] ?? $key,
                'formatHint' => $config['formatHint'] ?? '',
                'accept' => $this->mimeTypesToAccept($config['mimeTypes']),
                'maxWidth' => $config['maxWidth'],
                'maxHeight' => $config['maxHeight'],
                'requiredFields' => $config['nameFields'],
            ];
        }
        return $types;
    }

    /**
     * Convert MIME types to HTML accept attribute value
     */
    private function mimeTypesToAccept(array $mimeTypes): string
    {
        // Deduplicate and convert to accept format
        $accepts = [];
        foreach ($mimeTypes as $mime) {
            if ($mime === 'image/jpg') {
                $accepts['image/jpeg'] = true;
            } else {
                $accepts[$mime] = true;
            }
        }
        return implode(',', array_keys($accepts));
    }
}
