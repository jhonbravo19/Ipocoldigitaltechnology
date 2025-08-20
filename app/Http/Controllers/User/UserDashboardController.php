<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Certificate;
use App\Models\CertificateHolder;
use App\Models\Order;
use App\Models\Product;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $holder = CertificateHolder::where('identification_number', $user->identification_number)->first();

        if (!$holder) {
            return view('user.dashboard')->with([
                'certificadosActivos' => 0,
                'certificadosVencidos' => 0,
                'ordenesRealizadas' => 0,
                'productosActivos' => 0,
                'ultimosCertificados' => collect(),
                'holder' => null,
            ]);
        }

        $certificadosActivos = Certificate::where('certificate_holder_id', $holder->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            })
            ->count();

        $certificadosVencidos = Certificate::where('certificate_holder_id', $holder->id)
            ->where(function ($q) {
                $q->where('status', 'inactive')
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'active')
                            ->whereNotNull('expiry_date')
                            ->where('expiry_date', '<', now());
                    });
            })
            ->count();


        $ordenesRealizadas = Order::where('buyer_id', $user->id)->count();

        $productosActivos = Product::where('seller_id', $user->id)
            ->where('status', 'active')
            ->count();

        $ultimosCertificados = Certificate::with('course')
            ->where('certificate_holder_id', $holder->id)
            ->latest()
            ->take(5)
            ->get();

        return view('user.dashboard', compact(
            'certificadosActivos',
            'certificadosVencidos',
            'ordenesRealizadas',
            'productosActivos',
            'ultimosCertificados',
            'holder'
        ));
    }
}
