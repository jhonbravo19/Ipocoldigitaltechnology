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
            'certificate_title' => 'required|string|max:255',
            'intro_text' => 'nullable|string|max:1000',
            'signature_1_name' => 'nullable|string|max:255',
            'signature_1_position' => 'nullable|string|max:255',
            'signature_2_name' => 'nullable|string|max:255',
            'signature_2_position' => 'nullable|string|max:255',
            'additional_text' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $config = CertificateTemplateConfig::getActiveConfig();
            $config->updateTextFields($request->only([
                'certificate_title',
                'intro_text',
                'signature_1_name',
                'signature_1_position',
                'signature_2_name',
                'signature_2_position',
                'additional_text'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Campos de texto actualizados correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $field = $request->get('field');

        if (!in_array($field, ['company_logo', 'background_image', 'carnet_background_image', 'signature_1_image', 'signature_2_image'])) {
            return response()->json(['success' => false, 'message' => 'Campo no válido'], 400);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:' .
                ($field === 'background_image' ? '5120' : '2048'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $config = CertificateTemplateConfig::getActiveConfig();
            $path = $request->file('image')->store('templates', 'public');

            $config->updateImageField($field, $path);

            $urlAttribute = str_replace(['_image', '_logo'], ['_image_url', '_logo_url'], $field);

            return response()->json([
                'success' => true,
                'message' => 'Imagen subida correctamente.',
                'image_url' => $config->$urlAttribute,
                'field' => $field
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteImage(Request $request): JsonResponse
    {
        $field = $request->get('field');
        $allowedFields = ['company_logo', 'background_image', 'carnet_background_image', 'signature_1_image', 'signature_2_image'];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Campo no válido'], 400);
        }

        try {
            $config = CertificateTemplateConfig::getActiveConfig();
            $config->updateImageField($field, null);

            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ], 500);
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
            'last_names' => 'Apellido Prueba',
        ];

        $sampleCourse = (object) [
            'name' => 'Curso Intensivo Avanzado de Capacitación Integral en Normas de Seguridad Vial, Transporte de Carga Pesada y Prevención de Riesgos Laborales con Enfoque en Regulaciones Nacionales e Internacionales 2025',
            'duration_hours' => 40
        ];

        return view('admin.template.preview', compact('config', 'sampleHolder', 'sampleCourse'));
    }

    public function getConfigStatus(): JsonResponse
    {
        try {
            $config = CertificateTemplateConfig::getActiveConfig();
            $status = $config->getConfigurationStatus();

            return response()->json([
                'success' => true,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getValidationRules(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'certificate_title' => 'required|string|max:255',
            'intro_text' => 'nullable|string|max:1000',
            'signature_1_name' => 'nullable|string|max:255',
            'signature_1_position' => 'nullable|string|max:255',
            'signature_2_name' => 'nullable|string|max:255',
            'signature_2_position' => 'nullable|string|max:255',
            'additional_text' => 'nullable|string|max:1000',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'carnet_background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'signature_1_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_2_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    }

    private function applyTextFieldUpdates(CertificateTemplateConfig $config, Request $request): void
    {
        $textFields = [
            'certificate_title',
            'intro_text',
            'signature_1_name',
            'signature_1_position',
            'signature_2_name',
            'signature_2_position',
            'additional_text'
        ];

        $config->updateTextFields($request->only($textFields));
    }

    private function updateImageFields(CertificateTemplateConfig $config, Request $request): void
    {
        $imageFields = ['company_logo', 'background_image', 'carnet_background_image', 'signature_1_image', 'signature_2_image'];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("templates", "public");
                $config->updateImageField($field, $path);
            }
        }
    }
}
