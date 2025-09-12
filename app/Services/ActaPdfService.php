<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplateConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ActaPdfService
{
    /**
     * Genera el PDF del acta en storage/app/public/certificates/{id}_acta.pdf
     * y retorna la ruta web "storage/certificates/{id}_acta.pdf"
     */
    public static function generate(Certificate $certificate): ?string
    {
        try {
            $certificate->loadMissing(['course','holder']);
            $config = CertificateTemplateConfig::getActiveConfig();

            // 1) Render HTML
            $html = view('pdfs.acta_plain', [
                'certificate' => $certificate,
                'config'      => $config,
            ])->render();

            // 2) Generar PDF
            Storage::disk('public')->makeDirectory('certificates');
            $pdfAbs = Storage::disk('public')->path("certificates/{$certificate->id}_acta.pdf");
            $pdfWeb = "storage/certificates/{$certificate->id}_acta.pdf";

            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait'); // cámbialo a 'landscape' si lo prefieres

            file_put_contents($pdfAbs, $pdf->output());

            Log::info("[ACTA PDF] generado: {$pdfWeb}");
            return $pdfWeb;

        } catch (\Throwable $e) {
            Log::error("[ACTA PDF] Error: ".$e->getMessage());
            return null;
        }
    }
}
