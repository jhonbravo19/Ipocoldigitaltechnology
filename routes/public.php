<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController;

Route::get('/certificados/buscar', function () {
    return view('certificates.search');
})->name('certificates.form');

Route::post('/certificados/buscar', [CertificateController::class, 'publicSearch'])->name('certificates.search');

Route::get('/certificados/{certificate}', [CertificateController::class, 'results'])->name('certificates.results');

Route::get('/certificados/{certificate}/descargar', [CertificateController::class, 'downloadCertificate'])->name('certificates.downloadCertificate');

Route::get('/certificados/{certificate}/carnet', [CertificateController::class, 'downloadCard'])->name('certificates.downloadCard');
