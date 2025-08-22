<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplateConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AdminTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'No tienes permisos de administrador');
            }
            return $next($request);
        });
    }

    public function showConfig()
    {
        $config = CertificateTemplateConfig::getActiveConfig();
        $configStatus = $config->getConfigurationStatus();
        $completeConfig = $config->getCompleteConfig();

        return view('admin.template.config', compact('config', 'configStatus', 'completeConfig'));
    }

    public function updateConfig(Request $request)
    {
        $validator = $this->getValidationRules($request);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $config = CertificateTemplateConfig::getActiveConfig();

            $this->applyTextFieldUpdates($config, $request);
            $this->updateImageFields($config, $request);

            return redirect()->back()->with('success', 'Configuración actualizada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error al actualizar la configuración: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function updateTextFields(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'certificate_title'   => 'required|string|max:255',
            'intro_text'          => 'nullable|string|max:1000',
            'signature_1_name'    => 'nullable|string|max:255',
            'signature_1_position'=> 'nullable|string|max:255',
            'signature_2_name'    => 'nullable|string|max:255',
            'signature_2_position'=> 'nullable|string|max:255',
            'additional_text'     => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'errors' => $validator->errors()], 422);
        }

        try {
            $config = CertificateTemplateConfig::getActiveConfig();
            $config->updateTextFields($request->only([
                'certificate_title','intro_text',
                'signature_1_name','signature_1_position',
                'signature_2_name','signature_2_position',
                'additional_text'
            ]));

            return response()->json(['success' => true,'message' => 'Campos de texto actualizados correctamente.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => 'Error al actualizar: '.$e->getMessage()], 500);
        }
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $field = $request->get('field');

        if (!in_array($field, ['company_logo','background_image','carnet_background_image','signature_1_image','signature_2_image'])) {
            return response()->json(['success' => false, 'message' => 'Campo no válido'], 400);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:' . ($field === 'background_image' || $field === 'carnet_background_image' ? '5120' : '2048'),
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'errors' => $validator->errors()], 422);
        }

        try {
            $config = CertificateTemplateConfig::getActiveConfig();

            // Asegura directorio absoluto en storage/templates
            $absDir = storage_path('templates');
            if (!is_dir($absDir)) {
                mkdir($absDir, 0775, true);
            }

            // Nombre consistente por campo
            $ext = strtolower($request->file('image')->getClientOriginalExtension());
            $base = match ($field) {
                'company_logo'          => 'company_logo',
                'background_image'      => 'background',
                'carnet_background_image'=> 'carnet_background',
                'signature_1_image'     => 'signature_1',
                'signature_2_image'     => 'signature_2',
                default                 => 'image',
            };
            $fileName = "{$base}.{$ext}";

            // Si existe anterior, elimínalo de forma segura (soporta public y storage)
            $current = $config->$field ?? null;
            if ($current) {
                $this->safeDelete($current);
            }

            // Mover archivo subido a storage/templates
            $request->file('image')->move($absDir, $fileName);

            // Guardar en BD como ruta relativa nueva "storage/templates/..."
            $relPath = "storage/templates/{$fileName}";
            $config->updateImageField($field, $relPath);

            // Si tu modelo expone *_url, lo reutilizamos
            $urlAttribute = str_replace(['_image', '_logo'], ['_image_url', '_logo_url'], $field);

            return response()->json([
                'success'   => true,
                'message'   => 'Imagen subida correctamente.',
                'image_url' => $config->$urlAttribute ?? null, // si tu accessor lo provee
                'field'     => $field
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => 'Error al subir la imagen: '.$e->getMessage()], 500);
        }
    }

    public function deleteImage(Request $request): JsonResponse
    {
        $field = $request->get('field');
        $allowedFields = ['company_logo','background_image','carnet_background_image','signature_1_image','signature_2_image'];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Campo no válido'], 400);
        }

        try {
            $config = CertificateTemplateConfig::getActiveConfig();

            // Borra archivo físico si hay uno
            $current = $config->$field ?? null;
            if ($current) {
                $this->safeDelete($current);
            }

            // Limpia campo en BD
            $config->updateImageField($field, null);

            return response()->json(['success' => true,'message' => 'Imagen eliminada correctamente.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => 'Error al eliminar la imagen: '.$e->getMessage()], 500);
        }
    }

    public function resetConfig()
    {
        try {
            $config = CertificateTemplateConfig::getActiveConfig();
            $config->resetToDefaults();

            return redirect()->back()->with('success', 'Configuración restablecida a valores por defecto.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error al restablecer la configuración: ' . $e->getMessage()]);
        }
    }

    public function previewCertificate(Request $request)
    {
        $config = CertificateTemplateConfig::getActiveConfig();

        if ($request->isMethod('post')) {
            $config->fill($request->all());
        }

        $sampleHolder = (object) [
            'first_names' => 'Nombre Prueba ',
            'last_names'  => 'Apellido Prueba',
        ];

        $sampleCourse = (object) [
            'name'           => 'Curso Intensivo Avanzado de Capacitación Integral en Normas de Seguridad Vial, Transporte de Carga Pesada y Prevención de Riesgos Laborales con Enfoque en Regulaciones Nacionales e Internacionales 2025',
            'duration_hours' => 40
        ];

        return view('admin.template.preview', compact('config', 'sampleHolder', 'sampleCourse'));
    }

    public function getConfigStatus(): JsonResponse
    {
        try {
            $config = CertificateTemplateConfig::getActiveConfig();
            $status = $config->getConfigurationStatus();

            return response()->json(['success' => true,'status' => $status]);

        } catch (\Exception $e) {
            return response()->json(['success' => false,'message' => $e->getMessage()], 500);
        }
    }

    private function getValidationRules(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'certificate_title'       => 'required|string|max:255',
            'intro_text'              => 'nullable|string|max:1000',
            'signature_1_name'        => 'nullable|string|max:255',
            'signature_1_position'    => 'nullable|string|max:255',
            'signature_2_name'        => 'nullable|string|max:255',
            'signature_2_position'    => 'nullable|string|max:255',
            'additional_text'         => 'nullable|string|max:1000',
            'company_logo'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'carnet_background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'signature_1_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_2_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    }

    private function applyTextFieldUpdates(CertificateTemplateConfig $config, Request $request): void
    {
        $textFields = [
            'certificate_title','intro_text',
            'signature_1_name','signature_1_position',
            'signature_2_name','signature_2_position',
            'additional_text'
        ];

        $config->updateTextFields($request->only($textFields));
    }

    /**
     * Guarda imágenes en storage/templates y persiste "storage/templates/..."
     * Borrando versiones anteriores (public o storage) de forma segura.
     */
    private function updateImageFields(CertificateTemplateConfig $config, Request $request): void
    {
        $fields = ['company_logo','background_image','carnet_background_image','signature_1_image','signature_2_image'];

        $absDir = storage_path('templates');
        if (!is_dir($absDir)) {
            mkdir($absDir, 0775, true);
        }

        foreach ($fields as $field) {
            if (!$request->hasFile($field)) continue;

            // borra anterior si existe
            if (!empty($config->$field)) {
                $this->safeDelete($config->$field);
            }

            $ext = strtolower($request->file($field)->getClientOriginalExtension());
            $base = match ($field) {
                'company_logo'          => 'company_logo',
                'background_image'      => 'background',
                'carnet_background_image'=> 'carnet_background',
                'signature_1_image'     => 'signature_1',
                'signature_2_image'     => 'signature_2',
                default                 => 'image',
            };

            $fileName = "{$base}.{$ext}";
            $request->file($field)->move($absDir, $fileName);

            $relPath = "storage/templates/{$fileName}";
            $config->updateImageField($field, $relPath);
        }
    }

    /**
     * Elimina un archivo soportando:
     *  - disco public (p.ej. "templates/archivo.jpg")
     *  - nueva ubicación "storage/templates/archivo.jpg"
     *  - rutas absolutas
     */
    private function safeDelete(string $storedPath): void
    {
        // 1) Si era del disco public (rutas antiguas)
        if (Storage::disk('public')->exists($storedPath)) {
            Storage::disk('public')->delete($storedPath);
            \Log::info("Deleted old template file from public disk: {$storedPath}");
            return;
        }

        // 2) Resolver a absoluta en storage
        $abs = $this->resolveAbsolutePath($storedPath);
        if ($abs && is_file($abs)) {
            @unlink($abs);
            \Log::info("Deleted old template file from storage: {$abs}");
        }
    }

    /**
     * Resuelve:
     *  - Absolutas existentes
     *  - "storage/templates/..." -> storage_path("templates/...")
     *  - "templates/..." (viejo public) -> Storage::disk('public')->path(...)
     *  - "app/public/..." -> storage_path("app/public/...")
     *  - intento final: storage_path($norm)
     */
    private function resolveAbsolutePath(?string $path): ?string
    {
        if (!$path) return null;

        if (preg_match('/^(\/|[A-Za-z]:\\\\)/', $path) && file_exists($path)) {
            return $path;
        }

        $norm = str_replace('\\', '/', $path);

        if (str_starts_with($norm, 'storage/templates/')) {
            $candidate = storage_path(substr($norm, strlen('storage/'))); // -> storage_path('templates/...')
            if (file_exists($candidate)) return $candidate;
        }

        if (str_starts_with($norm, 'templates/')) {
            if (Storage::disk('public')->exists($norm)) {
                return Storage::disk('public')->path($norm);
            }
            $candidate = storage_path($norm);
            if (file_exists($candidate)) return $candidate;
        }

        if (str_starts_with($norm, 'app/public/')) {
            $candidate = storage_path($norm);
            if (file_exists($candidate)) return $candidate;
        }

        $maybe = storage_path($norm);
        if (file_exists($maybe)) return $maybe;

        return null;
    }
}
