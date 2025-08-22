<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Support\DocxToPdf;
use Carbon\Carbon;

Carbon::setLocale('es');
setlocale(LC_TIME, 'es_ES.UTF-8');

class ActaService
{
    public static function generate($certificate)
    {
        try {
            $docxWebPath = self::generateDocx($certificate); // "storage/certificates/ID_acta.docx"
            if (!$docxWebPath) {
                return null;
            }

            $pdfWebPath = self::convertToPdf($certificate, $docxWebPath);

            // Si por alguna razón no hubo PDF, devolvemos el DOCX
            return $pdfWebPath ?: $docxWebPath;

        } catch (\Exception $e) {
            \Log::error("Error generating acta for certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera el DOCX desde la plantilla y lo guarda en storage/certificates.
     * Retorna la RUTA WEB (con prefijo "storage/") para consistencia con tu código actual.
     */
    private static function generateDocx($certificate): ?string
    {
        $templatePath = storage_path('app/public/courses/' . $certificate->course->id . '/acta_template.docx');

        if (!file_exists($templatePath)) {
            \Log::warning("No se encontró la plantilla del acta: {$templatePath}");
            return null;
        }

        $certificatesDir = storage_path('certificates');
        if (!is_dir($certificatesDir)) {
            mkdir($certificatesDir, 0775, true);
        }

        $docxAbs = storage_path("certificates/{$certificate->id}_acta.docx");

        $template = new TemplateProcessor($templatePath);

        $template->setValue('first_names', $certificate->holder->first_names ?? '');
        $template->setValue('last_names', $certificate->holder->last_names ?? '');
        $template->setValue('identification_type', $certificate->holder->identification_type ?? '');
        $template->setValue('identification_number', $certificate->holder->identification_number ?? '');
        $template->setValue('day', $certificate->issue_date->format('d'));
        $template->setValue('month', ucfirst($certificate->issue_date->translatedFormat('F')));
        $template->setValue('year', $certificate->issue_date->format('Y'));
        $template->setValue('course_hours', $certificate->course->duration_hours ?? '');

        $config = \App\Models\CertificateTemplateConfig::getActiveConfig();
        self::processSignatures($template, $config);

        $template->saveAs($docxAbs);
        \Log::info("Acta DOCX generated for certificate {$certificate->id}");

        // devolvemos ruta WEB para mantener compatibilidad con tu flujo
        return "storage/certificates/{$certificate->id}_acta.docx";
    }

    /**
     * Intenta convertir localmente con DocxToPdf (HTML->PDF, barryvdh/domdpdf).
     * Si algo falla, intenta con ConvertAPI (si CONVERTAPI_SECRET existe).
     * Retorna la RUTA WEB del PDF.
     */
    private static function convertToPdf($certificate, string $docxWebPath): ?string
    {
        \Log::info("Starting PDF conversion for certificate {$certificate->id}, DOCX(web): {$docxWebPath}");

        // Obtener ruta ABSOLUTA del DOCX a partir de "storage/..."
        $relative = str_replace('storage/', '', $docxWebPath); // "certificates/ID_acta.docx"
        $docxAbs  = storage_path($relative);
        $pdfAbs   = storage_path("certificates/{$certificate->id}_acta.pdf");
        $pdfWeb   = "storage/certificates/{$certificate->id}_acta.pdf";

        // Asegurar carpeta destino
        if (!is_dir(dirname($pdfAbs))) {
            mkdir(dirname($pdfAbs), 0775, true);
        }

        try {
            // 1) Conversión LOCAL (recomendada, 100% PHP)
            DocxToPdf::convert($docxAbs, $pdfAbs);

            // Limpieza opcional del DOCX
            if (is_file($docxAbs)) {
                @unlink($docxAbs);
            }

            \Log::info("PDF conversion LOCAL successful for certificate {$certificate->id}: {$pdfWeb}");
            return $pdfWeb;

        } catch (\Throwable $e) {
            \Log::error("Local PDF conversion failed for certificate {$certificate->id}: " . $e->getMessage());
        }

        // 2) FALLBACK: ConvertAPI (si tienes la key)
        try {
            $fallback = self::convertWithConvertApi($certificate, $docxWebPath);
            if ($fallback) {
                \Log::info("PDF conversion via ConvertAPI successful for certificate {$certificate->id}: {$fallback}");
                return $fallback;
            }
        } catch (\Throwable $e) {
            \Log::error("Fallback ConvertAPI failed for certificate {$certificate->id}: " . $e->getMessage());
        }

        \Log::warning("PDF conversion failed for certificate {$certificate->id}, keeping DOCX");
        return null;
    }

    /**
     * ConvertAPI (fallback). Requiere CONVERTAPI_SECRET en .env
     * Devuelve ruta web del PDF en storage/certificates.
     */
    private static function convertWithConvertApi($certificate, string $docxWebPath): ?string
    {
        $secret = env('CONVERTAPI_SECRET');
        if (!$secret) {
            \Log::warning("CONVERTAPI_SECRET not set in .env");
            return null;
        }

        $relative     = str_replace("storage/", "", $docxWebPath); // "certificates/123_acta.docx"
        $fullDocxPath = storage_path($relative);

        if (!file_exists($fullDocxPath)) {
            \Log::error("DOCX file not found: {$fullDocxPath}");
            return null;
        }

        try {
            \Log::info("Converting DOCX to PDF using ConvertAPI for certificate {$certificate->id}");

            $response = Http::timeout(60)
                ->attach('File', file_get_contents($fullDocxPath), 'document.docx')
                ->post("https://v2.convertapi.com/convert/docx/to/pdf?Secret={$secret}");

            if (!$response->successful()) {
                \Log::error("ConvertAPI request failed ({$response->status()}) for certificate {$certificate->id}");
                \Log::debug("ConvertAPI error: " . $response->body());
                return null;
            }

            $result = $response->json();
            if (!isset($result['Files'][0])) {
                \Log::error("No Files array in ConvertAPI response for certificate {$certificate->id}");
                \Log::debug("ConvertAPI response: " . json_encode($result));
                return null;
            }

            $fileInfo = $result['Files'][0];
            $pdfAbs   = storage_path("certificates/{$certificate->id}_acta.pdf");
            $pdfWeb   = "storage/certificates/{$certificate->id}_acta.pdf";

            // Método por URL
            if (!empty($fileInfo['Url'])) {
                $pdfDownload = Http::timeout(60)->get($fileInfo['Url']);
                if ($pdfDownload->successful()) {
                    if (!is_dir(dirname($pdfAbs))) {
                        mkdir(dirname($pdfAbs), 0775, true);
                    }
                    file_put_contents($pdfAbs, $pdfDownload->body());
                    \Log::info("PDF saved from ConvertAPI URL for certificate {$certificate->id}");
                    @unlink($fullDocxPath);
                    return $pdfWeb;
                }
                \Log::error("Failed to download PDF URL from ConvertAPI for certificate {$certificate->id}");
                return null;
            }

            // Método base64
            if (!empty($fileInfo['FileData'])) {
                $pdfData = base64_decode($fileInfo['FileData']);
                if ($pdfData !== false && strlen($pdfData) > 0) {
                    if (!is_dir(dirname($pdfAbs))) {
                        mkdir(dirname($pdfAbs), 0775, true);
                    }
                    file_put_contents($pdfAbs, $pdfData);
                    \Log::info("PDF saved from ConvertAPI base64 for certificate {$certificate->id}");
                    @unlink($fullDocxPath);
                    return $pdfWeb;
                }
                \Log::error("Invalid base64 PDF data from ConvertAPI for certificate {$certificate->id}");
            }

            \Log::error("No usable URL or FileData from ConvertAPI for certificate {$certificate->id}");
            return null;

        } catch (\Exception $e) {
            \Log::error("Exception during ConvertAPI conversion for certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function processSignatures($template, $config): void
    {
        if (!empty($config->signature_1_image)) {
            $signaturePath = storage_path('app/public/' . $config->signature_1_image);
            if (file_exists($signaturePath)) {
                try {
                    $template->setImageValue('signature_1_image', [
                        'path' => $signaturePath,
                        'width' => 150,
                        'height' => 70,
                        'ratio'  => true,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Error setting signature 1: " . $e->getMessage());
                    $template->setValue('signature_1_image', '');
                }
            } else {
                $template->setValue('signature_1_image', '');
            }
        } else {
            $template->setValue('signature_1_image', '');
        }

        if (!empty($config->signature_2_image)) {
            $signaturePath = storage_path('app/public/' . $config->signature_2_image);
            if (file_exists($signaturePath)) {
                try {
                    $template->setImageValue('signature_2_image', [
                        'path' => $signaturePath,
                        'width' => 150,
                        'height' => 70,
                        'ratio'  => true,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Error setting signature 2: " . $e->getMessage());
                    $template->setValue('signature_2_image', '');
                }
            } else {
                $template->setValue('signature_2_image', '');
            }
        } else {
            $template->setValue('signature_2_image', '');
        }
    }
}
