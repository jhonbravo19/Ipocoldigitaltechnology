@extends('layouts.user')

@section('title', 'Detalle del Certificado')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm border">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-3">Detalle del Certificado</h5>

                {{-- Información del titular --}}
                <div class="mb-4">
                    <p class="mb-1"><strong>Nombre:</strong> {{ strtoupper($certificate->holder->full_name) }}</p>
                    <p class="mb-1"><strong>Identificación:</strong>
                        {{ $certificate->holder->identification_type }}
                        {{ $certificate->holder->identification_number }}
                    </p>
                    <p class="mb-1"><strong>Curso:</strong> {{ $certificate->course->name }}</p>
                    <p class="mb-1"><strong>Horas:</strong> {{ $certificate->course->duration_hours }}</p>
                    <p class="mb-1"><strong>Expedición:</strong> {{ $certificate->issue_date->format('d/m/Y') }}</p>
                    <p class="mb-1"><strong>Vencimiento:</strong> {{ $certificate->expiry_date->format('d/m/Y') }}</p>
                    <p class="mb-1">
                        <strong>Estado:</strong>
                        @php
                            $isExpired = $certificate->expiry_date < now() || $certificate->status !== 'active';
                        @endphp
                        @if(!$isExpired)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Vencido</span>
                        @endif
                    </p>
                </div>

                <hr>

                {{-- Condicional para mostrar preview --}}
                @if(!$isExpired)
                    <div class="text-center">
                        @if($certificate->certificate_file_path && Storage::disk('public')->exists($certificate->certificate_file_path))
                            <div class="mb-5">
                                <h6 class="fw-bold mb-3 text-secondary">Certificado</h6>
                                <iframe
                                    src="{{ Storage::url($certificate->certificate_file_path) }}?v={{ time() }}#toolbar=0"
                                    style="width:100%; max-width:800px; height:500px; border:1px solid #ccc; border-radius:6px;"
                                    frameborder="0">
                                </iframe>
                            </div>
                        @endif
                        @if($certificate->card_file_path && Storage::disk('public')->exists($certificate->card_file_path))
                            <div>
                                <h6 class="fw-bold mb-3 text-secondary">Carnet</h6>
                                <iframe src="{{ Storage::url($certificate->card_file_path) }}?v={{ time() }}#toolbar=0&navpanes=0&scrollbar=0"
                                    style="width:100%; max-width:600px; height:350px; border:1px solid #ccc; border-radius:6px;"
                                    frameborder="0">
                                </iframe>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning text-center mt-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Este certificado está vencido. La vista previa no está disponible.
                    </div>
                @endif

                <div class="text-center mt-4">
                    <a href="{{ route('user.certificates') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Volver a mis certificados
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection