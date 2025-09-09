<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplateConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificatePdfService
{
    /**
     * Genera el PDF del certificado y devuelve una RUTA WEB tipo:
     *   storage/certificates/{id}_certificate.pdf
     * Esto se sirve vía el symlink /public/storage (php artisan storage:link)
     */
    public static function generate(
        Certificate $certificate,
        string $paper = 'a4',
        string $orientation = 'landscape'
    ): ?string {
        @ini_set('memory_limit', '512M');
        @set_time_limit(60);

        $config = CertificateTemplateConfig::getActiveConfig();

        // ===== 1) Render del Blade a HTML =====
        // Usa la vista "bonita" por defecto; si aún estás ajustando fondo,
        // puedes cambiar temporalmente a 'pdfs.certificate_plain'
        $html = View::make('pdfs.certificate_plain', [
            'certificate' => $certificate,
            'config'      => $config,
        ])->render();

        // Carpeta de depuración (privada, solo para ver los .html)
        $debugDir = storage_path('certificates');
        if (!is_dir($debugDir)) {
            mkdir($debugDir, 0775, true);
        }
        file_put_contents($debugDir.'/_debug_certificate.html', $html);
        Log::info("[CERT PDF] HTML debug guardado (".strlen($html)." bytes)");

        // Helper para generar bytes de PDF desde HTML
        $makePdf = function (string $htmlStr) use ($paper, $orientation) {
            $pdf = Pdf::loadHTML($htmlStr)
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => true,
                    'dpi'                  => 110,
                    'defaultFont'          => 'DejaVu Sans',
                ])
                ->setPaper($paper, $orientation)
                ->setWarnings(true);

            return $pdf->output(); // string (bytes) o excepción
        };

        $bytes = null;

        // ===== 2) Intento normal =====
        try {
            $bytes = $makePdf($html);
            Log::info("[CERT PDF] intento 1 (normal) bytes=".(is_string($bytes) ? strlen($bytes) : 0));
        } catch (\Throwable $e) {
            Log::error("[CERT PDF] intento 1 falló: ".$e->getMessage());
        }

        // ===== 3) Reintento: sin fondo (elimina <img class="bg">) =====
        if (!is_string($bytes) || strlen($bytes) === 0) {
            $htmlNoBg = preg_replace('/<img[^>]*class="[^"]*\bbg\b[^"]*"[^>]*>/i', '', $html) ?? $html;
            file_put_contents($debugDir.'/_debug_certificate_no_bg.html', $htmlNoBg);
            try {
                $bytes = $makePdf($htmlNoBg);
                Log::info("[CERT PDF] intento 2 (sin fondo) bytes=".(is_string($bytes) ? strlen($bytes) : 0));
            } catch (\Throwable $e) {
                Log::error("[CERT PDF] intento 2 falló: ".$e->getMessage());
            }
        }

        // ===== 4) Reintento: sin ninguna imagen =====
        if (!is_string($bytes) || strlen($bytes) === 0) {
            $htmlNoImgs = preg_replace('/<img[^>]*>/i', '', $html) ?? $html;
            file_put_contents($debugDir.'/_debug_certificate_no_imgs.html', $htmlNoImgs);
            try {
                $bytes = $makePdf($htmlNoImgs);
                Log::info("[CERT PDF] intento 3 (sin imgs) bytes=".(is_string($bytes) ? strlen($bytes) : 0));
            } catch (\Throwable $e) {
                Log::error("[CERT PDF] intento 3 falló: ".$e->getMessage());
            }
        }

        // ===== 5) Último fallback: HTML ultra simple =====
        if (!is_string($bytes) || strlen($bytes) === 0) {
            $plain = '<html><head><meta charset="utf-8"></head><body>'.
                     '<h1 style="text-align:center">Certificado</h1>'.
                     '<p>ID: '.e($certificate->id).'</p>'.
                     '<p>Alumno: '.e(optional($certificate->holder)->first_names).' '.
                                  e(optional($certificate->holder)->last_names).'</p>'.
                     '<p>Curso: '.e(optional($certificate->course)->name).'</p>'.
                     '<p>Emitido: '.e(optional($certificate->issue_date)->format("d/m/Y")).'</p>'.
                     '</body></html>';
            file_put_contents($debugDir.'/_debug_certificate_plain.html', $plain);
            try {
                $bytes = $makePdf($plain);
                Log::info("[CERT PDF] intento 4 (plain) bytes=".(is_string($bytes) ? strlen($bytes) : 0));
            } catch (\Throwable $e) {
                Log::error("[CERT PDF] intento 4 falló: ".$e->getMessage());
                return null;
            }
        }

        if (!is_string($bytes) || strlen($bytes) === 0) {
            Log::error("[CERT PDF] No se obtuvieron bytes de PDF en ningún intento.");
            return null;
        }

        // ===== 6) Guardar en DISK 'public' y devolver RUTA WEB =====
        // Se servirá como /storage/certificates/ID_certificate.pdf
        $rel = "certificates/{$certificate->id}_certificate.pdf"; // dentro de storage/app/public
        Storage::disk('public')->put($rel, $bytes);

        $size = Storage::disk('public')->size($rel) ?: 0;
        Log::info("[CERT PDF] escrito en disk 'public': {$rel} ({$size} bytes)");

        // Muy importante: devolver con prefijo "storage/" para que asset() funcione
        return "storage/{$rel}";
    }
}
