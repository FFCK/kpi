<?php

/**
 * QRCode Generator - Modern wrapper using endroid/qr-code
 *
 * This class provides backward compatibility with the old QRcode library
 * while using the modern endroid/qr-code library underneath.
 *
 * @author  KPI Migration Team
 * @version 2.0 (endroid/qr-code wrapper)
 * @license LGPL
 */

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Label\Label;

if (!defined('__CLASS_QRCODE__')) {
    define('__CLASS_QRCODE__', '2.0');

    class QRcode
    {
        private string $data;
        private ErrorCorrectionLevel $errorCorrectionLevel;
        private int $size = 300;
        private bool $borderEnabled = true;

        /**
         * Constructor
         *
         * @param string $data The data to encode in the QR code
         * @param string $level Error correction level: L (Low), M (Medium), Q (Quartile), H (High)
         */
        public function __construct(string $data, string $level = 'L')
        {
            $this->data = $data;
            $this->errorCorrectionLevel = match (strtoupper($level)) {
                'L' => ErrorCorrectionLevel::Low,
                'M' => ErrorCorrectionLevel::Medium,
                'Q' => ErrorCorrectionLevel::Quartile,
                'H' => ErrorCorrectionLevel::High,
                default => ErrorCorrectionLevel::Low,
            };
        }

        /**
         * Disable border around QR code
         */
        public function disableBorder(): void
        {
            $this->borderEnabled = false;
        }

        /**
         * Create PNG image resource from QR code
         *
         * @param int $size Size of the QR code in pixels
         * @return \GdImage|false GD image resource or false on failure
         */
        public function createPNG(int $size = 300)
        {
            try {
                $this->size = $size;

                $result = Builder::create()
                    ->writer(new PngWriter())
                    ->data($this->data)
                    ->encoding(new Encoding('UTF-8'))
                    ->errorCorrectionLevel($this->errorCorrectionLevel)
                    ->size($size)
                    ->margin($this->borderEnabled ? 10 : 0)
                    ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                    ->build();

                // Convert result to GD image resource
                return imagecreatefromstring($result->getString());
            } catch (\Exception $e) {
                error_log("QRCode generation error: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Add logo to QR code image
         *
         * @param \GdImage $qrImage The QR code image resource
         * @param string $logoPath Path to the logo file
         * @param float $ratio Logo size ratio (0-1)
         * @return \GdImage|false Modified image or false on failure
         */
        public function addLogo($qrImage, string $logoPath, float $ratio = 0.3)
        {
            try {
                if (!file_exists($logoPath)) {
                    error_log("Logo file not found: $logoPath");
                    return $qrImage; // Return original image if logo not found
                }

                // Get QR code dimensions
                $qrWidth = imagesx($qrImage);
                $qrHeight = imagesy($qrImage);

                // Load logo
                $logoExt = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                $logo = match ($logoExt) {
                    'jpg', 'jpeg' => imagecreatefromjpeg($logoPath),
                    'png' => imagecreatefrompng($logoPath),
                    'gif' => imagecreatefromgif($logoPath),
                    default => false,
                };

                if ($logo === false) {
                    error_log("Failed to load logo: $logoPath");
                    return $qrImage;
                }

                // Calculate logo dimensions
                $logoWidth = imagesx($logo);
                $logoHeight = imagesy($logo);
                $newLogoWidth = (int)($qrWidth * $ratio);
                $newLogoHeight = (int)($logoHeight * ($newLogoWidth / $logoWidth));

                // Calculate center position
                $logoX = (int)(($qrWidth - $newLogoWidth) / 2);
                $logoY = (int)(($qrHeight - $newLogoHeight) / 2);

                // Add white background for logo
                $white = imagecolorallocate($qrImage, 255, 255, 255);
                $padding = 5;
                imagefilledrectangle(
                    $qrImage,
                    $logoX - $padding,
                    $logoY - $padding,
                    $logoX + $newLogoWidth + $padding,
                    $logoY + $newLogoHeight + $padding,
                    $white
                );

                // Resize and merge logo
                imagecopyresampled(
                    $qrImage,
                    $logo,
                    $logoX,
                    $logoY,
                    0,
                    0,
                    $newLogoWidth,
                    $newLogoHeight,
                    $logoWidth,
                    $logoHeight
                );

                imagedestroy($logo);
                return $qrImage;
            } catch (\Exception $e) {
                error_log("Error adding logo: " . $e->getMessage());
                return $qrImage;
            }
        }

        /**
         * Convert GD image to base64 data URL
         *
         * @param \GdImage $image The image resource
         * @return string Base64 data URL
         */
        public function getBase64Url($image): string
        {
            try {
                ob_start();
                imagepng($image);
                $imageData = ob_get_clean();
                return 'data:image/png;base64,' . base64_encode($imageData);
            } catch (\Exception $e) {
                error_log("Error creating base64 URL: " . $e->getMessage());
                return '';
            }
        }

        /**
         * Display QR code directly as PNG
         */
        public function displayPNG(): void
        {
            try {
                $result = Builder::create()
                    ->writer(new PngWriter())
                    ->data($this->data)
                    ->encoding(new Encoding('UTF-8'))
                    ->errorCorrectionLevel($this->errorCorrectionLevel)
                    ->size($this->size)
                    ->margin($this->borderEnabled ? 10 : 0)
                    ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                    ->build();

                header('Content-Type: ' . $result->getMimeType());
                echo $result->getString();
            } catch (\Exception $e) {
                error_log("Error displaying PNG: " . $e->getMessage());
            }
        }

        /**
         * Display QR code in FPDF/mPDF
         *
         * @param object $pdf FPDF or mPDF instance
         * @param float $x X position
         * @param float $y Y position
         * @param float $w Width
         * @param array $background Background color (not used in this implementation)
         * @param array $color QR code color (not used in this implementation)
         */
        public function displayFPDF($pdf, float $x, float $y, float $w, array $background = [255, 255, 255], array $color = [0, 0, 0]): void
        {
            try {
                $result = Builder::create()
                    ->writer(new PngWriter())
                    ->data($this->data)
                    ->encoding(new Encoding('UTF-8'))
                    ->errorCorrectionLevel($this->errorCorrectionLevel)
                    ->size(300) // Fixed size for PDF
                    ->margin($this->borderEnabled ? 10 : 0)
                    ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                    ->build();

                // Create temporary file for the QR code
                $tempFile = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
                file_put_contents($tempFile, $result->getString());

                // Add image to PDF
                $pdf->Image($tempFile, $x, $y, $w, 0, 'PNG');

                // Clean up temporary file
                unlink($tempFile);
            } catch (\Exception $e) {
                error_log("Error displaying FPDF: " . $e->getMessage());
            }
        }

        /**
         * Display QR code as HTML table (legacy compatibility)
         */
        public function displayHTML(): void
        {
            try {
                // For HTML display, we'll use the PNG as base64
                $image = $this->createPNG($this->size);
                if ($image !== false) {
                    $dataUrl = $this->getBase64Url($image);
                    imagedestroy($image);
                    echo '<img src="' . htmlspecialchars($dataUrl) . '" alt="QR Code" />';
                }
            } catch (\Exception $e) {
                error_log("Error displaying HTML: " . $e->getMessage());
            }
        }
    }
}
