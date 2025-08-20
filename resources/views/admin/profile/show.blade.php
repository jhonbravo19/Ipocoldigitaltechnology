@extends('layouts.admin')

@section('title', 'Mi Perfil (Admin)')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i> Perfil de Administrador</h5>
                        <a href="{{ route('admin.profile.edit') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil-square me-1"></i> Editar
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="rounded-circle bg-dark text-white d-inline-flex align-items-center justify-content-center"
                                style="width: 90px; height: 90px; font-size: 32px; font-weight: bold;">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                            </div>
                            <h4 class="mt-3 mb-0">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h4>
                            <span class="text-muted">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>

                        <hr>

                        <div class="profile-details">
                            <p><i class="bi bi-envelope me-2 text-dark"></i>
                                <strong>Email:</strong> {{ auth()->user()->email }}
                            </p>
                            <p><i class="bi bi-card-text me-2 text-dark"></i>
                                <strong>Identificación:</strong> 
                                {{ auth()->user()->identification_type ?? 'N/A' }} 
                                {{ auth()->user()->identification_number ?? '' }}
                            </p>
                            <p><i class="bi bi-telephone me-2 text-dark"></i>
                                <strong>Teléfono:</strong> {{ auth()->user()->phone ?? 'No registrado' }}
                            </p>
                            <p><i class="bi bi-house-door me-2 text-dark"></i>
                                <strong>Dirección:</strong> {{ auth()->user()->address ?? 'No registrada' }}
                            </p>
                            <p><i class="bi bi-calendar-check me-2 text-dark"></i>
                                <strong>Registrado:</strong> {{ auth()->user()->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
