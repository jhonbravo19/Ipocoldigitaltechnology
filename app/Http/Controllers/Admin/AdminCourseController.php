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

        $data = $request->only([
            'name',
            'description',
            'duration_hours',
            'serial_prefix'
        ]);

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

        $data = $request->only([
            'name',
            'description',
            'duration_hours',
            'serial_prefix'
        ]);

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

        Storage::disk('public')->deleteDirectory("courses/{$course->id}");

        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', '✅ Curso eliminado exitosamente.');
    }

    public function show(Course $course)
    {
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Maneja la subida de archivos con nombres consistentes
     */
    private function handleFileUploads(Course $course, Request $request, bool $isUpdate = false)
    {
        $courseDir = "courses/{$course->id}";

        if (!Storage::disk('public')->exists($courseDir)) {
            Storage::disk('public')->makeDirectory($courseDir);
        }

        if ($request->hasFile('acta_template_file')) {
            $actaPath = "{$courseDir}/acta_template.docx";
            
            if ($isUpdate && $course->acta_template_file_path) {
                Storage::disk('public')->delete($course->acta_template_file_path);
            }
            
            $request->file('acta_template_file')->storeAs($courseDir, 'acta_template.docx', 'public');
            $course->acta_template_file_path = $actaPath;
        }

        if ($request->hasFile('manual_file')) {
            $extension = $request->file('manual_file')->getClientOriginalExtension();
            $manualPath = "{$courseDir}/manual.{$extension}";
            
            if ($isUpdate && $course->manual_file_path) {
                Storage::disk('public')->delete($course->manual_file_path);
            }
            
            $request->file('manual_file')->storeAs($courseDir, "manual.{$extension}", 'public');
            $course->manual_file_path = $manualPath;
        }

        if ($request->hasFile('card_back_file')) {
            $extension = $request->file('card_back_file')->getClientOriginalExtension();
            $cardBackPath = "{$courseDir}/card_back.{$extension}";
            
            if ($isUpdate && $course->card_back_file_path) {
                Storage::disk('public')->delete($course->card_back_file_path);
            }
            
            $request->file('card_back_file')->storeAs($courseDir, "card_back.{$extension}", 'public');
            $course->card_back_file_path = $cardBackPath;
        }

        $course->save();

        \Log::info("Course {$course->id} files updated:", [
            'manual_file_path' => $course->manual_file_path,
            'card_back_file_path' => $course->card_back_file_path,
            'acta_template_file_path' => $course->acta_template_file_path,
        ]);
    }
}
