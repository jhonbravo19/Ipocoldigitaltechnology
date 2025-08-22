<?php
namespace App\Support;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class DocxToPdf
{
    public static function convert(string $docxAbs, string $pdfAbs): void
    {
        // 1) Configurar el renderer de PDF a mPDF
        Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
        Settings::setPdfRendererPath(base_path('vendor/mpdf/mpdf'));

        // 2) Cargar DOCX
        $phpWord = IOFactory::load($docxAbs);

        // 3) Writer PDF directo (sin HTML intermedio)
        $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
        if (!is_dir(dirname($pdfAbs))) {
            mkdir(dirname($pdfAbs), 0775, true);
        }
        $pdfWriter->save($pdfAbs);
    }
}
