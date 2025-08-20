@extends('layouts.admin')

@section('title', 'Editar Perfil (Admin)')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i> Editar Perfil de Administrador</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="bi bi-person"></i> Nombre</label>
                                    <input type="text" name="first_name"
                                           value="{{ old('first_name', auth()->user()->first_name) }}"
                                           class="form-control @error('first_name') is-invalid @enderror"
                                           required
                                           pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+"
                                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '').toUpperCase();">
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="bi bi-person"></i> Apellido</label>
                                    <input type="text" name="last_name"
                                           value="{{ old('last_name', auth()->user()->last_name) }}"
                                           class="form-control @error('last_name') is-invalid @enderror"
                                           required
                                           pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+"
                                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '').toUpperCase();">
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                                <input type="email" name="email"
                                       value="{{ old('email', auth()->user()->email) }}"
                                       class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-telephone"></i> Teléfono</label>
                                <input type="text" name="phone"
                                       value="{{ old('phone', auth()->user()->phone) }}"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       pattern="[0-9]{7,10}" maxlength="10"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-house-door"></i> Dirección</label>
                                <input type="text" name="address"
                                       value="{{ old('address', auth()->user()->address) }}"
                                       class="form-control @error('address') is-invalid @enderror">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr>
                            <h6 class="fw-bold text-secondary"><i class="bi bi-shield-lock"></i> Cambiar Contraseña</h6>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Contraseña Actual</label>
                                    <input type="password" name="current_password"
                                           class="form-control @error('current_password') is-invalid @enderror">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nueva Contraseña</label>
                                    <input type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirmar Contraseña</label>
                                    <input type="password" name="password_confirmation" class="form-control">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
