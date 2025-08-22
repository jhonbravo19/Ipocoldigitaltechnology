<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

Carbon::setLocale('es');
setlocale(LC_TIME, 'es_ES.UTF-8');

class ActaService
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
            \Log::error("Error generating acta for certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function generateDocx($certificate): ?string
    {
        $templatePath = storage_path('app/public/courses/' . $certificate->course->id . '/acta_template.docx');
        
        if (!file_exists($templatePath)) {
            \Log::warning("No se encontró la plantilla del acta: {$templatePath}");
            return null;
        }

        $certificatesDir = storage_path("certificates");
        if (!is_dir($certificatesDir)) {
            mkdir($certificatesDir, 0775, true);
        }

        $docxPath = storage_path("certificates/{$certificate->id}_acta.docx");

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

        $template->saveAs($docxPath);
        \Log::info("Acta DOCX generated for certificate {$certificate->id}");

        return "storage/certificates/{$certificate->id}_acta.docx";
    }

    private static function convertToPdf($certificate, $docxPath): ?string
    {
        \Log::info("Starting PDF conversion for certificate {$certificate->id}, DOCX: {$docxPath}");
        
        try {
            $result = self::convertWithConvertApi($certificate, $docxPath);
            
            if ($result) {
                \Log::info("PDF conversion successful for certificate {$certificate->id}: {$result}");
                return $result;
            } else {
                \Log::warning("PDF conversion failed for certificate {$certificate->id}, keeping DOCX");
                return null;
            }
            
        } catch (\Exception $e) {
            \Log::error("Exception during PDF conversion for certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }
    private static function convertWithCloudmersive($certificate, $docxPath): ?string
    {
        $apiKey = env('CLOUDMERSIVE_API_KEY');
        if (!$apiKey) {
            return null;
        }

        $relative = str_replace("storage/", "", $docxPath); // "certificates/123_acta.docx"
        $fullDocxPath = storage_path($relative);
        
        $response = Http::withHeaders([
            'Apikey' => $apiKey,
        ])->attach(
            'inputFile', 
            file_get_contents($fullDocxPath), 
            'document.docx'
        )->post('https://api.cloudmersive.com/convert/docx/to/pdf');

        if ($response->successful()) {
    $pdfPath = "storage/certificates/{$certificate->id}_acta.pdf";
    $pdfAbs  = storage_path("certificates/{$certificate->id}_acta.pdf");

    file_put_contents($pdfAbs, $response->body());

    if (file_exists($fullDocxPath)) {
        unlink($fullDocxPath);
    }

    return $pdfPath; // <- lo que tu controller usará para mostrar/descargar
}

        return null;
    }

    private static function convertWithILovePdf($certificate, $docxPath): ?string
    {
        $publicKey = env('ILOVEPDF_PUBLIC_KEY');
        $secretKey = env('ILOVEPDF_SECRET_KEY');
        
        if (!$publicKey || !$secretKey) {
            return null;
        }

        $relative = str_replace("storage/", "", $docxPath); // "certificates/123_acta.docx"
$fullDocxPath = storage_path($relative);
        
        return null;
    }

    private static function convertWithConvertApi($certificate, $docxPath): ?string
    {
        $secret = env('CONVERTAPI_SECRET');
        if (!$secret) {
            \Log::warning("CONVERTAPI_SECRET not set in .env");
            return null;
        }

        $relative = str_replace("storage/", "", $docxPath); // "certificates/123_acta.docx"
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

            if ($response->successful()) {
                $result = $response->json();
                \Log::info("ConvertAPI response received for certificate {$certificate->id}");
                
                
                if (isset($result['Files'][0])) {
                    $fileInfo = $result['Files'][0];
                    
                    if (isset($fileInfo['Url'])) {
                        \Log::info("Using download URL method for certificate {$certificate->id}");
                        
                        $pdfDownload = Http::timeout(60)->get($fileInfo['Url']);
                        
                        
                        if ($pdfDownload->successful()) {
                            // Ruta WEB que usará tu vista
                            $pdfPath = "storage/certificates/{$certificate->id}_acta.pdf";
                            // Ruta ABSOLUTA en el servidor (carpeta storage/)
                            $pdfAbs  = storage_path("certificates/{$certificate->id}_acta.pdf");

                            // Asegura que exista el directorio
                            if (!is_dir(dirname($pdfAbs))) {
                                mkdir(dirname($pdfAbs), 0775, true);
                            }

                            // Guarda el PDF en storage/certificates
                            file_put_contents($pdfAbs, $pdfDownload->body());

                            \Log::info("PDF successfully downloaded from URL for certificate {$certificate->id}");

                            // Borra el DOCX si existe
                            if (is_file($fullDocxPath)) {
                                @unlink($fullDocxPath);
                            }

                            // Devuelve la ruta WEB (coincide con public_html/storage/...)
                            return $pdfPath;
                        } else {
                            \Log::error("Failed to download PDF from URL for certificate {$certificate->id}");
                                                }

                        
                        if ($pdfDownload->successful()) {
                            $pdfPath = "certificates/{$certificate->id}_acta.pdf";
                            Storage::disk('public')->put($pdfPath, $pdfDownload->body());
                            
                            \Log::info("PDF successfully downloaded from URL for certificate {$certificate->id}");
                            
                            if (file_exists($fullDocxPath)) {
                                unlink($fullDocxPath);
                            }
                            
                            return $pdfPath;
                        } else {
                            \Log::error("Failed to download PDF from URL for certificate {$certificate->id}");
                        }
                    }
                    elseif (isset($fileInfo['FileData'])) {
                        \Log::info("Using base64 data method for certificate {$certificate->id}");
                        
                        $pdfData = base64_decode($fileInfo['FileData']);
                        
                        if ($pdfData !== false && strlen($pdfData) > 0) {
                            $pdfPath = "certificates/{$certificate->id}_acta.pdf";
                            Storage::disk('public')->put($pdfPath, $pdfData);
                            
                            \Log::info("PDF successfully saved from base64 data for certificate {$certificate->id}");
                            
                            if (file_exists($fullDocxPath)) {
                                unlink($fullDocxPath);
                            }
                            
                            return $pdfPath;
                        } else {
                            \Log::error("Invalid base64 data for certificate {$certificate->id}");
                        }
                    }
                    else {
                        \Log::error("No URL or FileData in ConvertAPI response for certificate {$certificate->id}");
                        \Log::debug("Available keys in Files[0]: " . implode(', ', array_keys($fileInfo)));
                    }
                } else {
                    \Log::error("No Files array in ConvertAPI response for certificate {$certificate->id}");
                    \Log::debug("ConvertAPI response keys: " . implode(', ', array_keys($result)));
                }
                
                \Log::debug("Full ConvertAPI response for certificate {$certificate->id}: " . json_encode($result));
                
            } else {
                \Log::error("ConvertAPI request failed for certificate {$certificate->id}: " . $response->status());
                \Log::debug("ConvertAPI error response: " . $response->body());
            }

        } catch (\Exception $e) {
            \Log::error("Exception during ConvertAPI conversion for certificate {$certificate->id}: " . $e->getMessage());
        }

        return null;
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
                        'width' => 150,
                        'height' => 70,
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
}