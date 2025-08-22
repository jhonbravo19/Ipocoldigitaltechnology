@extends('layouts.admin')

@section('title', 'Ver Certificado')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Certificado #{{ $certificate->series_number }}</h3>
                        <div>
                            <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <a href="{{ route('admin.certificates.edit', $certificate) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            @if($certificate->status === 'active')
                                <form action="{{ route('admin.certificates.toggleStatus', $certificate) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('¿Desactivar certificado?')">
                                        <i class="fas fa-ban"></i> Desactivar
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.certificates.toggleStatus', $certificate) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Activar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Información del Titular</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Nombres:</th>
                                        <td>{{ $certificate->holder->first_names }}</td>
                                    </tr>
                                    <tr>
                                        <th>Apellidos:</th>
                                        <td>{{ $certificate->holder->last_names }}</td>
                                    </tr>
                                    <tr>
                                        <th>Identificación:</th>
                                        <td>{{ $certificate->holder->identification_type }}
                                            {{ $certificate->holder->identification_number }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Lugar de expedición:</th>
                                        <td>{{ $certificate->holder->identification_place }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo de sangre:</th>
                                        <td>{{ $certificate->holder->blood_type }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tiene Licencia:</th>
                                        <td>
                                            @if($certificate->holder->has_drivers_license === 'SI')
                                                <span class="badge bg-success">SI</span>
                                            @else
                                                <span class="badge bg-secondary">NO</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Categoría Licencia:</th>
                                        <td>
                                            {{ $certificate->holder->drivers_license_category ?? '—' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h5>Información del Certificado</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Número de serie:</th>
                                        <td><strong>{{ $certificate->series_number }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Curso:</th>
                                        <td>{{ $certificate->course->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Duración:</th>
                                        <td>{{ $certificate->course->duration_hours }} horas</td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de emisión:</th>
                                        <td>{{ $certificate->issue_date->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de vencimiento:</th>
                                        <td>{{ $certificate->expiry_date->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Estado:</th>
                                        <td>
                                            @if($certificate->status === 'active')
                                                <span class="badge bg-success text-white me-2">Activo</span>
                                            @else
                                                <span class="badge bg-secondary text-white me-2">Inactivo</span>
                                            @endif

                                            @if($certificate->isExpired())
                                                <span class="badge bg-danger text-white">
                                                    Vencido {{ $certificate->expiry_date->diffForHumans() }}
                                                </span>
                                            @elseif($certificate->expiresSoon(30))
                                                <span class="badge bg-warning text-dark">
                                                    Vence en {{ $certificate->daysUntilExpiry() }} días
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Emitido por:</th>
                                        <td>{{ $certificate->issuer->name ?? 'Usuario eliminado' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de creación:</th>
                                        <td>{{ $certificate->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($certificate->holder->photo_path)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5>Fotografía</h5>
                                    <img src="{{ asset('storage/' . $certificate->holder->photo_path) }}" alt="Foto del titular"
                                        class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            </div>
                        @endif

                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Archivos PDF</h5>
                                <div class="btn-group" role="group">
                                    @if($certificate->certificate_file_path)
                                        <a href="{{ asset($certificate->certificate_file_path) }}?v={{ time() }}"
                                            target="_blank" class="btn btn-primary">
                                            <i class="fas fa-file-pdf"></i> Ver Certificado
                                        </a>
                                        <a href="{{ asset('storage/' . $certificate->certificate_file_path) }}?v={{ time() }}"
                                            download class="btn btn-outline-primary">
                                            <i class="fas fa-download"></i> Descargar Certificado
                                        </a>
                                    @endif

                                    @if($certificate->card_file_path)
                                        <a href="{{ asset($certificate->card_file_path) }}?v={{ time() }}"
                                            target="_blank" class="btn btn-info">
                                            <i class="fas fa-id-card"></i> Ver Carnet
                                        </a>
                                        <a href="{{ asset('storage/' . $certificate->card_file_path) }}?v={{ time() }}" download
                                            class="btn btn-outline-info">
                                            <i class="fas fa-download"></i> Descargar Carnet
                                        </a>
                                    @endif

                                    @if($certificate->acta_file_path)
                                        <a href="{{ asset($certificate->acta_file_path) }}?v={{ time() }}"
                                            target="_blank" class="btn btn-success">
                                            <i class="fas fa-file-alt"></i> Ver Acta
                                        </a>
                                        <a href="{{ asset($certificate->acta_file_path) }}?v={{ time() }}" download
                                            class="btn btn-outline-success">
                                            <i class="fas fa-download"></i> Descargar Acta
                                        </a>
                                    @endif

                                    @if($certificate->paquete_file_path)
                                        <a href="{{ asset('storage/' . $certificate->paquete_file_path) }}?v={{ time() }}"
                                            target="_blank" class="btn btn-dark">
                                            <i class="fas fa-layer-group"></i> Ver Paquete Completo
                                        </a>
                                        <a href="{{ asset('storage/' . $certificate->paquete_file_path) }}?v={{ time() }}"
                                            download class="btn btn-outline-dark">
                                            <i class="fas fa-download"></i> Descargar Paquete Completo
                                        </a>
                                    @endif

                                    <form action="{{ route('admin.certificates.regenerate', $certificate) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning"
                                            onclick="return confirm('¿Regenerar los PDFs?')">
                                            <i class="fas fa-sync"></i> Regenerar PDFs
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @if(!$certificate->certificate_file_path || !$certificate->card_file_path)
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                Los archivos PDF no han sido generados o están faltando. Use el botón "Regenerar PDFs" para
                                crearlos.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection