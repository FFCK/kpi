<?php

namespace App\Service;

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
    ];

    private string $documentRoot;

    public function __construct()
    {
        // Get document root from server or use default
        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 4);
    }

    /**
     * Upload an image with automatic resizing
     */
    public function uploadImage(string $imageType, UploadedFile $file, array $params): array
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
            throw new \Exception("File '$filename' already exists. Use rename to change the existing file first.");
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

            // Determine if this is a PNG based on actual mime type
            $isPng = ($mimeType === 'image/png');

            // Create source image
            if ($isPng) {
                $sourceImage = imagecreatefrompng($file->getPathname());
            } else {
                $sourceImage = imagecreatefromjpeg($file->getPathname());
            }

            if ($sourceImage === false) {
                throw new \Exception('Cannot process source image');
            }

            // Create resized image
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG
            if ($isPng) {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefill($resizedImage, 0, 0, $transparent);
            }

            // Resize
            imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save resized image
            if ($isPng) {
                $result = imagepng($resizedImage, $destinationPath, 9);
            } else {
                $result = imagejpeg($resizedImage, $destinationPath, 90);
            }

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
