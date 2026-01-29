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
            'mimeTypes' => ['image/jpeg', 'image/jpg'],
            'maxWidth' => 1000,
            'maxHeight' => 1000,
            'destination' => '/img/logo/',
            'nameFields' => ['codeCompetition', 'saison'],
        ],
        'bandeau_competition' => [
            'prefix' => 'B-',
            'extension' => '.jpg',
            'mimeTypes' => ['image/jpeg', 'image/jpg'],
            'maxWidth' => 2480,
            'maxHeight' => 250,
            'destination' => '/img/logo/',
            'nameFields' => ['codeCompetition', 'saison'],
        ],
        'sponsor_competition' => [
            'prefix' => 'S-',
            'extension' => '.jpg',
            'mimeTypes' => ['image/jpeg', 'image/jpg'],
            'maxWidth' => 2480,
            'maxHeight' => 250,
            'destination' => '/img/logo/',
            'nameFields' => ['codeCompetition', 'saison'],
        ],
        'logo_club' => [
            'prefix' => '',
            'extension' => '-logo.png',
            'mimeTypes' => ['image/png'],
            'maxWidth' => 200,
            'maxHeight' => 200,
            'destination' => '/img/KIP/logo/',
            'nameFields' => ['numeroClub'],
        ],
        'logo_nation' => [
            'prefix' => '',
            'extension' => '.png',
            'mimeTypes' => ['image/png'],
            'maxWidth' => 200,
            'maxHeight' => 200,
            'destination' => '/img/Nations/',
            'nameFields' => ['codeNation'],
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

        $filename = $config['prefix'] . implode('-', $filenameParts) . $config['extension'];
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

            // Create source image
            if (in_array('image/png', $config['mimeTypes'])) {
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
            if (in_array('image/png', $config['mimeTypes'])) {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefill($resizedImage, 0, 0, $transparent);
            }

            // Resize
            imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save resized image
            if (in_array('image/png', $config['mimeTypes'])) {
                $result = imagepng($resizedImage, $destinationPath, 9);
            } else {
                $result = imagejpeg($resizedImage, $destinationPath, 90);
            }

            imagedestroy($sourceImage);
            imagedestroy($resizedImage);

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
}
