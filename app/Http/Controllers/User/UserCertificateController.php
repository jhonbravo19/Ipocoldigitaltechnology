<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Certificate;
use App\Models\CertificateHolder;

class UserCertificateController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $holder = CertificateHolder::where('identification_number', $user->identification_number)->first();

        if (!$holder) {
            return view('user.certificates')->with(['certificates' => collect()]);
        }

        $certificates = Certificate::with('course')
            ->where('certificate_holder_id', $holder->id)
            ->orderByDesc('issue_date')
            ->paginate(10);

        return view('user.certificates', compact('certificates'));
    }

    public function show($seriesNumber)
    {
        $user = Auth::user();

        $holder = CertificateHolder::where('identification_number', $user->identification_number)->first();

        if (!$holder) {
            abort(404, 'Certificado no encontrado');
        }

        $certificate = Certificate::with('course')
            ->where('certificate_holder_id', $holder->id)
            ->where('series_number', $seriesNumber)
            ->firstOrFail();

        return view('user.certificate-detail', compact('certificate'));
    }
}
