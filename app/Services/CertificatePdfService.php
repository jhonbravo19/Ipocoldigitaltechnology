<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplateConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificatePdfService
{
    /**
     * Genera el PDF del certificado, lo guarda en storage/app/public/certificates
     * (servible por /storage/...) y guarda la ruta web en certificate_file_path.
     *
     * @return string|null  Ruta web tipo "storage/certificates/{id}_certificate.pdf"
     */
    public static function generate(
        Certificate $certificate,
        string $paper = 'a4',
        string $orientation = 'landscape'
    ): ?string {
        @ini_set('memory_limit', '512M');
        @set_time_limit(60);

        try {
            // 1) Cargar relaciones y config
            $certificate->loadMissing(['course', 'holder']);
            $config = CertificateTemplateConfig::getActiveConfig();

            // 2) Asegurar carpeta pública
            Storage::disk('public')->makeDirectory('certificates');

            // 3) Intento principal: render de la vista oficial
            $pdf = Pdf::loadView('pdfs.certificate_plain', [
                    'certificate' => $certificate,
                    'config'      => $config,
                ])
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => true,          // permite http/https en <img>
                    'chroot'               => public_path(), // permite leer public_path(...)
                    'dpi'                  => 110,
                    'defaultFont'          => 'DejaVu Sans',
                ])
                ->setPaper($paper, $orientation);

            $rel = "certificates/{$certificate->id}_certificate.pdf";        // relativo al disk 'public'
            $abs = Storage::disk('public')->path($rel);                      // ruta absoluta
            $pdf->save($abs);

            $ok   = Storage::disk('public')->exists($rel);
            $size = $ok ? (Storage::disk('public')->size($rel) ?: 0) : 0;

            Log::info("[CERT PDF] Guardado (v1) {$rel} ({$size} bytes)");

            // 4) Fallback si guarda vacío (suele indicar problema en la vista/CSS/recursos)
            if (!$ok || $size < 500) {
                Log::warning("[CERT PDF] Fallback simple por bytes insuficientes en {$rel}");

                $htmlFallback = self::fallbackHtml($certificate);
                $pdf2 = Pdf::loadHTML($htmlFallback)
                    ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled'      => false,
                        'chroot'               => public_path(),
                        'dpi'                  => 110,
                        'defaultFont'          => 'DejaVu Sans',
                    ])
                    ->setPaper($paper, $orientation);

                $pdf2->save($abs);

                $ok   = Storage::disk('public')->exists($rel);
                $size = $ok ? (Storage::disk('public')->size($rel) ?: 0) : 0;
                Log::info("[CERT PDF] Guardado (fallback) {$rel} ({$size} bytes)");

                if (!$ok || $size < 300) {
                    Log::error("[CERT PDF] Archivo vacío tras fallback: {$rel}");
                    return null;
                }
            }

            // 5) Persistir ruta web en la BD y devolverla
            $web = "storage/{$rel}";
            $certificate->certificate_file_path = $web;
            $certificate->save();

            Log::info("[CERT PDF] OK → {$web} (cert_id={$certificate->id})");

            return $web;
        } catch (\Throwable $e) {
            Log::error("[CERT PDF] ERROR: {$e->getMessage()} @{$e->getFile()}:{$e->getLine()}");
            return null;
        }
    }

    /**
     * HTML mínimo de respaldo (sin imágenes ni CSS externos).
     */   
    protected static function fallbackHtml(Certificate $c): string
    {
        $id      = e($c->id);
        $alumno  = e(optional($c->holder)->full_name ?? optional($c->holder)->first_names.' '.optional($c->holder)->last_names);
        $curso   = e(optional($c->course)->name ?? 'Curso');
        $emitido = e(optional($c->issue_date)->format('Y-m-d') ?? now()->format('Y-m-d'));

        return <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 24px; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
  h1   { font-size: 20px; text-align:center; margin: 0 0 8px; }
  .box { border:1px solid #bbb; border-radius:6px; padding:12px; margin-top:10px; }
  .row { display: table; width:100%; }
  .col { display: table-cell; width:50%; vertical-align: top; }
  .muted { color:#666; }
</style>
</head>
<body>
  <h1>Certificado #{$id}</h1>
  <div class="row">
    <div class="col">
      <div class="box">
        <b>Titular:</b> {$alumno}<br>
        <b>Curso:</b> {$curso}<br>
        <b>Emitido:</b> {$emitido}
      </div>
    </div>
    <div class="col" style="text-align:right">
      <div class="muted">Vista simple de respaldo</div>
    </div>
  </div>
</body>
</html>
HTML;
    }
}
