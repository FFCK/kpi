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
        // mPDF v8 supporte déjà le style FPDF (B, I, U, BI, etc.)
        parent::SetFont($family, $style, $size, $write, $forcewrite);
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
