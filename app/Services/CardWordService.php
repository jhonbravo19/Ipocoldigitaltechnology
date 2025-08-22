<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

Carbon::setLocale('es');
setlocale(LC_TIME, 'es_ES.UTF-8');

class CardWordService
{
    public static function generate($certificate)
    {
        try {
            $docxPath = self::generateDocx($certificate);

            if (!$docxPath) {
                return null;
            }

            $pdfPath = self::convertToPdf($certificate, $docxPath);

            return $pdfPath ?: $docxPath;

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

        $certificatesDir = storage_path("certificates");
        if (!is_dir($certificatesDir)) {
            mkdir($certificatesDir, 0775, true);
        }

        $docxPath = storage_path("certificates/{$certificate->id}_card.docx");

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

        $template->saveAs($docxPath);
        \Log::info("Card DOCX generated for certificate {$certificate->id}");

        return "storage/certificates/{$certificate->id}_card.docx";
    }

    private static function convertToPdf($certificate, $docxPath): ?string
    {
        $secret = env('CONVERTAPI_SECRET');
        if (!$secret) {
            \Log::warning("CONVERTAPI_SECRET not set, keeping DOCX for card {$certificate->id}");
            return null;
        }

        $fullDocxPath = storage_path("app/public/{$docxPath}");

        if (!file_exists($fullDocxPath)) {
            \Log::error("DOCX file not found: {$fullDocxPath}");
            return null;
        }

        try {
            \Log::info("Converting card DOCX to PDF using ConvertAPI for certificate {$certificate->id}");

            $response = Http::timeout(60)
                ->attach('File', file_get_contents($fullDocxPath), 'document.docx')
                ->post("https://v2.convertapi.com/convert/docx/to/pdf?Secret={$secret}");

            if ($response->successful()) {
                $result = $response->json();
                \Log::info("ConvertAPI response received for card {$certificate->id}");

                if (isset($result['Files'][0])) {
                    $fileInfo = $result['Files'][0];

                    if (isset($fileInfo['Url'])) {
                        $pdfDownload = Http::timeout(60)->get($fileInfo['Url']);

                        if ($pdfDownload->successful()) {
                            $pdfPath = "certificates/{$certificate->id}_card.pdf";
                            Storage::disk('public')->put($pdfPath, $pdfDownload->body());

                            if (file_exists($fullDocxPath)) {
                                unlink($fullDocxPath);
                            }

                            \Log::info("Card PDF successfully generated for certificate {$certificate->id}");
                            return $pdfPath;
                        }
                    } elseif (isset($fileInfo['FileData'])) {
                        $pdfData = base64_decode($fileInfo['FileData']);

                        if ($pdfData !== false && strlen($pdfData) > 0) {
                            $pdfPath = "certificates/{$certificate->id}_card.pdf";
                            Storage::disk('public')->put($pdfPath, $pdfData);

                            if (file_exists($fullDocxPath)) {
                                unlink($fullDocxPath);
                            }

                            \Log::info("Card PDF successfully generated from base64 for certificate {$certificate->id}");
                            return $pdfPath;
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error("Exception during card PDF conversion for certificate {$certificate->id}: " . $e->getMessage());
        }

        return null;
    }

    private static function processImages($template, $certificate, $config): void
    {
        if (!empty($config->company_logo)) {
            $logoPath = storage_path('app/public/' . $config->company_logo);
            if (file_exists($logoPath)) {
                try {
                    $template->setImageValue('company_logo', [
                        'path' => $logoPath,
                        'width' => 65,
                        'height' => 70,
                        'ratio' => true
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
                        'path' => $bgPath,
                        'width' => 5,
                        'height' => 5,
                        'ratio' => true,
                        'alignment' => 'left',
                        'valign' => 'top'
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
                        'path' => $photoPath,
                        'width' => 200,
                        'height' => 100,
                        'ratio' => true
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
