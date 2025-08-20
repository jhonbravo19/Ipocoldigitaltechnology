@extends('layouts.auth')

@section('title', 'Registro de Usuario')

@section('content')
    <h3 class="mb-4 text-center">{{ __('Registro de Usuario') }}</h3>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">{{ __('Nombres') }} *</label>
                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror"
                    name="first_name" value="{{ old('first_name') }}" pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+"
                    title="Solo letras y espacios" oninput="this.value=this.value.toUpperCase()" maxlength="100" required>
                @error('first_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label">{{ __('Apellidos') }} *</label>
                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror"
                    name="last_name" value="{{ old('last_name') }}" pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+"
                    title="Solo letras y espacios" oninput="this.value=this.value.toUpperCase()" maxlength="100" required>
                @error('last_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Correo Electrónico') }} *</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                value="{{ old('email') }}" required>
            @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="identification_type" class="form-label">{{ __('Tipo de ID') }} *</label>
                <select id="identification_type" class="form-select @error('identification_type') is-invalid @enderror"
                    name="identification_type" required>
                    <option value="">Seleccionar...</option>
                    <option value="CC" {{ old('identification_type') == 'CC' ? 'selected' : '' }}>Cédula de Ciudadanía
                    </option>
                    <option value="CE" {{ old('identification_type') == 'CE' ? 'selected' : '' }}>Cédula de Extranjería
                    </option>
                    <option value="PA" {{ old('identification_type') == 'PA' ? 'selected' : '' }}>Pasaporte</option>
                </select>
                @error('identification_type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-8">
                <label for="identification_number" class="form-label">{{ __('Número de Identificación') }} *</label>
                <input id="identification_number" type="text"
                    class="form-control @error('identification_number') is-invalid @enderror" name="identification_number"
                    value="{{ old('identification_number') }}" pattern="[0-9]+" maxlength="50" required>
                @error('identification_number')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="phone" class="form-label">{{ __('Teléfono') }} (opcional)</label>
                <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone"
                    value="{{ old('phone') }}" maxlength="20">
                @error('phone')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="address" class="form-label">{{ __('Dirección') }} (opcional)</label>
                <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address"
                    value="{{ old('address') }}" maxlength="500">
                @error('address')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="password" class="form-label">{{ __('Contraseña') }} *</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                    name="password" required minlength="8">
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="password-confirm" class="form-label">{{ __('Confirmar Contraseña') }} *</label>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required
                    minlength="8">
            </div>
        </div>

        <div class="alert alert-info small">
            <i class="fas fa-info-circle"></i> Los campos marcados con (*) son obligatorios.
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> {{ __('Registrarse') }}
            </button>
            <a class="btn btn-link text-center" href="{{ route('login') }}">
                {{ __('¿Ya tienes cuenta? Inicia sesión') }}
            </a>
        </div>

        <div class="mt-3 text-center">
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                {{ __('← Volver al Inicio') }}
            </a>
        </div>
    </form>
@endsection