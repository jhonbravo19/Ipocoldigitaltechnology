<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

Carbon::setLocale('es');
setlocale(LC_TIME, 'es_ES.UTF-8');

class CertificateWordService
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

        $certificatesDir = storage_path("app/public/certificates");
        if (!is_dir($certificatesDir)) {
            mkdir($certificatesDir, 0775, true);
        }

        $docxPath = storage_path("app/public/certificates/{$certificate->id}_certificate.docx");

        $template = new TemplateProcessor($templatePath);

        $config = self::getConfig();

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

        $template->saveAs($docxPath);
        \Log::info("Certificate DOCX generated for certificate {$certificate->id}");

        return "certificates/{$certificate->id}_certificate.docx";
    }

    private static function convertToPdf($certificate, $docxPath): ?string
    {
        $secret = env('CONVERTAPI_SECRET');
        if (!$secret) {
            \Log::warning("CONVERTAPI_SECRET not set, keeping DOCX for certificate {$certificate->id}");
            return null;
        }

        $fullDocxPath = storage_path("app/public/{$docxPath}");

        if (!file_exists($fullDocxPath)) {
            \Log::error("DOCX file not found: {$fullDocxPath}");
            return null;
        }

        try {
            \Log::info("Converting certificate DOCX to PDF using ConvertAPI for certificate {$certificate->id}");

            $response = Http::timeout(60)
                ->attach('File', file_get_contents($fullDocxPath), 'document.docx')
                ->post("https://v2.convertapi.com/convert/docx/to/pdf?Secret={$secret}");

            if ($response->successful()) {
                $result = $response->json();
                \Log::info("ConvertAPI response received for certificate {$certificate->id}");

                if (isset($result['Files'][0])) {
                    $fileInfo = $result['Files'][0];

                    if (isset($fileInfo['Url'])) {
                        $pdfDownload = Http::timeout(60)->get($fileInfo['Url']);

                        if ($pdfDownload->successful()) {
                            $pdfPath = "certificates/{$certificate->id}_certificate.pdf";
                            Storage::disk('public')->put($pdfPath, $pdfDownload->body());

                            if (file_exists($fullDocxPath)) {
                                unlink($fullDocxPath);
                            }

                            \Log::info("Certificate PDF successfully generated for certificate {$certificate->id}");
                            return $pdfPath;
                        }
                    }
                    elseif (isset($fileInfo['FileData'])) {
                        $pdfData = base64_decode($fileInfo['FileData']);

                        if ($pdfData !== false && strlen($pdfData) > 0) {
                            $pdfPath = "certificates/{$certificate->id}_certificate.pdf";
                            Storage::disk('public')->put($pdfPath, $pdfData);

                            if (file_exists($fullDocxPath)) {
                                unlink($fullDocxPath);
                            }

                            \Log::info("Certificate PDF successfully generated from base64 for certificate {$certificate->id}");
                            return $pdfPath;
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error("Exception during certificate PDF conversion for certificate {$certificate->id}: " . $e->getMessage());
        }

        return null;
    }

    private static function processImages($template, $config): void
    {
        if (!empty($config->company_logo)) {
            $logoPath = storage_path('app/public/' . $config->company_logo);
            if (file_exists($logoPath)) {
                try {
                    $template->setImageValue('company_logo', [
                        'path' => $logoPath,
                        'width' => 310,
                        'height' => 250,
                        'ratio' => false,
                        'alignment' => 'center',
                        'valign' => 'top'
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
                        'path' => $signaturePath,
                        'width' => 200,
                        'height' => 100,
                        'ratio' => true
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
                        'width' => 200,
                        'height' => 100,
                        'ratio' => true
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
