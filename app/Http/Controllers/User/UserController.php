<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $user = Auth::user();

        $certificates = $user->certificates();

        $stats = [
            'total_certificates' => $certificates->count(),
            'active_certificates' => $certificates->active()->count(),
            'expired_certificates' => $certificates->expired()->count(),
            'expiring_soon' => $certificates->expiringSoon(30)->count(),
        ];

        $recentCertificates = $certificates->latest('issue_date')->take(5)->get();

        return view('user.dashboard', compact('user', 'stats', 'recentCertificates'));
    }

    public function myCertificates()
    {
        $user = Auth::user();
        $certificates = $user->certificates()->paginate(10);

        return view('user.certificates', compact('certificates'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('user.settings', compact('user'));
    }
}
