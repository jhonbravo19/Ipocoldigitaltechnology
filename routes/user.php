<?php

use App\Http\Controllers\User\UserCertificateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ConfigController;

Route::prefix('usuario')->name('user.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/certificados', [UserCertificateController::class, 'index'])->name('certificates');
    Route::get('/certificados/{seriesNumber}', [UserCertificateController::class, 'show'])->name('certificates.show');
    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/perfil/editar', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/tienda', [ShopController::class, 'index'])->name('shop');
});
