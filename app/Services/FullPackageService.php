<?php

namespace App\Services;

use iio\libmergepdf\Merger;
use iio\libmergepdf\Driver\TcpdiDriver;
use Illuminate\Support\Facades\Storage;
use App\Models\Certificate;
use Illuminate\Support\Str;

class FullPackageService
{
    public static function generate(Certificate $certificate)
    {
        $certificate->loadMissing(['course', 'holder']);

        \Log::info("Generating package for certificate {$certificate->id}");

        $merger = new Merger(new TcpdiDriver());

        self::addIfExists($merger, $certificate->certificate_file_path);
        self::addIfExists($merger, $certificate->card_file_path);
        self::addIfExists($merger, $certificate->course->card_back_file_path);
        self::addIfExists($merger, $certificate->acta_file_path);
        self::addIfExists($merger, $certificate->course->manual_file_path);

        $pdfContent = $merger->merge();

        $fileName = self::generateFileName($certificate);
        $paquetePath = "certificates/{$fileName}";

        if ($certificate->paquete_file_path && Storage::disk('public')->exists($certificate->paquete_file_path)) {
            Storage::disk('public')->delete($certificate->paquete_file_path);
            \Log::info("Deleted old package: {$certificate->paquete_file_path}");
        }

        Storage::disk('public')->put($paquetePath, $pdfContent);

        $certificate->paquete_file_path = $paquetePath;
        $certificate->save();

        \Log::info("Package generated at: {$paquetePath}");

        return $paquetePath;
    }


    private static function generateFileName(Certificate $certificate): string
    {
        $firstName = $certificate->holder->first_names ?? '';
        $lastName = $certificate->holder->last_names ?? '';

        $identification_number = $certificate->holder->identification_number ?? '';

        $fullName = trim("{$firstName} {$lastName} {$identification_number}");

        if (empty($fullName)) {
            $fullName = "Certificate_{$certificate->id}";
        }

        $cleanName = self::sanitizeFileName($fullName);

        $fileName = "{$cleanName}.TODO.pdf";

        \Log::info("Generated filename: {$fileName} from holder: {$fullName}");

        return $fileName;
    }

    private static function sanitizeFileName(string $name): string
    {
        $name = strtoupper($name);

        $name = str_replace(' ', '_', $name);

        $name = self::removeAccents($name);

        $name = preg_replace('/[^A-Z0-9_-]/', '', $name);

        $name = preg_replace('/[-_]+/', '_', $name);

        $name = trim($name, '_-');

        $name = Str::limit($name, 50, '');

        return $name ?: 'UNNAMED';
    }

    private static function removeAccents(string $string): string
    {
        $accents = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ú' => 'u',
            'Ü' => 'u',
            'Ñ' => 'n',
            'à' => 'a',
            'è' => 'e',
            'ì' => 'i',
            'ò' => 'o',
            'ù' => 'u',
            'À' => 'a',
            'È' => 'e',
            'Ì' => 'i',
            'Ò' => 'o',
            'Ù' => 'u',
        ];

        return strtr($string, $accents);
    }

    private static function addIfExists(Merger $merger, ?string $path): bool
    {
        if (!$path) {
            \Log::debug("Path is null or empty");
            return false;
        }

        if (!Storage::disk('public')->exists($path)) {
            \Log::warning("PDF no encontrado: {$path}");
            return false;
        }

        $fullPath = Storage::disk('public')->path($path);
        if (filesize($fullPath) === 0) {
            \Log::warning("PDF vacío: {$fullPath}");
            return false;
        }

        if (strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) !== 'pdf') {
            \Log::warning("Archivo no es PDF: {$fullPath}");
            return false;
        }

        try {
            $merger->addFile($fullPath);
            \Log::debug("Successfully added: {$path}");
            return true;
        } catch (\Exception $e) {
            \Log::error("Error adding PDF {$path}: " . $e->getMessage());
            return false;
        }
    }
}