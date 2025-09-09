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

        // Agrega en el orden deseado (resuelve rutas de forma robusta)
        self::addIfExists($merger, $certificate->certificate_file_path);
        self::addIfExists($merger, $certificate->card_file_path);
        self::addIfExists($merger, $certificate->course->card_back_file_path ?? null);
        self::addIfExists($merger, $certificate->acta_file_path);
        self::addIfExists($merger, $certificate->course->manual_file_path ?? null);

        $pdfContent = $merger->merge();

        // Asegura carpeta final en disco 'public' -> storage/app/public/certificates
        Storage::disk('public')->makeDirectory('certificates');

        $fileName = self::generateFileName($certificate); // p.ej. NOMBRE_CEDULA.TODO.pdf

        // Ruta absoluta (real en disco) y ruta web (para BD)
        $absOutPath = Storage::disk('public')->path("certificates/{$fileName}");      // /full/path/storage/app/public/certificates/...
        $relOutWeb  = "storage/certificates/{$fileName}";                             // para <a href="{{ asset(...) }}">

        // Si existía un paquete anterior, elimínalo (soporta ubicaciones viejas/nuevas)
        if (!empty($certificate->paquete_file_path)) {
            self::safeDelete($certificate->paquete_file_path);
        }

        // Escribe el PDF final en el disco público
        file_put_contents($absOutPath, $pdfContent);

        $certificate->paquete_file_path = $relOutWeb;
        $certificate->save();

        \Log::info("Package generated at: {$relOutWeb}");

        return $relOutWeb;
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
     * Resuelve una ruta (web/relativa/absoluta) a una ruta ABSOLUTA existente.
     * Soporta:
     *  - Absolutas ya existentes
     *  - "storage/certificates/..."  -> disco public ("certificates/...") y fallback a storage_path("certificates/...")
     *  - "certificates/..." en disco public
     *  - "app/public/..." (legado)
     */
    private static function resolveAbsolutePath(?string $path): ?string
    {
        if (!$path) return null;

        // 1) Si ya es absoluta y existe
        if (preg_match('/^(\/|[A-Za-z]:\\\\)/', $path) && file_exists($path)) {
            return $path;
        }

        $norm = str_replace('\\', '/', $path);

        // 2) "storage/certificates/..." => primero intenta en disco public
        if (str_starts_with($norm, 'storage/certificates/')) {
            $publicRel = substr($norm, strlen('storage/')); // "certificates/..."
            if (Storage::disk('public')->exists($publicRel)) {
                return Storage::disk('public')->path($publicRel);
            }
            // Fallback: vieja ubicación directa en storage/
            $candidate = storage_path($publicRel); // storage_path("certificates/...")
            if (file_exists($candidate)) return $candidate;
        }

        // 3) "certificates/..." (relativo al disco public)
        if (str_starts_with($norm, 'certificates/')) {
            if (Storage::disk('public')->exists($norm)) {
                return Storage::disk('public')->path($norm);
            }
            // Fallback: vieja ubicación directa en storage/
            $candidate = storage_path($norm);
            if (file_exists($candidate)) return $candidate;
        }

        // 4) "app/public/..." (legado)
        if (str_starts_with($norm, 'app/public/')) {
            $candidate = storage_path($norm);
            if (file_exists($candidate)) return $candidate;
        }

        // 5) Último intento dentro de storage/
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
     * Elimina un archivo previo, soportando tanto la ubicación antigua (directo en storage/)
     * como la nueva (disco public con ruta web "storage/certificates/...").
     */
    private static function safeDelete(string $storedPath): void
    {
        $norm = str_replace('\\', '/', $storedPath);

        // Si viene como "storage/certificates/..." => en disco public el relativo es "certificates/..."
        if (str_starts_with($norm, 'storage/')) {
            $publicRel = substr($norm, strlen('storage/')); // "certificates/..."
            if (Storage::disk('public')->exists($publicRel)) {
                Storage::disk('public')->delete($publicRel);
                \Log::info("Deleted old package from public disk: {$publicRel}");
                return;
            }
        }

        // Intento directo en disco public por si en BD quedó "certificates/..."
        if (str_starts_with($norm, 'certificates/')) {
            if (Storage::disk('public')->exists($norm)) {
                Storage::disk('public')->delete($norm);
                \Log::info("Deleted old package from public disk: {$norm}");
                return;
            }
        }

        // Fallback: ubicación antigua directa dentro de storage/
        $abs = self::resolveAbsolutePath($storedPath);
        if ($abs && is_file($abs)) {
            @unlink($abs);
            \Log::info("Deleted old package from storage: {$abs}");
        }
    }
}
