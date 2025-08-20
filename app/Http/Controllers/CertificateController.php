<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateTemplateConfig;
use App\Models\CertificateHolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CertificateController extends Controller
{
    public function publicSearchForm()
    {
        return view('certificates.search');
    }

    public function publicSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|numeric|digits_between:6,15',
        ], [
            'query.required' => 'Debes ingresar tu número de cédula.',
            'query.numeric' => 'La cédula debe contener solo números.',
            'query.digits_between' => 'La cédula debe tener entre 6 y 15 dígitos.',
        ]);

        $query = trim($request->input('query'));

        $certificates = Certificate::searchPublic('identification', $query)
            ->orderBy('issue_date', 'desc')
            ->get();

        if ($certificates->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No se encontraron certificados con ese número de cédula.')
                ->withInput();
        }

        return view('certificates.results', compact('certificates'));
    }


    public function showPublic(string $seriesNumber)
    {
        $certificate = Certificate::findBySeries($seriesNumber);

        if (!$certificate) {
            return redirect()->route('certificates.search')
                ->with('error', 'No se encontró el certificado solicitado.');
        }

        $displayInfo = $certificate->getDisplayInfo();
        return view('certificates.public-detail', compact('certificate', 'displayInfo'));
    }

    public function myCertificates()
    {
        $user = auth()->user();

        $holder = CertificateHolder::where('identification_number', $user->identification_number)->first();

        if (!$holder) {
            return redirect()->route('certificates.search')
                ->with('error', 'No se encontraron certificados asociados a tu identificación.');
        }

        $activeCertificates = $holder->getValidCertificates();
        $expiredCertificates = $holder->getExpiredCertificates();

        return view('certificates.my-certificates', compact(
            'holder',
            'activeCertificates',
            'expiredCertificates'
        ));
    }

    public function showMyCertificate(Certificate $certificate)
    {
        $user = auth()->user();

        if ($certificate->holder->identification_number !== $user->identification_number) {
            abort(403, 'No tienes permiso para ver este certificado.');
        }

        $displayInfo = $certificate->getDisplayInfo();
        return view('certificates.my-certificate-detail', compact('certificate', 'displayInfo'));
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'series_number' => 'required|string|max:50'
        ]);

        try {
            $certificate = Certificate::findBySeries($request->series_number);

            if (!$certificate) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Certificado no encontrado.'
                ]);
            }

            $isValid = $certificate->isValid();

            return response()->json([
                'success' => true,
                'valid' => $isValid,
                'certificate' => [
                    'series_number' => $certificate->series_number,
                    'holder_name' => $certificate->holder->full_name,
                    'course_name' => $certificate->course->name,
                    'issue_date' => $certificate->issue_date?->format('d/m/Y'),
                    'expiry_date' => $certificate->expiry_date?->format('d/m/Y'),
                    'status' => $certificate->status_label,
                    'is_expired' => $certificate->isExpired(),
                    'days_until_expiry' => $certificate->daysUntilExpiry(),
                ],
                'message' => $isValid
                    ? 'Certificado válido y activo.'
                    : ($certificate->isExpired() ? 'Certificado expirado.' : 'Certificado inactivo.')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el certificado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function preview(Request $request)
    {
        $config = CertificateTemplateConfig::getActiveConfig();

        $sampleData = [
            'student_name' => $request->get('preview_name', 'María Fernanda López García'),
            'course_name' => $request->get('preview_course', 'Curso de Desarrollo Web Avanzado'),
            'completion_date' => $request->get('preview_date', now()->format('d/m/Y')),
            'duration_hours' => $request->get('preview_hours', '40'),
            'identification' => 'CC 12.345.678',
            'series_number' => 'WEB-0001',
            'issue_date' => now()->format('d/m/Y'),
            'expiry_date' => now()->addYear()->format('d/m/Y'),
        ];

        return view('certificates.preview', compact('config', 'sampleData'));
    }

    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_certificates' => Certificate::count(),
                'active_certificates' => Certificate::active()->count(),
                'total_holders' => CertificateHolder::count(),
                'certificates_this_year' => Certificate::whereYear('created_at', now()->year)->count(),
                'expiring_soon' => Certificate::expiringSoon(30)->active()->count(),
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
