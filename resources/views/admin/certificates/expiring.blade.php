@extends('layouts.admin')

@section('title', 'Certificados por Vencer')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4 fw-bold text-warning">Certificados por Vencer (30 días)</h2>

        @if($certificates->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No hay certificados próximos a vencer.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Curso</th>
                            <th>Fecha de Emisión</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($certificates as $cert)
                            <tr>
                                <td>{{ $cert->holder->first_names }} {{ $cert->holder->last_names }}</td>
                                <td>{{ $cert->holder->identification_number }}</td>
                                <td>{{ $cert->course->name }}</td>
                                <td>{{ $cert->issue_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        {{ $cert->expiry_date->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.certificates.show', $cert) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                    <a href="{{ route('admin.certificates.edit', $cert) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection