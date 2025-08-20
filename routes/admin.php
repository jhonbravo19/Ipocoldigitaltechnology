<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCertificateController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminTemplateController;
use App\Http\Controllers\Admin\ProfileController;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/statistics', [AdminDashboardController::class, 'statistics'])->name('statistics');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    Route::get('certificates/expiring-soon', [AdminCertificateController::class, 'expiringSoon'])
        ->name('certificates.expiring-soon');
    Route::get('certificates/export', [AdminCertificateController::class, 'export'])
        ->name('certificates.export');
    Route::post('certificates/{certificate}/toggle-status', [AdminCertificateController::class, 'toggleStatus'])
        ->name('certificates.toggleStatus');
    Route::post('certificates/{certificate}/regenerate', [AdminCertificateController::class, 'regeneratePDFs'])
        ->name('certificates.regenerate');
    
    Route::resource('certificates', AdminCertificateController::class);

    Route::resource('courses', AdminCourseController::class);

    Route::prefix('template')->name('template.')->group(function () {
        Route::get('/config', [AdminTemplateController::class, 'showConfig'])->name('config');
        Route::put('/config', [AdminTemplateController::class, 'updateConfig'])->name('config.update');
        Route::post('/reset', [AdminTemplateController::class, 'resetConfig'])->name('reset');

        Route::get('/preview', [AdminTemplateController::class, 'previewCertificate'])->name('preview');
        Route::post('/preview', [AdminTemplateController::class, 'previewCertificate'])->name('preview.post');

        Route::post('/generate-pdf', [AdminTemplateController::class, 'generatePDF'])->name('generate-pdf');
        Route::post('/delete-image', [AdminTemplateController::class, 'deleteImage'])->name('delete-image');
    });
});