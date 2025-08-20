@extends('layouts.admin')

@section('content')

    <link rel="stylesheet" href="{{ asset('css/dashboardAdmin.css') }}">

    <div class="container py-4">
        <h2 class="mb-4 fw-bold text-primary">Panel de Administración</h2>

        <div class="row g-4 mb-4">
            @php
                $cards = [
                    ['icon' => 'bi-file-earmark-text', 'label' => 'Certificados Totales', 'value' => $stats['total_certificates'], 'color' => 'primary', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
                    ['icon' => 'bi-check-circle', 'label' => 'Activos', 'value' => $stats['active_certificates'], 'color' => 'success', 'gradient' => 'linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%)'],
                    ['icon' => 'bi-hourglass-split', 'label' => 'Por Vencer (30 días)', 'value' => $stats['expiring_soon'], 'color' => 'warning', 'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
                    ['icon' => 'bi-x-circle', 'label' => 'Vencidos', 'value' => $stats['expired_certificates'], 'color' => 'danger', 'gradient' => 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)'],
                    ['icon' => 'bi-book', 'label' => 'Cursos', 'value' => $stats['total_courses'], 'color' => 'secondary', 'gradient' => 'linear-gradient(135deg, #bdc3c7 0%, #2c3e50 100%)'],
                    ['icon' => 'bi-calendar-event', 'label' => 'Este Mes', 'value' => $stats['certificates_this_month'], 'color' => 'dark', 'gradient' => 'linear-gradient(135deg, #2980b9 0%, #6dd5fa 100%)'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="col-md-3">
                    <div class="card shadow-lg border-0 stat-card" style="background: {{ $card['gradient'] }}; color: white;">
                        <div class="card-body text-center position-relative overflow-hidden">
                            <div class="stat-icon-bg">
                                <i class="bi {{ $card['icon'] }}"></i>
                            </div>
                            <div class="position-relative z-index-2">
                                <div class="mb-2">
                                    <i class="bi {{ $card['icon'] }} fs-1"></i>
                                </div>
                                <h6 class="card-title text-white-50">{{ $card['label'] }}</h6>
                                <p class="card-text fs-2 fw-bold mb-0 counter" data-count="{{ $card['value'] }}">
                                    {{ $card['value'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-4 d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.certificates.create') }}" class="btn btn-success btn-modern">
                <i class="bi bi-plus-lg me-2"></i> Nuevo certificado
            </a>
            <a href="{{ route('admin.certificates.index') }}" class="btn btn-primary btn-modern">
                <i class="bi bi-table me-2"></i> Ver todos
            </a>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-info btn-modern">
                <i class="bi bi-book me-2"></i> Cursos
            </a>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        @stack('scripts')

        <div class="card border-0 shadow-lg">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        Últimos Certificados Emitidos
                    </h5>
                    <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-primary btn-sm">
                        Ver todos <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle modern-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Curso</th>
                                <th>Fecha de emisión</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentCertificates as $cert)
                                <tr class="table-row-animated">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span>{{ $cert->holder->first_names ?? '' }}
                                                {{ $cert->holder->last_names ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td><code>{{ $cert->holder->identification_number ?? 'N/A' }}</code></td>
                                    <td>
                                        <span class="course-badge">{{ Str::limit($cert->course->name ?? 'N/A', 30) }}</span>
                                    </td>
                                    <td>{{ $cert->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $cert->status === 'active' ? 'success' : 'secondary' }} rounded-pill">
                                            <i
                                                class="bi bi-{{ $cert->status === 'active' ? 'check-circle' : 'clock' }} me-1"></i>
                                            {{ ucfirst($cert->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="{{ asset('js/admin.js') }}"></script>

    @stack('scripts')

    @yield('scripts')

@endsection