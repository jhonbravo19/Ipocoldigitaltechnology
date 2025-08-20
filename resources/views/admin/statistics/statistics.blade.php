@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="{{ asset('css/dashboardAdmin.css') }}">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1">Estadísticas Detalladas</h2>
            <p class="text-muted mb-0">Análisis completo de certificados emitidos</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i> Volver al Dashboard
        </a>
    </div>

    <div class="row g-4 mb-4">
        @php
            $generalCards = [
                ['icon' => 'bi-file-earmark-text', 'label' => 'Total Certificados', 'value' => $generalStats['total_certificates'], 'color' => 'primary'],
                ['icon' => 'bi-check-circle', 'label' => 'Activos', 'value' => $generalStats['active_certificates'], 'color' => 'success'],
                ['icon' => 'bi-x-circle', 'label' => 'Inactivos', 'value' => $generalStats['inactive_certificates'], 'color' => 'secondary'],
                ['icon' => 'bi-exclamation-triangle', 'label' => 'Vencidos', 'value' => $generalStats['expired_certificates'], 'color' => 'danger'],
                ['icon' => 'bi-people', 'label' => 'Portadores', 'value' => $generalStats['total_holders'], 'color' => 'info'],
                ['icon' => 'bi-book', 'label' => 'Cursos', 'value' => $generalStats['total_courses'], 'color' => 'dark'],
            ];
        @endphp

        @foreach ($generalCards as $card)
            <div class="col-md-2">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body">
                        <i class="bi {{ $card['icon'] }} text-{{ $card['color'] }} fs-1 mb-2"></i>
                        <h6 class="card-title text-muted">{{ $card['label'] }}</h6>
                        <h4 class="text-{{ $card['color'] }} fw-bold">{{ $card['value'] }}</h4>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-bar-chart text-primary me-2"></i>
                        Certificados por Mes (Últimos 12 meses)
                    </h5>
                    <div style="height: 400px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-pie-chart text-success me-2"></i>
                        Distribución por Estado
                    </h5>
                    <div style="height: 400px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-calendar3 text-info me-2"></i>
                        Detalle Mensual
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mes</th>
                                    <th>Período</th>
                                    <th class="text-center">Certificados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyStats as $stat)
                                    <tr>
                                        <td><strong>{{ $stat['month_name'] }}</strong></td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $stat['start_date'] }} - {{ $stat['end_date'] }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-pill">
                                                {{ $stat['count'] }}
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

        <div class="col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-book text-warning me-2"></i>
                        Certificados por Curso
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Curso</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Activos</th>
                                    <th class="text-center">Vencidos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($courseStats->take(10) as $course)
                                    <tr>
                                        <td>
                                            <strong>{{ Str::limit($course->name, 30) }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $course->certificates_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $course->active_certificates_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">{{ $course->expired_certificates_count }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-calendar-year text-danger me-2"></i>
                        Resumen Anual
                    </h5>
                    <div class="row">
                        @foreach ($yearlyStats as $year)
                            <div class="col-md-4">
                                <div class="text-center p-3 border rounded">
                                    <h3 class="text-primary fw-bold">{{ $year['year'] }}</h3>
                                    <p class="mb-1">
                                        <span class="badge bg-primary fs-6">{{ $year['count'] }} certificados</span>
                                    </p>
                                    <small class="text-muted">
                                        {{ $year['start_date'] }} - {{ $year['end_date'] }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyStats);
    const statusData = @json($statusStats);

    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month_short),
            datasets: [{
                label: 'Certificados',
                data: monthlyData.map(item => item.count),
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = Object.keys(statusData);
    const statusValues = Object.values(statusData);
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(status => {
                const labels = { 'active': 'Activos', 'inactive': 'Inactivos', 'replaced': 'Reemplazados' };
                return labels[status] || status;
            }),
            datasets: [{
                data: statusValues,
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection