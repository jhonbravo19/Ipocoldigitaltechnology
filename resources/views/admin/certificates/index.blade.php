@extends('layouts.admin')

@section('title', 'Certificados')

@section('content')
    <div class="container">
        <h1 class="mb-4">Certificados</h1>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o cédula"
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <select name="course_id" class="form-select">
                    <option value="">Todos los cursos</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Estado</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>

        <div class="mb-3">
            <a href="{{ route('admin.certificates.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Nuevo certificado
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Curso</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Archivos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certificates as $certificate)
                        <tr>
                            <td>
                                {{ $certificate->holder->full_name }}<br>
                            </td>
                            <td>{{ $certificate->holder->identification_number }}</td>
                            <td>{{ $certificate->course->name }}</td>
                            <td>{{ $certificate->issue_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $certificate->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($certificate->status) }}
                                </span>
                            </td>
                            <td>
                                @if($certificate->certificate_file_path)
                                    <a href="{{ Storage::url($certificate->certificate_file_path) }}?v={{ time() }}" target="_blank">📄 Certificado</a>
                                @endif
                                @if($certificate->card_file_path)
                                    <a href="{{ Storage::url($certificate->card_file_path) }}?v={{ time() }}" target="_blank">🪪 Carnet</a>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.certificates.show', $certificate) }}"
                                    class="btn btn-sm btn-info">Ver</a>
                                <a href="{{ route('admin.certificates.edit', $certificate) }}"
                                    class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.certificates.destroy', $certificate) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('¿Eliminar este certificado?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No se encontraron certificados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $certificates->withQueryString()->links() }}
        </div>
    </div>
@endsection