<?php

namespace App\Services;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CardPdfService
{
    /**
     * Genera el PDF del CARNET y guarda la ruta web en `card_file_path`.
     * Devuelve: "storage/certificates/{id}_card.pdf" o null si falla.
     */
    public static function generate(Certificate $certificate): ?string
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(60);

        try {
            // Cargar relaciones mínimas
            $certificate->loadMissing(['course','holder']);

            // Asegura carpeta pública
            Storage::disk('public')->makeDirectory('certificates');

            // --- Tamaño real de tarjeta (CR80): 85.6 x 54 mm ---
            // DomPDF usa puntos (pt). 1in = 25.4mm, 72pt = 1in
            // 85.6mm -> ~242.65pt ; 54mm -> ~153.07pt
            $cardSize = [0, 0, 242.65, 153.07]; // ancho x alto

            // Render de la vista (usa CSS inline o public_path en el blade)
            $pdf = Pdf::loadView('pdfs.card_plain', [
                    'certificate' => $certificate,
                ])
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => true,          // http/https si los usas en <img>
                    'chroot'               => public_path(), // permite leer public_path(...)
                    'dpi'                  => 110,
                    'defaultFont'          => 'DejaVu Sans',
                ])
                ->setPaper($cardSize, 'landscape');

            $rel = "certificates/{$certificate->id}_card.pdf";   // relativo al disk 'public'
            $abs = Storage::disk('public')->path($rel);

            $pdf->save($abs);

            $ok   = Storage::disk('public')->exists($rel);
            $size = $ok ? (Storage::disk('public')->size($rel) ?: 0) : 0;
            Log::info("[CARD PDF] Guardado (v1) {$rel} ({$size} bytes)");

            // Fallback mínimo si quedó vacío (típico por CSS/imagenes en la vista)
            if (!$ok || $size < 200) {
                Log::warning("[CARD PDF] Fallback por bytes insuficientes en {$rel}");

                $html = self::fallbackHtml($certificate);
                $pdf2 = Pdf::loadHTML($html)
                    ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled'      => false,
                        'chroot'               => public_path(),
                        'dpi'                  => 110,
                        'defaultFont'          => 'DejaVu Sans',
                    ])
                    ->setPaper($cardSize, 'landscape');

                $pdf2->save($abs);

                $ok   = Storage::disk('public')->exists($rel);
                $size = $ok ? (Storage::disk('public')->size($rel) ?: 0) : 0;
                Log::info("[CARD PDF] Guardado (fallback) {$rel} ({$size} bytes)");

                if (!$ok || $size < 150) {
                    Log::error("[CARD PDF] Archivo vacío tras fallback: {$rel}");
                    return null;
                }
            }

            // Persistir ruta web y devolver
            $web = "storage/{$rel}";
            $certificate->card_file_path = $web;
            $certificate->save();

            Log::info("[CARD PDF] OK → {$web} (cert_id={$certificate->id})");
            return $web;

        } catch (\Throwable $e) {
            Log::error("[CARD PDF] ERROR: {$e->getMessage()} @{$e->getFile()}:{$e->getLine()}");
            return null;
        }
    }

    protected static function fallbackHtml(Certificate $c): string
    {
        $id     = e($c->id);
        $nombre = e(optional($c->holder)->full_name
            ?? trim((optional($c->holder)->first_names.' '.optional($c->holder)->last_names) ?? '')
        );
        $curso  = e(optional($c->course)->name ?? 'Curso');

        return <<<HTML
<!doctype html>
<html><head><meta charset="utf-8"><style>
  @page { margin: 8px; }
  body  { font-family: DejaVu Sans, sans-serif; font-size: 10px; color:#111; }
  .card { width:100%; height:100%; border:1px solid #999; border-radius:8px; padding:8px; }
  h1 { font-size: 14px; margin:0 0 6px; text-align:center; }
  .muted { color:#666; font-size:9px; }
  .row { display: table; width:100% }
  .col { display: table-cell; width:50%; vertical-align: top; }
</style></head>
<body>
  <div class="card">
    <h1>Carnet #{$id}</h1>
    <div><b>Titular:</b> {$nombre}</div>
    <div><b>Curso:</b> {$curso}</div>
    <div class="muted">Vista simple de respaldo</div>
  </div>
</body></html>
HTML;
    }
}
