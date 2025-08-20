<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCertificateRequest;
use App\Http\Requests\Admin\UpdateCertificateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Certificate;
use App\Models\CertificateHolder;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;


class AdminCertificateController extends Controller
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

    public function index(Request $request)
    {
        $query = Certificate::withRelations();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('series_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('holder', function ($subQ) use ($search) {
                        $subQ->search($search);
                    })
                    ->orWhereHas('course', function ($subQ) use ($search) {
                        $subQ->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $this->applyFilters($query, $request);

        $certificates = $query->orderBy('created_at', 'desc')->paginate(20);
        $courses = Course::select('id', 'name')->orderBy('name')->get();

        $stats = $this->getCertificateStats();

        return view('admin.certificates.index', compact('certificates', 'courses', 'stats'));
    }

    public function create()
    {
        $courses = Course::select('id', 'name', 'duration_hours', 'serial_prefix')
            ->orderBy('name')
            ->get();

        $idTypes = CertificateHolder::ID_TYPES;
        $bloodTypes = CertificateHolder::BLOOD_TYPES;

        return view('admin.certificates.create', compact('courses', 'idTypes', 'bloodTypes'));
    }

    public function store(StoreCertificateRequest $request)
    {
        try {
            DB::transaction(function () use ($request, &$certificate) {
                $holderData = $this->prepareHolderData($request);
                $holder = $this->createOrUpdateHolder($holderData);

                Certificate::where('certificate_holder_id', $holder->id)
                    ->where('course_id', $request->course_id)
                    ->get()
                    ->each->syncStatus();


                $result = Certificate::canIssueNew($holder->id, $request->course_id);

                if (!$result['allowed']) {
                    $reason = $result['reason'] ?? 'unknown';
                    $messages = [
                        'inactive' => 'Ya existe un certificado para este curso, pero está inactivo. Revíselos antes de crear uno nuevo.',
                        'active' => 'Esta persona ya tiene un certificado activo para este curso.',
                        'unknown' => 'No se puede emitir el certificado por una razón desconocida.',
                    ];

                    throw new \Exception($messages[$reason]);
                }

                $certificate = $this->createCertificate($request, $holder);

                if (in_array($result['reason'], ['expired', 'renewal']) && isset($result['certificate'])) {
                    $result['certificate']->update([
                        'status' => Certificate::STATUS_REPLACED,
                        'status_reason' => $result['reason'] === 'expired'
                            ? 'Reemplazado por vencimiento'
                            : 'Renovado antes del vencimiento',
                    ]);
                }



                $this->generateCertificatePDFs($certificate);
            });

            return redirect()->route('admin.certificates.show', $certificate)
                ->with('success', 'Certificado creado exitosamente.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al crear el certificado: ' . $e->getMessage()])
                ->withInput();
        }
    }


    public function show(Certificate $certificate)
    {
        $certificate->syncStatus();
        $certificate->loadMissing(['holder', 'course', 'issuer']);

        $displayInfo = $certificate->getDisplayInfo();
        $holderInfo = $certificate->holder->getCompleteInfo();

        $holderCertificates = $certificate->holder->certificates()
            ->where('id', '!=', $certificate->id)
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.certificates.show', compact(
            'certificate',
            'displayInfo',
            'holderInfo',
            'holderCertificates'
        ));
    }
    public function edit(Certificate $certificate)
    {
        $certificate->loadMissing(['holder', 'course']);
        $courses = Course::select('id', 'name', 'duration_hours')->orderBy('name')->get();
        $idTypes = CertificateHolder::ID_TYPES;
        $bloodTypes = CertificateHolder::BLOOD_TYPES;

        return view('admin.certificates.edit', compact(
            'certificate',
            'courses',
            'idTypes',
            'bloodTypes'
        ));
    }
    public function update(UpdateCertificateRequest $request, Certificate $certificate)
    {
        try {
            DB::transaction(function () use ($request, $certificate) {
                $this->assertHolderIdentificationUnique(
                    $request->identification_type,
                    $request->identification_number,
                    $certificate->holder->id
                );

                $holderData = $this->prepareHolderData($request);
                $certificate->holder->update($holderData);

                $certificateData = [
                    'course_id' => $request->course_id,
                    'issue_date' => $request->issue_date,
                    'status' => $request->status,
                ];

                if ($certificate->course_id != $request->course_id) {
                    $certificateData['expiry_date'] = Carbon::parse($request->issue_date)->addYear();
                }

                $certificate->update($certificateData);

                if ($certificate->wasChanged('course_id')) {
                    $this->generateCertificatePDFs($certificate);
                }
            });

            return redirect()->route('admin.certificates.show', $certificate)
                ->with('success', 'Certificado actualizado exitosamente.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Certificate $certificate)
    {
        try {
            $holderId = $certificate->certificate_holder_id;

            $certificate->delete();

            $this->cleanupOrphanedHolder($holderId);

            return redirect()->route('admin.certificates.index')
                ->with('success', 'Certificado eliminado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    public function toggleStatus(Certificate $certificate)
    {
        try {
            if ($certificate->status === Certificate::STATUS_ACTIVE) {
                $certificate->update([
                    'status' => Certificate::STATUS_INACTIVE,
                    'status_reason' => 'manual',
                ]);
                $statusLabel = 'desactivado';
            } else {
                $certificate->update([
                    'status' => Certificate::STATUS_ACTIVE,
                    'status_reason' => null,
                ]);
                $statusLabel = 'activado';
            }

            return redirect()->route('admin.certificates.show', $certificate)
                ->with('success', "El certificado ha sido {$statusLabel} correctamente.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cambiar el estado: ' . $e->getMessage()]);
        }
    }


    public function regeneratePDFs(Certificate $certificate)
    {
        try {
            $this->generateCertificatePDFs($certificate);
            return back()->with('success', 'PDFs regenerados exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al regenerar PDFs: ' . $e->getMessage()]);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'certificate_ids' => 'required|array|min:1',
            'certificate_ids.*' => 'exists:certificates,id'
        ]);

        try {
            $certificates = Certificate::whereIn('id', $request->certificate_ids);
            $count = $certificates->count();

            switch ($request->action) {
                case 'activate':
                    $certificates->update(['status' => Certificate::STATUS_ACTIVE]);
                    $message = "{$count} certificados activados correctamente.";
                    break;

                case 'deactivate':
                    $certificates->update(['status' => Certificate::STATUS_INACTIVE]);
                    $message = "{$count} certificados desactivados correctamente.";
                    break;

                case 'delete':
                    $holderIds = $certificates->pluck('certificate_holder_id')->unique();
                    $certificates->delete();

                    foreach ($holderIds as $holderId) {
                        $this->cleanupOrphanedHolder($holderId);
                    }

                    $message = "{$count} certificados eliminados correctamente.";
                    break;
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error en la operación masiva: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        return back()->withErrors(['error' => 'Funcionalidad de exportación en desarrollo.']);
    }

    public function expiringSoon(Request $request)
    {
        $days = $request->get('days', 30);
        $certificates = Certificate::getExpiringSoonWithRelations($days);

        return view('admin.certificates.expiring', compact('certificates', 'days'));
    }

    public function searchHolders(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $holders = CertificateHolder::search($search)
            ->limit(10)
            ->get(['id', 'first_names', 'last_names', 'identification_type', 'identification_number'])
            ->map(function ($holder) {
                return [
                    'id' => $holder->id,
                    'text' => $holder->full_name . ' - ' . $holder->short_identification,
                    'full_name' => $holder->full_name,
                    'identification' => $holder->short_identification,
                ];
            });

        return response()->json($holders);
    }

    public function getHolderDetails(CertificateHolder $holder): JsonResponse
    {
        try {
            $holderInfo = $holder->getCompleteInfo();
            $activeCertificates = $holder->getActiveCertificates()->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'series_number' => $cert->series_number,
                    'course_name' => $cert->course->name ?? 'N/A',
                    'issue_date' => $cert->issue_date?->format('d/m/Y'),
                    'expiry_date' => $cert->expiry_date?->format('d/m/Y'),
                    'is_expired' => $cert->isExpired(),
                ];
            });

            return response()->json([
                'success' => true,
                'holder' => $holderInfo,
                'certificates' => $activeCertificates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkCourseEligibility(Request $request): JsonResponse
    {
        $request->validate([
            'holder_id' => 'required|exists:certificate_holders,id',
            'course_id' => 'required|exists:courses,id'
        ]);

        try {
            $holder = CertificateHolder::findOrFail($request->holder_id);
            $hasActiveCertificate = $holder->hasCertificateForCourse($request->course_id);

            return response()->json([
                'success' => true,
                'eligible' => !$hasActiveCertificate,
                'message' => $hasActiveCertificate
                    ? 'Esta persona ya tiene un certificado activo para este curso.'
                    : 'Esta persona puede recibir un certificado para este curso.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar elegibilidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validateHolderIdentification(Request $request): JsonResponse
    {
        $request->validate([
            'identification_type' => 'required|string',
            'identification_number' => 'required|string',
            'exclude_holder_id' => 'nullable|exists:certificate_holders,id'
        ]);

        try {
            $existing = CertificateHolder::where('identification_type', $request->identification_type)
                ->where('identification_number', $request->identification_number);

            if ($request->exclude_holder_id) {
                $existing->where('id', '!=', $request->exclude_holder_id);
            }

            $holder = $existing->first();

            return response()->json([
                'success' => true,
                'exists' => !is_null($holder),
                'holder' => $holder ? [
                    'id' => $holder->id,
                    'full_name' => $holder->full_name,
                    'identification' => $holder->short_identification,
                ] : null,
                'message' => $holder
                    ? 'Ya existe una persona con esa identificación.'
                    : 'Identificación disponible.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar identificación: ' . $e->getMessage()
            ], 500);
        }
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        match ($request->filter) {
            'expired' => $query->expired(),
            'expiring_soon' => $query->expiringSoon($request->get('expiring_days', 30)),
            'active' => $query->active(),
            'inactive' => $query->inactive(),
            default => null
        };
    }

    private function prepareHolderData(Request $request): array
    {
        $holderData = $request->only([
            'first_names',
            'last_names',
            'identification_type',
            'identification_number',
            'identification_place',
            'blood_type',
            'email',
            'phone',
            'has_drivers_license',
            'drivers_license_category'
        ]);

        $holderData['first_names'] = strtoupper($holderData['first_names'] ?? '');
        $holderData['last_names'] = strtoupper($holderData['last_names'] ?? '');
        $holderData['identification_place'] = strtoupper($holderData['identification_place'] ?? '');
        
        if (($holderData['has_drivers_license'] ?? 'NO') === 'NO') {
            $holderData['drivers_license_category'] = null;
        }

        if ($request->hasFile('photo')) {
            $holderData['photo_path'] = $request->file('photo')
                ->store('certificate_photos', 'public');
        }

        return $holderData;
    }

    private function createOrUpdateHolder(array $holderData): CertificateHolder
    {
        $existing = CertificateHolder::findByIdentification(
            $holderData['identification_type'],
            $holderData['identification_number']
        );

        if ($existing) {
            if (!isset($holderData['photo_path'])) {
                unset($holderData['photo_path']);
            }
            $existing->update($holderData);
            return $existing;
        }

        return CertificateHolder::create($holderData);
    }

    private function assertHolderIdentificationUnique(string $type, string $number, int $excludeId = null): void
    {
        $query = CertificateHolder::where('identification_type', $type)
            ->where('identification_number', $number);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \Exception('Ya existe otra persona con esa identificación.');
        }
    }

    private function createCertificate(Request $request, CertificateHolder $holder): Certificate
    {
        $expiryDate = Carbon::parse($request->issue_date)->addYear();

        return Certificate::create([
            'certificate_holder_id' => $holder->id,
            'course_id' => $request->course_id,
            'issue_date' => $request->issue_date,
            'expiry_date' => $expiryDate,
            'issued_by' => auth()->id(),
            'status' => Certificate::STATUS_ACTIVE,
            'series_number' => $this->generateSeriesNumber($request->course_id),
            'certificate_file_path' => '',
            'card_file_path' => '',
        ]);
    }

    private function generateCertificatePDFs(Certificate $certificate): void
    {
        $certificate->loadMissing(['holder', 'course', 'issuer']);

        try {
            $certificatePath = \App\Services\CertificateWordService::generate($certificate);
            if ($certificatePath) {
                $certificate->certificate_file_path = $certificatePath;
                \Log::info("Certificate generated successfully: {$certificatePath}");
            } else {
                \Log::warning("CertificateWordService returned null for certificate {$certificate->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Error generating certificate for certificate {$certificate->id}: " . $e->getMessage());
        }

        try {
            $cardPath = \App\Services\CardWordService::generate($certificate);
            if ($cardPath) {
                $certificate->card_file_path = $cardPath;
                \Log::info("Card generated successfully: {$cardPath}");
            } else {
                \Log::warning("CardWordService returned null for certificate {$certificate->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Error generating card for certificate {$certificate->id}: " . $e->getMessage());
        }

        try {
            $actaPath = \App\Services\ActaService::generate($certificate);
            if ($actaPath) {
                $certificate->acta_file_path = $actaPath;
                \Log::info("Acta generated successfully: {$actaPath}");
            } else {
                \Log::warning("ActaService returned null for certificate {$certificate->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Error generating acta for certificate {$certificate->id}: " . $e->getMessage());
        }

        try {
            $paquetePath = \App\Services\FullPackageService::generate($certificate);
            if ($paquetePath) {
                $certificate->paquete_file_path = $paquetePath;
                \Log::info("Package generated successfully: {$paquetePath}");
            } else {
                \Log::warning("FullPackageService returned null for certificate {$certificate->id}");
            }
        } catch (\Exception $e) {
            \Log::error("Error generating package for certificate {$certificate->id}: " . $e->getMessage());
        }

        $saved = $certificate->save();

        if (!$saved) {
            \Log::error("Failed to save certificate {$certificate->id} with file paths");
            throw new \Exception("Failed to save certificate with file paths");
        }

        \Log::info("Certificate {$certificate->id} saved with paths - Certificate: {$certificate->certificate_file_path}, Card: {$certificate->card_file_path}, Acta: {$certificate->acta_file_path}, Package: {$certificate->paquete_file_path}");
    }

    private function generateSeriesNumber(int $courseId): string
    {
        $course = Course::findOrFail($courseId);
        $prefix = $course->serial_prefix ?? 'CUR';

        $lastCertificate = Certificate::where('series_number', 'like', "{$prefix}-%")
            ->orderByRaw("CAST(SUBSTRING(series_number, LENGTH(?) + 2) AS UNSIGNED) DESC", [$prefix])
            ->first();

        $nextNumber = $lastCertificate
            ? ((int) substr($lastCertificate->series_number, strlen($prefix) + 1)) + 1
            : 1;

        return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function cleanupOrphanedHolder(int $holderId): void
    {
        $holder = CertificateHolder::find($holderId);

        if ($holder && !$holder->certificates()->exists()) {
            $holder->delete();
        }
    }

    private function getCertificateStats(): array
    {
        return [
            'total' => Certificate::count(),
            'active' => Certificate::active()->count(),
            'inactive' => Certificate::inactive()->count(),
            'expired' => Certificate::expired()->count(),
            'expiring_soon' => Certificate::expiringSoon(30)->count(),
            'this_month' => Certificate::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_holders' => CertificateHolder::count(),
            'holders_with_active_certs' => CertificateHolder::whereHas(
                'certificates',
                fn($q) => $q->active()
            )->count(),
        ];
    }
}
