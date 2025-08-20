@extends('layouts.app')

@section('title', 'Resultados de Búsqueda')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm border">
            <div class="card-body">
                <h5 class="fw-bold mb-3 text-primary">Información del Usuario</h5>
                @if ($certificates->isNotEmpty())
                    @php
                        $holder = $certificates->first()->holder;
                    @endphp
                    <p class="mb-1"><strong>Nombre:</strong> {{ strtoupper($holder->full_name) }}</p>
                    <p class="mb-1"><strong>Tipo de Identificación:</strong> {{ $holder->identification_type }}</p>
                    <p class="mb-1"><strong>Número de Identificación:</strong> {{ $holder->identification_number }}</p>
                @endif

                <hr>

                <h6 class="fw-bold text-secondary">Certificados ({{ $certificates->count() }})</h6>
                <div class="mt-3">
                    @foreach($certificates as $certificate)
                        @php
                            $displayInfo = $certificate->getDisplayInfo();
                            $statusText = $certificate->isExpired() ? 'Vencido' : $displayInfo['status'];
                            $statusClass = $certificate->isExpired() ? 'bg-danger' : 'bg-success';
                        @endphp

                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0">
                                    {{ strtoupper($displayInfo['course_name']) }}
                                </h6>
                                <span class="badge {{ $statusClass }} text-white">
                                    {{ $statusText }}
                                </span>
                            </div>

                            <p class="mb-1"><strong>Serie:</strong> {{ $certificate->series_number }}</p>
                            <p class="mb-1"><strong>Horas:</strong> {{ $displayInfo['course_hours'] }}</p>
                            <p class="mb-1"><strong>Expedición:</strong> {{ $displayInfo['issue_date'] }}</p>
                            <p class="mb-1"><strong>Vencimiento:</strong> {{ $displayInfo['expiry_date'] }}</p>

                            <div class="d-flex gap-3 mt-2">
                                @if($certificate->certificate_file_path && Storage::disk('public')->exists($certificate->certificate_file_path))
                                    <button type="button" class="btn btn-link text-primary p-0" data-bs-toggle="modal"
                                        data-bs-target="#pdfModal"
                                        data-url="{{ Storage::url($certificate->certificate_file_path) }}?v={{ time() }}">
                                        <i class="fas fa-file-pdf fa-lg me-1"></i> Ver Certificado
                                    </button>
                                @endif
                                @if($certificate->card_file_path && Storage::disk('public')->exists($certificate->card_file_path))
                                    <button type="button" class="btn btn-link text-warning p-0" data-bs-toggle="modal"
                                        data-bs-target="#pdfModal" data-url="{{ Storage::url($certificate->card_file_path) }}?v={{ time() }}">
                                        <i class="fas fa-id-card fa-lg me-1"></i> Ver Carnet
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('certificates.form') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Nueva Búsqueda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vista Previa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    <iframe id="pdfViewer" src="" width="100%" height="100%" style="border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pdfModal = document.getElementById('pdfModal');
            const pdfViewer = document.getElementById('pdfViewer');

            pdfModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const url = button.getAttribute('data-url');
                pdfViewer.src = url + '#toolbar=0';
            });

            pdfModal.addEventListener('hidden.bs.modal', function () {
                pdfViewer.src = "";
            });
        });
    </script>
@endpush