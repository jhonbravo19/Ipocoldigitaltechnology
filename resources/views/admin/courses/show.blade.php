@extends('layouts.admin')

@section('title', 'Detalle del Curso')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-info">{{ $course->name }}</h2>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <h5 class="card-title">Información del Curso</h5>
                <p><strong>Duración:</strong> {{ $course->duration_hours }} horas</p>
                <p><strong>Descripción:</strong> {{ $course->description ?? 'No disponible' }}</p>
                <p><strong>Total Certificados:</strong> {{ $course->certificates->count() }}</p>
            </div>
        </div>

        <div class="card shadow-lg">
            <div class="card-body">
                <h5 class="card-title">Certificados Asociados</h5>
                @if($course->certificates->isEmpty())
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No hay certificados emitidos para este curso.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Número de Serie</th>
                                    <th>Alumno</th>
                                    <th>Fecha Emisión</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($course->certificates as $cert)
                                    <tr>
                                        <td>{{ $cert->series_number }}</td>
                                        <td>{{ $cert->holder->first_names }} {{ $cert->holder->last_names }}</td>
                                        <td>{{ $cert->issue_date->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $cert->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($cert->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.certificates.show', $cert) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection