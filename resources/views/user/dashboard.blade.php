@extends('layouts.user')

@section('title', 'Mi Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboardUser.css') }}">
@endpush

@section('content')
    <div class="user-dashboard-container">
        <div class="page-header mb-4">
            <h1 class="page-title">
                <i class="bi bi-person-circle me-3"></i>
                Mi Dashboard
            </h1>
            <p class="page-subtitle">
                Bienvenido {{ auth()->user()->name }}, aquí puedes gestionar tus certificados y compras
            </p>
        </div>

        <div class="row g-4 mb-5">
            @php
                $userStats = [
                    ['icon' => 'bi-award', 'label' => 'Certificados Activos', 'value' => $certificadosActivos, 'gradient' => 'linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%)', 'description' => 'Certificados válidos'],
                    ['icon' => 'bi-clock-history', 'label' => 'Certificados Vencidos', 'value' => $certificadosVencidos, 'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', 'description' => 'Requieren renovación'],
                    ['icon' => 'bi-bag-check', 'label' => 'Órdenes Realizadas', 'value' => $ordenesRealizadas, 'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', 'description' => 'Compras completadas'],
                    ['icon' => 'bi-shop', 'label' => 'Productos Activos', 'value' => $productosActivos, 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 'description' => 'En venta actualmente'],
                ];
            @endphp

            @foreach ($userStats as $stat)
                <div class="col-xl-3 col-md-6">
                    <div class="user-stat-card shadow-lg border-0" style="background: {{ $stat['gradient'] }}; color: white;">
                        <div class="stat-card-body text-center position-relative overflow-hidden">
                            <div class="stat-icon-bg"><i class="bi {{ $stat['icon'] }}"></i></div>
                            <div class="position-relative z-index-2">
                                <div class="stat-icon mb-3"><i class="bi {{ $stat['icon'] }}"></i></div>
                                <h6 class="stat-label text-white-75">{{ $stat['label'] }}</h6>
                                <div class="stat-value counter" data-count="{{ $stat['value'] }}">
                                    {{ $stat['value'] }}
                                </div>
                                <p class="stat-description mb-0">{{ $stat['description'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="quick-actions mb-5">
            <div class="section-title mb-4">
                <h5><i class="bi bi-lightning me-2"></i>Acciones Rápidas</h5>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="{{ route('user.certificates') }}" class="btn btn-primary w-100 py-3 fs-5 shadow-sm">
                        <i class="bi bi-award me-2"></i> Ver Certificados
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('user.shop') }}" class="btn btn-success w-100 py-3 fs-5 shadow-sm">
                        <i class="bi bi-cart-plus me-2"></i> Ir a la Tienda
                    </a>
                </div>
            </div>
        </div>


        <div class="row g-4">
            <div class="col-lg-12">
                <div class="content-card shadow-lg border-0">
                    <div
                        class="content-card-header d-flex justify-content-between align-items-center p-4 bg-light rounded-top">
                        <div>
                            <h5 class="card-title mb-1 text-primary">
                                <i class="bi bi-award-fill me-2"></i> Mis Últimos Certificados
                            </h5>
                            <p class="card-subtitle text-muted mb-0">
                                Certificados obtenidos recientemente
                            </p>
                        </div>
                        @if($ultimosCertificados->count() > 0)
                            <a href="{{ route('user.certificates') }}" class="btn btn-primary btn-sm shadow-sm">
                                Ver todos <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @endif
                    </div>

                    <div class="content-card-body p-4">
                        @if($ultimosCertificados->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle modern-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="bi bi-book me-2 text-secondary"></i>Curso</th>
                                            <th><i class="bi bi-calendar-event me-2 text-secondary"></i>Fecha de Emisión</th>
                                            <th><i class="bi bi-flag me-2 text-secondary"></i>Estado</th>
                                            <th><i class="bi bi-gear me-2 text-secondary"></i>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ultimosCertificados as $cert)
                                            @php
                                                $isExpired = $cert->expiry_date && $cert->expiry_date < now();
                                                $statusClass = $isExpired ? 'danger' : ($cert->status === 'active' ? 'success' : 'secondary');
                                                $statusIcon = $isExpired ? 'x-circle' : ($cert->status === 'active' ? 'check-circle' : 'clock');
                                                $statusText = $isExpired ? 'Vencido' : ucfirst($cert->status);
                                            @endphp
                                            <tr>
                                                <td class="fw-bold">{{ $cert->course->name ?? 'N/A' }}</td>
                                                <td>{{ $cert->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $statusClass }} px-3 py-2 shadow-sm">
                                                        <i class="bi bi-{{ $statusIcon }} me-1"></i>{{ $statusText }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('user.certificates.show', $cert->series_number) }}"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-eye me-1"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state text-center py-5">
                                <i class="bi bi-award empty-icon text-muted" style="font-size:3rem;"></i>
                                <h6 class="mt-3">No tienes certificados aún</h6>
                                <p class="text-muted">Los certificados que obtengas aparecerán aquí.</p>
                                @if(!$holder)
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No se encontró un perfil asociado a tu número de identificación.
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

@endsection

    @push('scripts')
        <script src="{{ asset('js/dashboardUser.js') }}"></script>
    @endpush