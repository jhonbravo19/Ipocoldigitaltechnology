@extends('layouts.user')

@section('title', 'Mis Certificados')

@section('content')
    <div class="container">
        <h1 class="mb-4">Mis Certificados</h1>

        @if($certificates->isEmpty())
            <div class="alert alert-info">
                No tienes certificados registrados en este momento.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Fecha de Aprobación</th>
                            <th>Válido hasta</th>
                            <th>Estado</th>
                            <th>Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($certificates as $certificate)
                            <tr>
                                <td>{{ $certificate->course->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($certificate->issue_date)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($certificate->expiry_date)->format('d/m/Y') }}</td>
                                <td>
                                    @if($certificate->status === 'active')
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Vencido</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('user.certificates.show', $certificate->series_number) }}"
                                        class="btn btn-primary btn-sm">
                                        Ver Detalle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>
@endsection