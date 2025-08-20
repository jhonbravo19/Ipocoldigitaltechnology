<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Models\CertificateHolder;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return redirect()->route('user.dashboard');
        }

        $stats = [
            'total_certificates' => Certificate::count(),
            'active_certificates' => Certificate::active()->count(),
            'total_holders' => CertificateHolder::count(),
            'total_courses' => Course::count(),
            'certificates_this_month' => Certificate::whereMonth('created_at', now()->month)->count(),
            'expired_certificates' => Certificate::expired()->count(),
            'expiring_soon' => Certificate::expiringSoon(30)->count(),
        ];

        $recentCertificates = Certificate::withRelations()
            ->latest()
            ->take(5)
            ->get();

        $courseStats = Course::withCount('certificates')
            ->orderByDesc('certificates_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentCertificates',
            'courseStats'
        ));
    }
    public function statistics(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return redirect()->route('user.dashboard');
        }

        $generalStats = [
            'total_certificates' => Certificate::count(),
            'active_certificates' => Certificate::active()->count(),
            'inactive_certificates' => Certificate::inactive()->count(),
            'total_holders' => CertificateHolder::count(),
            'total_courses' => Course::count(),
            'certificates_this_month' => Certificate::whereMonth('created_at', now()->month)->count(),
            'certificates_this_year' => Certificate::whereYear('created_at', now()->year)->count(),
            'expired_certificates' => Certificate::expired()->count(),
            'expiring_soon' => Certificate::expiringSoon(30)->count(),
        ];

        $monthlyStats = $this->getMonthlyCertificateStats();

        $courseStats = Course::withCount([
                'certificates',
                'certificates as active_certificates_count' => function ($query) {
                    $query->active();
                },
                'certificates as expired_certificates_count' => function ($query) {
                    $query->expired();
                }
            ])
            ->orderByDesc('certificates_count')
            ->get();

        $yearlyStats = $this->getYearlyCertificateStats();

        $statusStats = Certificate::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.statistics.statistics', compact(
            'generalStats',
            'monthlyStats',
            'courseStats',
            'yearlyStats',
            'statusStats'
        ));
    }

    private function getMonthlyCertificateStats()
    {
        $monthlyStats = collect();
        
        Carbon::setLocale('es');
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $count = Certificate::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            
            $monthlyStats->push([
                'month' => $date->format('Y-m'),
                'month_name' => $date->translatedFormat('F Y'),
                'month_short' => $date->translatedFormat('M Y'),
                'count' => $count,
                'start_date' => $startOfMonth->format('d/m/Y'),
                'end_date' => $endOfMonth->format('d/m/Y'),
                'year' => $date->year,
                'month_number' => $date->month
            ]);
        }
        
        return $monthlyStats;
    }
    private function getYearlyCertificateStats()
    {
        $yearlyStats = collect();
        
        for ($i = 0; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfDay();
            
            $count = Certificate::whereBetween('created_at', [$startOfYear, $endOfYear])->count();
            
            $yearlyStats->push([
                'year' => $year,
                'count' => $count,
                'start_date' => $startOfYear->format('d/m/Y'),
                'end_date' => $endOfYear->format('d/m/Y')
            ]);
        }
        
        return $yearlyStats;
    }
}