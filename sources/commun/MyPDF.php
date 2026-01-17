<?php
/**
 * MyPDF - Wrapper mPDF compatible avec l'API FPDF existante
 *
 * Cette classe permet une migration progressive de FPDF vers mPDF
 * tout en gardant la compatibilité avec le code existant.
 *
 * Avantages mPDF:
 * - Support UTF-8 natif (fini les problèmes d'encodage)
 * - Support HTML/CSS
 * - Activement maintenu (PHP 7.4+ et PHP 8.3+ compatible)
 * - Font subsetting automatique
 *
 * @author  Laurent Garrigue / Claude Code
 * @date    2025-10-19
 * @version 2.0 - mPDF v8.2+ compatible
 */

// Charger mPDF v8.x via l'autoloader Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    die('mPDF not installed. Run: composer require mpdf/mpdf');
}

use Mpdf\Mpdf;

/**
 * Classe MyPDF - Wrapper de compatibilité FPDF → mPDF
 * Compatible avec mPDF v8.x (PHP 7.4+ et PHP 8.3+)
 */
class MyPDF extends Mpdf
{
    // Propriétés FPDF compatibles
    public $x0;

    /**
     * Constructeur compatible FPDF
     *
     * @param string $orientation P=Portrait, L=Landscape
     * @param string $unit        mm, pt, cm, in (ignoré pour mPDF v8)
     * @param mixed  $format      A4, A3, Letter, etc.
     */
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        // Convertir orientation FPDF vers mPDF v8
        $mpdfOrientation = ($orientation == 'L') ? 'L' : 'P';

        // Configuration mPDF v8 (array config)
        $config = [
            'mode' => 'utf-8',
            'format' => $format,
            'orientation' => $mpdfOrientation,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'default_font_size' => 12,
            'default_font' => 'arial',
            'tempDir' => sys_get_temp_dir() . '/mpdf',
        ];

        // Initialiser mPDF v8
        parent::__construct($config);
    }

    /**
     * NOTE: La méthode Open() de FPDF n'est PAS implémentée ici
     * car elle interfère avec l'initialisation interne de mPDF.
     * Open() est obsolète dans FPDF 1.8+ et n'est pas nécessaire avec mPDF.
     * Si votre code appelle Open(), vous pouvez simplement supprimer cet appel.
     */

    /**
     * SetFont - Compatible FPDF et mPDF v8
     *
     * @param string $family Arial, Times, Courier, etc.
     * @param string $style  B=Bold, I=Italic, U=Underline, BI=Bold+Italic
     * @param int    $size   Taille en points
     * @param bool   $write  (mPDF v8 parameter)
     * @param bool   $forcewrite (mPDF v8 parameter)
     */
    public function SetFont($family, $style = '', $size = 0, $write = true, $forcewrite = false)
    {
        // Ensure $family is a non-null string to avoid PHP deprecation in strtolower()
        if ($family === null || $family === '') {
            $family = (isset($this->default_font) && $this->default_font) ? $this->default_font : 'Arial';
        }
        
        // Cast to string to ensure type safety
        $family = (string)$family;
        $style = (string)$style;

        // mPDF v8 supporte déjà le style FPDF (B, I, U, BI, etc.)
        parent::SetFont($family, $style, $size, $write, $forcewrite);
    }

    // ========================================================================
    // MÉTADONNÉES PDF - Protection contre les valeurs null (PHP 8.1+ deprecation)
    // ========================================================================

    /**
     * SetTitle - Définir le titre du document PDF
     * @param string $title Titre du document
     */
    public function SetTitle($title)
    {
        parent::SetTitle((string)($title ?? ''));
    }

    /**
     * SetSubject - Définir le sujet du document PDF
     * @param string $subject Sujet du document
     */
    public function SetSubject($subject)
    {
        parent::SetSubject((string)($subject ?? ''));
    }

    /**
     * SetKeywords - Définir les mots-clés du document PDF
     * @param string $keywords Mots-clés séparés par des virgules
     */
    public function SetKeywords($keywords)
    {
        parent::SetKeywords((string)($keywords ?? ''));
    }

    /**
     * SetAuthor - Définir l'auteur du document PDF
     * @param string $author Nom de l'auteur
     */
    public function SetAuthor($author)
    {
        parent::SetAuthor((string)($author ?? ''));
    }

    /**
     * SetCreator - Définir le créateur du document PDF
     * @param string $creator Nom du créateur/application
     */
    public function SetCreator($creator)
    {
        parent::SetCreator((string)($creator ?? ''));
    }

    // ========================================================================
    // CELLULES - Protection contre les valeurs null (PHP 8.1+ deprecation)
    // ========================================================================

    /**
     * Cell - Écrire une cellule avec protection contre null
     * Compatible FPDF et mPDF v8
     * Signature identique à mPDF::Cell()
     */
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '', $currentx = 0, $lcpaddingL = 0, $lcpaddingR = 0, $valign = 'M', $spanfill = 0, $exactWidth = false, $OTLdata = false, $textvar = 0, $lineBox = false)
    {
        // Convertir null en chaîne vide pour le texte et l'alignement
        $txt = (string)($txt ?? '');
        $align = (string)($align ?? '');
        $link = (string)($link ?? '');

        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link, $currentx, $lcpaddingL, $lcpaddingR, $valign, $spanfill, $exactWidth, $OTLdata, $textvar, $lineBox);
    }

    /**
     * MultiCell - Écrire une cellule multi-lignes avec protection contre null
     * Compatible FPDF et mPDF v8
     */
    public function MultiCell($w, $h, $txt = '', $border = 0, $align = 'J', $fill = 0, $link = '', $directionRTL = false, $encoded = false, $OTLdata = false, $maxrows = false)
    {
        // Convertir null en chaîne vide pour le texte
        $txt = (string)($txt ?? '');
        $align = (string)($align ?? 'J');
        $link = (string)($link ?? '');

        parent::MultiCell($w, $h, $txt, $border, $align, $fill, $link, $directionRTL, $encoded, $OTLdata, $maxrows);
    }

    /**
     * Text - Écrire du texte à une position avec protection contre null
     * Signature identique à mPDF::Text()
     */
    public function Text($x, $y, $txt, $OTLdata = [], $textvar = 0, $aixextra = '', $coordsys = '', $return = false)
    {
        // Convertir null en chaîne vide pour le texte
        $txt = (string)($txt ?? '');

        parent::Text($x, $y, $txt, $OTLdata, $textvar, $aixextra, $coordsys, $return);
    }

    /**
     * SetHTMLHeader - Set page header with HTML content
     * Sanitizes HTML to prevent null values in CSS processing
     *
     * @param string $header HTML content for header
     * @param string $OE Optional element
     * @param bool $write Whether to write immediately
     */
    public function SetHTMLHeader($header = '', $OE = '', $write = false)
    {
        // Sanitize HTML to prevent null values in CSS attributes
        $header = (string)$header;
        if (!empty($header)) {
            $header = $this->sanitizeHTML($header);
        }
        // Suppress deprecated warnings from mPDF's CSS processing
        @parent::SetHTMLHeader($header, $OE, $write);
    }

    /**
     * SetHTMLFooter - Set page footer with HTML content
     * Sanitizes HTML to prevent null values in CSS processing
     *
     * @param string $footer HTML content for footer
     * @param string $OE Optional element
     * @param bool $write Whether to write immediately
     */
    public function SetHTMLFooter($footer = '', $OE = '', $write = false)
    {
        // Sanitize HTML to prevent null values in CSS processing
        $footer = (string)$footer;
        if (!empty($footer)) {
            $footer = $this->sanitizeHTML($footer);
        }
        // Suppress deprecated warnings from mPDF's CSS processing
        @parent::SetHTMLFooter($footer, $OE, $write);
    }

    /**
     * WriteHTML - Override to sanitize HTML/CSS before processing
     * Accepts all mPDF v8 WriteHTML parameters
     *
     * @param string $html HTML content to write
     * @param int $mode HTML parser mode
     * @param bool $init Initialize
     * @param bool $close Close
     */
    public function WriteHTML($html, $mode = \Mpdf\HTMLParserMode::DEFAULT_MODE, $init = true, $close = true)
    {
        // Sanitize HTML to prevent null values in CSS attributes
        $html = (string)$html;
        if (!empty($html)) {
            $html = $this->sanitizeHTML($html);
        }
        
        // Suppress deprecated warnings from mPDF's CSS processing
        @parent::WriteHTML($html, $mode, $init, $close);
    }

    /**
     * sanitizeHTML - Remove or replace problematic patterns in HTML
     * Prevents null values from being passed to strtolower() in mPDF CSS processing
     *
     * @param string $html HTML content to sanitize
     * @return string Sanitized HTML
     */
    private function sanitizeHTML($html)
    {
        // Convert to string if not already
        $html = (string)$html;
        
        // Ensure all attributes have non-empty values
        // This is the key to avoiding null values passed to strtolower()
        
        // 1. Replace empty style attributes with safe defaults
        $html = preg_replace('/style\s*=\s*["\'][\s]*["\']/i', 'style="font-family:Arial"', $html);
        
        // 2. Remove null-like values in common attributes
        $html = preg_replace('/\s+(class|id|style|lang)\s*=\s*["\']?(null|undefined|empty|NULL)["\']?/i', '', $html);
        
        // 3. Ensure ALL style attributes have at least a minimal valid value
        $html = preg_replace_callback(
            '/style\s*=\s*["\']([^"\']*?)["\']/i',
            function($matches) {
                $styleContent = trim($matches[1] ?? '');
                if (empty($styleContent)) {
                    return 'style="font-family:Arial"';
                }
                return $matches[0];
            },
            $html
        );
        
        // 4. Remove any remaining style="" (empty value) before CSS parsing
        $html = preg_replace('/\s+style\s*=\s*["\'][\s]*["\']/i', '', $html);
        
        // 5. Ensure no null values in attribute values (safeguard for CSS)
        $html = preg_replace_callback(
            '/\s+(\w+)\s*=\s*["\']([^"\']*?)["\']/i',
            function($matches) {
                $attrName = $matches[1];
                $attrValue = $matches[2] ?? '';
                
                // Cast to string and trim
                $attrValue = (string)$attrValue;
                $attrValue = trim($attrValue);
                
                // If attribute is empty or only whitespace, remove it
                if (empty($attrValue) && !in_array(strtolower($attrName), ['style'])) {
                    return '';
                }
                
                // For style specifically, add minimal default if empty
                if (strtolower($attrName) === 'style' && empty($attrValue)) {
                    return ' style="font-family:Arial"';
                }
                
                return $matches[0];
            },
            $html
        );
        
        return $html;
    }

    /**
     * Les méthodes suivantes sont héritées directement de mPDF v8
     * et sont compatibles FPDF :
     *
     * - Cell($w, $h, $txt, $border, $ln, $align, $fill, $link)
     * - MultiCell($w, $h, $txt, $border, $align, $fill, $link)
     * - Ln($h, $collapsible)
     * - Image($file, $x, $y, $w, $h, $type, $link)
     * - SetTextColor($r, $g, $b)
     * - SetDrawColor($r, $g, $b)
     * - SetFillColor($r, $g, $b)
     * - AddPage()
     * - SetXY($x, $y)
     * - SetX($x)
     * - SetY($y)
     * - Line($x1, $y1, $x2, $y2)
     * - Rect($x, $y, $w, $h, $style)
     */

    /**
     * Output - mPDF v8 native
     *
     * IMPORTANT : mPDF v8 utilise des constantes Destination au lieu des codes FPDF
     *
     * Migration FPDF → mPDF :
     * - FPDF 'I' → \Mpdf\Output\Destination::INLINE
     * - FPDF 'D' → \Mpdf\Output\Destination::DOWNLOAD
     * - FPDF 'F' → \Mpdf\Output\Destination::FILE
     * - FPDF 'S' → \Mpdf\Output\Destination::STRING_RETURN
     *
     * Exemple :
     *   $pdf->Output('fichier.pdf', \Mpdf\Output\Destination::DOWNLOAD);
     *
     * @param string $name Nom du fichier
     * @param string $dest Destination (constante Destination)
     * @return string|void
     */
    // Méthode Output héritée directement de mPDF v8 - PAS d'override pour éviter les conflits
}
