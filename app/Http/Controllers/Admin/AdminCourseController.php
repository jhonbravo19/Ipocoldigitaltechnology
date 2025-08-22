<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCourseController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'regex:/^[A-ZÁÉÍÓÚÜÑ0-9 ,.\-:]+$/i', 'max:255'],
            'description' => 'nullable|string',
            'duration_hours' => 'required|integer|min:1',
            'serial_prefix' => ['required', 'regex:/^[A-Z0-9]+$/', 'max:10', 'unique:courses,serial_prefix'],
            'manual_file' => 'nullable|file|mimes:pdf,docx|max:5120',
            'card_back_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'acta_template_file' => 'nullable|file|mimes:docx|max:5120',
        ], [
            'name.regex' => 'El nombre solo puede contener letras, números y espacios.',
            'serial_prefix.regex' => 'El prefijo solo puede contener letras mayúsculas y números.',
        ]);

        $data = $request->only(['name','description','duration_hours','serial_prefix']);
        $data['name'] = strtoupper($data['name']);
        $data['serial_prefix'] = strtoupper($data['serial_prefix']);

        $course = Course::create($data);

        $this->handleFileUploads($course, $request);

        return redirect()->route('admin.courses.index')->with('success', 'Curso creado correctamente.');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => ['required', 'regex:/^[A-ZÁÉÍÓÚÜÑ0-9 ,.\-:]+$/i', 'max:255'],
            'description' => 'nullable|string',
            'duration_hours' => 'required|integer|min:1',
            'serial_prefix' => ['required', 'regex:/^[A-Z0-9]+$/', 'max:10', 'unique:courses,serial_prefix,' . $course->id],
            'manual_file' => 'nullable|file|mimes:pdf,docx|max:5120',
            'card_back_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'acta_template_file' => 'nullable|file|mimes:docx|max:5120',
        ], [
            'name.regex' => 'El nombre solo puede contener letras, números y espacios.',
            'serial_prefix.regex' => 'El prefijo solo puede contener letras mayúsculas y números.',
        ]);

        $data = $request->only(['name','description','duration_hours','serial_prefix']);
        $data['name'] = strtoupper($data['name']);
        $data['serial_prefix'] = strtoupper($data['serial_prefix']);

        $course->update($data);

        $this->handleFileUploads($course, $request, true);

        return redirect()->route('admin.courses.index')->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Course $course)
    {
        if ($course->certificates()->exists()) {
            return back()->with('warning', '⚠️ No se puede eliminar un curso que tiene certificados asociados.');
        }

        // 1) Borrar posible carpeta vieja en public
        Storage::disk('public')->deleteDirectory("courses/{$course->id}");

        // 2) Borrar carpeta nueva en storage/courses/{id}
        $absDir = storage_path("courses/{$course->id}");
        if (is_dir($absDir)) {
            $this->deleteDirectoryRecursive($absDir);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', '✅ Curso eliminado exitosamente.');
    }

    public function show(Course $course)
    {
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Maneja la subida de archivos guardando en storage/courses/{id}
     * y persistiendo rutas "storage/courses/..."
     */
    private function handleFileUploads(Course $course, Request $request, bool $isUpdate = false)
    {
        $relDir  = "storage/courses/{$course->id}";   // lo que guardamos en BD
        $absDir  = storage_path("courses/{$course->id}"); // dónde se guarda realmente

        if (!is_dir($absDir)) {
            mkdir($absDir, 0775, true);
        }

        // ACTA TEMPLATE (docx)
        if ($request->hasFile('acta_template_file')) {
            if ($isUpdate && $course->acta_template_file_path) {
                $this->safeDelete($course->acta_template_file_path);
            }
            $destAbs = "{$absDir}/acta_template.docx";
            $request->file('acta_template_file')->move($absDir, 'acta_template.docx');
            $course->acta_template_file_path = "{$relDir}/acta_template.docx";
        }

        // MANUAL (pdf o docx)
        if ($request->hasFile('manual_file')) {
            if ($isUpdate && $course->manual_file_path) {
                $this->safeDelete($course->manual_file_path);
            }
            $ext = strtolower($request->file('manual_file')->getClientOriginalExtension());
            $destAbs = "{$absDir}/manual.{$ext}";
            $request->file('manual_file')->move($absDir, "manual.{$ext}");
            $course->manual_file_path = "{$relDir}/manual.{$ext}";
        }

        // CARD BACK (pdf o imagen)
        if ($request->hasFile('card_back_file')) {
            if ($isUpdate && $course->card_back_file_path) {
                $this->safeDelete($course->card_back_file_path);
            }
            $ext = strtolower($request->file('card_back_file')->getClientOriginalExtension());
            $destAbs = "{$absDir}/card_back.{$ext}";
            $request->file('card_back_file')->move($absDir, "card_back.{$ext}");
            $course->card_back_file_path = "{$relDir}/card_back.{$ext}";
        }

        $course->save();

        \Log::info("Course {$course->id} files updated:", [
            'manual_file_path' => $course->manual_file_path,
            'card_back_file_path' => $course->card_back_file_path,
            'acta_template_file_path' => $course->acta_template_file_path,
        ]);
    }

    /**
     * Borra un archivo anterior, soportando:
     *  - rutas antiguas en disco public (p.ej. "courses/{id}/manual.pdf")
     *  - rutas nuevas "storage/courses/{id}/manual.pdf"
     *  - rutas absolutas
     */
    private function safeDelete(string $storedPath): void
    {
        // Caso 1: en public
        if (Storage::disk('public')->exists($storedPath)) {
            Storage::disk('public')->delete($storedPath);
            \Log::info("Deleted old file from public disk: {$storedPath}");
            return;
        }

        // Caso 2: ruta nueva relativa "storage/..."
        $abs = $this->resolveAbsolutePath($storedPath);
        if ($abs && is_file($abs)) {
            @unlink($abs);
            \Log::info("Deleted old file from storage: {$abs}");
        }
    }

    /**
     * Resuelve rutas:
     *  - absolutas
     *  - "storage/courses/..." -> storage_path("courses/...")
     *  - "courses/..." (viejo public) -> Storage::disk('public')->path(...)
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

        if (str_starts_with($norm, 'storage/courses/')) {
            $candidate = storage_path(substr($norm, strlen('storage/'))); // -> storage_path('courses/...')
            if (file_exists($candidate)) return $candidate;
        }

        if (str_starts_with($norm, 'courses/')) {
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

    private function deleteDirectoryRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectoryRecursive($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
