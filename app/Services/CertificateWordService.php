<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Http;
use App\Support\DocxToPdf;
use Carbon\Carbon;

Carbon::setLocale('es');
setlocale(LC_TIME, 'es_ES.UTF-8');

class CertificateWordService
{
    public static function generate($certificate)
    {
        try {
            $docxWebPath = self::generateDocx($certificate); // "storage/certificates/{id}_certificate.docx"
            if (!$docxWebPath) {
                return null;
            }

            $pdfWebPath = self::convertToPdf($certificate, $docxWebPath);

            return $pdfWebPath ?: $docxWebPath;

        } catch (\Exception $e) {
            \Log::error("Error generating certificate for certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function generateDocx($certificate): ?string
    {
        $templatePaths = [
            storage_path('app/public/templates/certificate_template.docx'),
            storage_path('app/public/courses/' . $certificate->course->id . '/certificate_template.docx'),
        ];

        $templatePath = null;
        foreach ($templatePaths as $path) {
            if (file_exists($path)) {
                $templatePath = $path;
                \Log::info("Using certificate template: {$path}");
                break;
            }
        }

        if (!$templatePath) {
            \Log::warning("No se encontró plantilla de certificado (ni global ni específica del curso {$certificate->course->id})");
            return null;
        }

        $dir = storage_path('certificates');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $docxAbs = storage_path("certificates/{$certificate->id}_certificate.docx");

        $template = new TemplateProcessor($templatePath);
        $config   = self::getConfig();

        $template->setValue('certificate_title', $config->certificate_title ?? 'CERTIFICADO DE FINALIZACIÓN');
        $template->setValue('intro_text', $config->intro_text ?? '');

        $template->setValue('first_names', $certificate->holder->first_names ?? '');
        $template->setValue('last_names', $certificate->holder->last_names ?? '');
        $template->setValue('identification_type', $certificate->holder->identification_type ?? '');
        $template->setValue('identification_number', $certificate->holder->identification_number ?? '');
        $template->setValue('identification_place', $certificate->holder->identification_place ?? '');

        $template->setValue('course_name', $certificate->course->name ?? '');
        $template->setValue('course_hours', $certificate->course->duration_hours ?? '');

        $template->setValue('issue_date', $certificate->issue_date ? $certificate->issue_date->format('d/m/Y') : '');
        $template->setValue('expiry_date', $certificate->expiry_date ? $certificate->expiry_date->format('d/m/Y') : '');

        $template->setValue('signature_1_name', $config->signature_1_name ?? '');
        $template->setValue('signature_1_position', $config->signature_1_position ?? '');
        $template->setValue('signature_2_name', $config->signature_2_name ?? '');
        $template->setValue('signature_2_position', $config->signature_2_position ?? '');

        $template->setValue('series_number', $certificate->series_number ?? '');
        $template->setValue('additional_text', $config->additional_text ?? '');

        self::processImages($template, $config);

        $template->saveAs($docxAbs);
        \Log::info("Certificate DOCX generated for certificate {$certificate->id}");

        // Devolvemos ruta WEB (si no tienes storage:link, sirve desde un endpoint que lea storage_path)
        return "storage/certificates/{$certificate->id}_certificate.docx";
    }

    /**
     * 1) Intento local con DocxToPdf (recomendado, 100% PHP).
     * 2) Fallback ConvertAPI si existe CONVERTAPI_SECRET.
     * Devuelve ruta web del PDF.
     */
    private static function convertToPdf($certificate, string $docxWebPath): ?string
    {
        \Log::info("Starting CERT PDF conversion for certificate {$certificate->id}, DOCX(web): {$docxWebPath}");

        // Pasar "storage/..." a ruta absoluta real
        $relative = str_replace('storage/', '', $docxWebPath); // "certificates/{id}_certificate.docx"
        $docxAbs  = storage_path($relative);
        $pdfAbs   = storage_path("certificates/{$certificate->id}_certificate.pdf");
        $pdfWeb   = "storage/certificates/{$certificate->id}_certificate.pdf";

        if (!file_exists($docxAbs)) {
            \Log::error("CERT DOCX not found at {$docxAbs}");
            return null;
        }

        if (!is_dir(dirname($pdfAbs))) {
            mkdir(dirname($pdfAbs), 0775, true);
        }

        // 1) LOCAL
        try {
            DocxToPdf::convert($docxAbs, $pdfAbs);

            // Limpieza opcional
            if (is_file($docxAbs)) {
                @unlink($docxAbs);
            }

            \Log::info("CERT PDF conversion LOCAL successful for certificate {$certificate->id}: {$pdfWeb}");
            return $pdfWeb;

        } catch (\Throwable $e) {
            \Log::error("Local CERT PDF conversion failed for certificate {$certificate->id}: " . $e->getMessage());
        }

        // 2) FALLBACK: ConvertAPI
        $secret = env('CONVERTAPI_SECRET');
        if (!$secret) {
            \Log::warning("CONVERTAPI_SECRET not set, keeping DOCX for certificate {$certificate->id}");
            return null;
        }

        try {
            \Log::info("Converting CERT via ConvertAPI for certificate {$certificate->id}");

            $response = Http::timeout(60)
                ->attach('File', file_get_contents($docxAbs), 'document.docx')
                ->post("https://v2.convertapi.com/convert/docx/to/pdf?Secret={$secret}");

            if (!$response->successful()) {
                \Log::error("ConvertAPI request failed ({$response->status()}) for CERT {$certificate->id}");
                \Log::debug("ConvertAPI error: " . $response->body());
                return null;
            }

            $result = $response->json();
            $file   = $result['Files'][0] ?? null;

            if (!$file) {
                \Log::error("No Files[0] in ConvertAPI response for CERT {$certificate->id}");
                return null;
            }

            if (!empty($file['Url'])) {
                $pdfDownload = Http::timeout(60)->get($file['Url']);
                if ($pdfDownload->successful()) {
                    file_put_contents($pdfAbs, $pdfDownload->body());
                    @unlink($docxAbs);
                    \Log::info("CERT PDF saved from ConvertAPI URL for certificate {$certificate->id}");
                    return $pdfWeb;
                }
                \Log::error("Failed to download CERT PDF URL from ConvertAPI for certificate {$certificate->id}");
                return null;
            }

            if (!empty($file['FileData'])) {
                $pdfData = base64_decode($file['FileData']);
                if ($pdfData !== false && strlen($pdfData) > 0) {
                    file_put_contents($pdfAbs, $pdfData);
                    @unlink($docxAbs);
                    \Log::info("CERT PDF saved from ConvertAPI base64 for certificate {$certificate->id}");
                    return $pdfWeb;
                }
                \Log::error("Invalid base64 CERT PDF data from ConvertAPI for certificate {$certificate->id}");
            }

            \Log::error("No usable CERT PDF returned by ConvertAPI for certificate {$certificate->id}");
            return null;

        } catch (\Throwable $e) {
            \Log::error("Exception during ConvertAPI CERT conversion for certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function processImages($template, $config): void
    {
        if (!empty($config->company_logo)) {
            $logoPath = storage_path('app/public/' . $config->company_logo);
            if (file_exists($logoPath)) {
                try {
                    $template->setImageValue('company_logo', [
                        'path'      => $logoPath,
                        'width'     => 310,
                        'height'    => 250,
                        'ratio'     => false,
                        'alignment' => 'center',
                        'valign'    => 'top'
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Error setting company logo: " . $e->getMessage());
                    $template->setValue('company_logo', '');
                }
            } else {
                $template->setValue('company_logo', '');
            }
        } else {
            $template->setValue('company_logo', '');
        }

        if (!empty($config->signature_1_image)) {
            $signaturePath = storage_path('app/public/' . $config->signature_1_image);
            if (file_exists($signaturePath)) {
                try {
                    $template->setImageValue('signature_1_image', [
                        'path'   => $signaturePath,
                        'width'  => 200,
                        'height' => 100,
                        'ratio'  => true
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
                        'path'   => $signaturePath,
                        'width'  => 200,
                        'height' => 100,
                        'ratio'  => true
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

    private static function getConfig()
    {
        return \App\Models\CertificateTemplateConfig::getActiveConfig();
    }
}
