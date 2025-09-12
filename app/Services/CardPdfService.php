<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplateConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CardPdfService
{
    /**
     * Genera el PDF del carnet (tarjeta) con DomPDF.
     * Retorna ruta WEB: "storage/certificates/{id}_card.pdf"
     */
    public static function generate(Certificate $certificate): ?string
    {
        try {
            // Cargar config activa (igual que en certificatepdfservice)
            $config = CertificateTemplateConfig::getActiveConfig();

            // 1) Renderizar HTML
            $html = view('pdfs.card_plain', [
                'certificate' => $certificate->loadMissing(['course','holder']),
                'config'      => $config,
            ])->render();

            // Carpeta destino en disco 'public'
            Storage::disk('public')->makeDirectory('certificates');

            $pdfAbs = Storage::disk('public')->path("certificates/{$certificate->id}_card.pdf");
            $pdfWeb = "storage/certificates/{$certificate->id}_card.pdf";

            // 2) Generar PDF (tamaño tarjeta – ajusta a tu diseño)
            //   landscape o portrait según necesites
            $pdf = Pdf::loadHTML($html)
                ->setPaper('credit-card', 'landscape'); // puedes usar [86, 54] mm como custom

            // Para tamaños custom en mm:
            // $custom = [0, 0, 242.65, 153.07]; // ~86x54mm en puntos (72 dpi * pulgadas)
            // $pdf->setPaper($custom, 'landscape');

            file_put_contents($pdfAbs, $pdf->output());

            Log::info("[CARD PDF] generado: {$pdfWeb}");

            return $pdfWeb;

        } catch (\Throwable $e) {
            Log::error("[CARD PDF] Error: " . $e->getMessage());
            return null;
        }
    }
}
