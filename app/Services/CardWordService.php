<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Http;
use App\Support\DocxToPdf; // <— IMPORTANTE
use Carbon\Carbon;

Carbon::setLocale('es');
setlocale(LC_TIME, 'es_ES.UTF-8');

class CardWordService
{
    public static function generate($certificate)
    {
        try {
            $docxWebPath = self::generateDocx($certificate); // "storage/certificates/{id}_card.docx"
            if (!$docxWebPath) {
                return null;
            }

            $pdfWebPath = self::convertToPdf($certificate, $docxWebPath);

            return $pdfWebPath ?: $docxWebPath;

        } catch (\Exception $e) {
            \Log::error("Error generating card for certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function generateDocx($certificate): ?string
    {
        $templatePaths = [
            storage_path('app/public/templates/carnet_template.docx'),
            storage_path('app/public/courses/' . $certificate->course->id . '/carnet_template.docx'),
        ];

        $templatePath = null;
        foreach ($templatePaths as $path) {
            if (file_exists($path)) {
                $templatePath = $path;
                \Log::info("Using card template: {$path}");
                break;
            }
        }

        if (!$templatePath) {
            \Log::warning("No se encontró plantilla de carnet (ni global ni específica del curso {$certificate->course->id})");
            return null;
        }

        $dir = storage_path('certificates');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $docxAbs = storage_path("certificates/{$certificate->id}_card.docx");

        $template = new TemplateProcessor($templatePath);

        $config = self::getConfig();

        $template->setValue('certificate_title', $config->certificate_title ?? 'COLSERTRANS');
        $template->setValue('first_name', $certificate->holder->first_names ?? '');
        $template->setValue('last_name', $certificate->holder->last_names ?? '');
        $template->setValue('full_name', trim(($certificate->holder->first_names ?? '') . ' ' . ($certificate->holder->last_names ?? '')));
        $template->setValue('identification_number', $certificate->holder->identification_number ?? '');
        $template->setValue('identification_place', $certificate->holder->identification_place ?? '');
        $template->setValue('blood_type', $certificate->holder->blood_type ?? 'O+');
        $template->setValue('has_drivers_license', $certificate->holder->has_drivers_license ?? 'NO');
        $template->setValue('drivers_license_category', $certificate->holder->drivers_license_category ?? '');

        $template->setValue('course_name', $certificate->course->name ?? '');
        $template->setValue('course_hours', $certificate->course->duration_hours ?? '');
        $template->setValue('series_number', $certificate->series_number ?? '');
        $template->setValue('issue_date', $certificate->issue_date ? $certificate->issue_date->format('d/m/Y') : '');
        $template->setValue('expiry_date', $certificate->expiry_date ? $certificate->expiry_date->format('d/m/Y') : '');
        $template->setValue('day', $certificate->issue_date ? $certificate->issue_date->format('d') : '');
        $template->setValue('month', $certificate->issue_date ? ucfirst($certificate->issue_date->translatedFormat('F')) : '');
        $template->setValue('year', $certificate->issue_date ? $certificate->issue_date->format('Y') : '');

        self::processImages($template, $certificate, $config);

        $template->saveAs($docxAbs);
        \Log::info("Card DOCX generated for certificate {$certificate->id}");

        // Ruta web (si usas storage:link apuntará al archivo; si no, sirve con un endpoint que lea desde storage_path)
        return "storage/certificates/{$certificate->id}_card.docx";
    }

    /**
     * Intento 1: conversión LOCAL con DocxToPdf (recomendado, 100% PHP).
     * Fallback: ConvertAPI si tienes CONVERTAPI_SECRET.
     * Retorna RUTA WEB del PDF.
     */
    private static function convertToPdf($certificate, string $docxWebPath): ?string
    {
        \Log::info("Starting CARD PDF conversion for certificate {$certificate->id}, DOCX(web): {$docxWebPath}");

        // Pasar de "storage/..." a ruta absoluta real en storage/
        $relative = str_replace('storage/', '', $docxWebPath); // "certificates/{id}_card.docx"
        $docxAbs  = storage_path($relative);
        $pdfAbs   = storage_path("certificates/{$certificate->id}_card.pdf");
        $pdfWeb   = "storage/certificates/{$certificate->id}_card.pdf";

        if (!file_exists($docxAbs)) {
            \Log::error("CARD DOCX not found at {$docxAbs}");
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

            \Log::info("CARD PDF conversion LOCAL successful for certificate {$certificate->id}: {$pdfWeb}");
            return $pdfWeb;

        } catch (\Throwable $e) {
            \Log::error("Local CARD PDF conversion failed for certificate {$certificate->id}: " . $e->getMessage());
        }

        // 2) FALLBACK: ConvertAPI
        $secret = env('CONVERTAPI_SECRET');
        if (!$secret) {
            \Log::warning("CONVERTAPI_SECRET not set, keeping DOCX for card {$certificate->id}");
            return null;
        }

        try {
            \Log::info("Converting CARD via ConvertAPI for certificate {$certificate->id}");

            $response = Http::timeout(60)
                ->attach('File', file_get_contents($docxAbs), 'document.docx')
                ->post("https://v2.convertapi.com/convert/docx/to/pdf?Secret={$secret}");

            if (!$response->successful()) {
                \Log::error("ConvertAPI request failed ({$response->status()}) for CARD {$certificate->id}");
                \Log::debug("ConvertAPI error: " . $response->body());
                return null;
            }

            $result  = $response->json();
            $file    = $result['Files'][0] ?? null;

            if (!$file) {
                \Log::error("No Files[0] in ConvertAPI response for CARD {$certificate->id}");
                return null;
            }

            if (!empty($file['Url'])) {
                $pdfDownload = Http::timeout(60)->get($file['Url']);
                if ($pdfDownload->successful()) {
                    file_put_contents($pdfAbs, $pdfDownload->body());
                    @unlink($docxAbs);
                    \Log::info("CARD PDF saved from ConvertAPI URL for certificate {$certificate->id}");
                    return $pdfWeb;
                }
                \Log::error("Failed to download CARD PDF URL from ConvertAPI for certificate {$certificate->id}");
                return null;
            }

            if (!empty($file['FileData'])) {
                $pdfData = base64_decode($file['FileData']);
                if ($pdfData !== false && strlen($pdfData) > 0) {
                    file_put_contents($pdfAbs, $pdfData);
                    @unlink($docxAbs);
                    \Log::info("CARD PDF saved from ConvertAPI base64 for certificate {$certificate->id}");
                    return $pdfWeb;
                }
                \Log::error("Invalid base64 CARD PDF data from ConvertAPI for certificate {$certificate->id}");
            }

            \Log::error("No usable CARD PDF returned by ConvertAPI for certificate {$certificate->id}");
            return null;

        } catch (\Throwable $e) {
            \Log::error("Exception during ConvertAPI CARD conversion for certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function processImages($template, $certificate, $config): void
    {
        if (!empty($config->company_logo)) {
            $logoPath = storage_path('app/public/' . $config->company_logo);
            if (file_exists($logoPath)) {
                try {
                    $template->setImageValue('company_logo', [
                        'path'   => $logoPath,
                        'width'  => 65,
                        'height' => 70,
                        'ratio'  => true
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Error setting company logo in card: " . $e->getMessage());
                    $template->setValue('company_logo', '');
                }
            } else {
                $template->setValue('company_logo', '');
            }
        } else {
            $template->setValue('company_logo', '');
        }

        if (!empty($config->carnet_background_image)) {
            $bgPath = storage_path('app/public/' . $config->carnet_background_image);
            if (file_exists($bgPath)) {
                try {
                    $template->setImageValue('carnet_background_image', [
                        'path'      => $bgPath,
                        'width'     => 5,
                        'height'    => 5,
                        'ratio'     => true,
                        'alignment' => 'left',
                        'valign'    => 'top'
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Error setting carnet background: " . $e->getMessage());
                    $template->setValue('carnet_background_image', '');
                }
            } else {
                $template->setValue('carnet_background_image', '');
            }
        } else {
            $template->setValue('carnet_background_image', '');
        }

        if (!empty($certificate->holder->photo_path)) {
            $photoPath = storage_path('app/public/' . $certificate->holder->photo_path);
            if (file_exists($photoPath)) {
                try {
                    $template->setImageValue('holder_photo', [
                        'path'   => $photoPath,
                        'width'  => 200,
                        'height' => 100,
                        'ratio'  => true
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("Error setting holder photo in card: " . $e->getMessage());
                    $template->setValue('holder_photo', '');
                }
            } else {
                $template->setValue('holder_photo', '');
            }
        } else {
            $template->setValue('holder_photo', '');
        }
    }

    private static function getConfig()
    {
        return \App\Models\CertificateTemplateConfig::getActiveConfig();
    }
}
