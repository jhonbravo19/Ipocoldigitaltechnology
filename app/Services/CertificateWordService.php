<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;       // <- FALTABA
use App\Support\DocxToPdf;
use Carbon\Carbon;

Carbon::setLocale('es');
setlocale(LC_TIME, 'es_ES.UTF-8');

class CertificateWordService
{
    public static function generate($certificate)
    {
        try {
            // 1) Genera el DOCX en el DISK 'public' y devuelve ruta relativa: "certificates/ID_certificate.docx"
            $docxRelative = self::generateDocx($certificate);
            if (!$docxRelative) {
                return null;
            }

            // 2) Convierte a PDF localmente (mPDF) y devuelve "certificates/ID_certificate.pdf"
            $pdfRelative = self::convertToPdf($certificate, $docxRelative);

            // 3) Devuelve PDF si existe; si no, al menos el DOCX
            return $pdfRelative ?: $docxRelative;

        } catch (\Throwable $e) {
            \Log::error("Error generating certificate {$certificate->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function generateDocx($certificate): ?string
    {
        // Busca plantilla global o por curso (en storage/app/public/…)
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
            \Log::warning("No se encontró plantilla (global ni curso {$certificate->course->id})");
            return null;
        }

        // ✅ RUTA RELATIVA en el DISK 'public'
        $relative = "certificates/{$certificate->id}_certificate.docx";
        // ✅ RUTA ABSOLUTA real
        $absolute = Storage::disk('public')->path($relative);

        // Asegura carpeta
        if (!is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0775, true);
        }

        // Rellenar DOCX
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

        // Guardar DOCX
        $template->saveAs($absolute);
        \Log::info("Certificate DOCX generated: {$absolute}");

        // Devuelve la RUTA RELATIVA (se expone como /storage/… si hiciste storage:link)
        return $relative;
    }

    private static function convertToPdf($certificate, string $docxRelative): ?string
    {
        try {
            $docxAbs = Storage::disk('public')->path($docxRelative);
            if (!file_exists($docxAbs)) {
                \Log::error("DOCX no encontrado: {$docxAbs}");
                return null;
            }

            $pdfRelative = "certificates/{$certificate->id}_certificate.pdf";
            $pdfAbs = Storage::disk('public')->path($pdfRelative);

            if (!is_dir(dirname($pdfAbs))) {
                mkdir(dirname($pdfAbs), 0775, true);
            }

            // Conversión local (mPDF) usando tu soporte
            DocxToPdf::convert($docxAbs, $pdfAbs);

            // Limpieza opcional del DOCX
            @unlink($docxAbs);

            \Log::info("Certificate PDF generated: {$pdfAbs}");
            return $pdfRelative;

        } catch (\Throwable $e) {
            \Log::error("Error al convertir a PDF cert {$certificate->id}: " . $e->getMessage());
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
