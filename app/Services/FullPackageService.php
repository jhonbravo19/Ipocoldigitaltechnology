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

        // Agrega en el orden deseado, resolviendo cada ruta de forma robusta
        self::addIfExists($merger, $certificate->certificate_file_path);
        self::addIfExists($merger, $certificate->card_file_path);
        self::addIfExists($merger, $certificate->course->card_back_file_path ?? null);
        self::addIfExists($merger, $certificate->acta_file_path);
        self::addIfExists($merger, $certificate->course->manual_file_path ?? null);

        $pdfContent = $merger->merge();

        // Asegura el directorio final en storage/certificates
        $certDir = storage_path('certificates');
        if (!is_dir($certDir)) {
            mkdir($certDir, 0775, true);
        }

        $fileName    = self::generateFileName($certificate); // p.ej. NOMBRE_CEDULA.TODO.pdf
        $absOutPath  = storage_path("certificates/{$fileName}"); // destino real en disco
        $relOutPath  = "storage/certificates/{$fileName}";       // ruta que guardarás en BD

        // Si existía un paquete anterior, elimínalo (soporta ambas ubicaciones posibles)
        if ($certificate->paquete_file_path) {
            self::safeDelete($certificate->paquete_file_path);
        }

        // Escribe el PDF final directamente en storage/certificates
        file_put_contents($absOutPath, $pdfContent);

        $certificate->paquete_file_path = $relOutPath;
        $certificate->save();

        \Log::info("Package generated at: {$relOutPath}");

        return $relOutPath;
    }

    private static function generateFileName(Certificate $certificate): string
    {
        $firstName = $certificate->holder->first_names ?? '';
        $lastName  = $certificate->holder->last_names ?? '';
        $idNum     = $certificate->holder->identification_number ?? '';
        $fullName  = trim("{$firstName} {$lastName} {$idNum}") ?: "Certificate_{$certificate->id}";

        $clean = self::sanitizeFileName($fullName);

        $fileName = "{$clean}.TODO.pdf";
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

    private static function removeAccents(string $s): string
    {
        $accents = [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
            'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n',
            'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u','À'=>'a','È'=>'e','Ì'=>'i','Ò'=>'o','Ù'=>'u',
        ];
        return strtr($s, $accents);
    }

    /**
     * Intenta resolver una ruta relativa guardada en BD a una ruta absoluta válida en disco.
     * Soporta:
     *   - Rutas absolutas (ya existentes)
     *   - "storage/certificates/..."  -> storage_path("certificates/...")
     *   - "certificates/..." en disco public -> Storage::disk('public')->path(...)
     *   - "app/public/..." (casos viejos)
     */
    private static function resolveAbsolutePath(?string $path): ?string
    {
        if (!$path) return null;

        // 1) Si ya es absoluta y existe
        if (preg_match('/^(\/|[A-Za-z]:\\\\)/', $path) && file_exists($path)) {
            return $path;
        }

        // Normaliza separadores
        $norm = str_replace('\\', '/', $path);

        // 2) Si empieza por "storage/..." y apunta a certificates, busca en storage_path("certificates/...")
        if (str_starts_with($norm, 'storage/certificates/')) {
            $candidate = storage_path(substr($norm, strlen('storage/'))); // -> storage_path('certificates/...')
            if (file_exists($candidate)) return $candidate;
        }

        // 3) Si es "certificates/..." en disco public (viejo enfoque)
        if (str_starts_with($norm, 'certificates/')) {
            if (Storage::disk('public')->exists($norm)) {
                return Storage::disk('public')->path($norm);
            }
            // también probar en storage_path('certificates/...') por si fue movido
            $candidate = storage_path($norm);
            if (file_exists($candidate)) return $candidate;
        }

        // 4) Si empieza por "app/public/..." (algún legado)
        if (str_starts_with($norm, 'app/public/')) {
            $candidate = storage_path($norm);
            if (file_exists($candidate)) return $candidate;
        }

        // 5) Último intento directo dentro de storage/
        $maybeStorage = storage_path($norm);
        if (file_exists($maybeStorage)) {
            return $maybeStorage;
        }

        return null;
    }

    private static function addIfExists(Merger $merger, ?string $path): bool
    {
        if (!$path) {
            \Log::debug("Path is null or empty");
            return false;
        }

        $abs = self::resolveAbsolutePath($path);
        if (!$abs) {
            \Log::warning("PDF no encontrado (no se pudo resolver): {$path}");
            return false;
        }

        if (!is_file($abs) || filesize($abs) === 0) {
            \Log::warning("PDF vacío o inexistente: {$abs}");
            return false;
        }

        if (strtolower(pathinfo($abs, PATHINFO_EXTENSION)) !== 'pdf') {
            \Log::warning("Archivo no es PDF: {$abs}");
            return false;
        }

        try {
            $merger->addFile($abs);
            \Log::debug("Successfully added: {$abs}");
            return true;
        } catch (\Exception $e) {
            \Log::error("Error adding PDF {$abs}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un archivo previo, soportando tanto la ubicación antigua (public)
     * como la nueva ("storage/certificates/...").
     */
    private static function safeDelete(string $storedPath): void
    {
        // Intento 1: si era del disco public
        if (Storage::disk('public')->exists($storedPath)) {
            Storage::disk('public')->delete($storedPath);
            \Log::info("Deleted old package from public disk: {$storedPath}");
            return;
        }

        // Intento 2: si era "storage/certificates/..."
        $abs = self::resolveAbsolutePath($storedPath);
        if ($abs && is_file($abs)) {
            @unlink($abs);
            \Log::info("Deleted old package from storage: {$abs}");
        }
    }
}
