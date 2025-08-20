@extends('layouts.auth')

@section('title', 'Restablecer contraseña')

@section('content')
<div class="text-center mb-4">
    <h4 class="mb-3">🔄 Restablecer contraseña</h4>
    <p class="text-muted">Ingresa tu nueva contraseña para: <strong>{{ $email ?? request('email') }}</strong></p>
</div>

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email ?? request('email') }}">
    
    {{-- Mostrar el email de forma visual pero no editable --}}
    <div class="mb-3">
        <label class="form-label">Correo electrónico</label>
        <div class="form-control-plaintext bg-light border rounded px-3 py-2">
            <i class="fas fa-envelope text-muted me-2"></i>
            {{ $email ?? request('email') }}
        </div>
        @error('email')
            <div class="text-danger mt-1">
                <small>{{ $message }}</small>
            </div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Nueva contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password" type="password" name="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   required autocomplete="new-password" 
                   placeholder="Mínimo 8 caracteres">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                <i class="fas fa-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">La contraseña debe tener al menos 8 caracteres.</small>
    </div>

    <div class="mb-4">
        <label for="password-confirm" class="form-label">Confirmar contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password-confirm" type="password" name="password_confirmation" 
                   class="form-control" required autocomplete="new-password"
                   placeholder="Repite la contraseña">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password-confirm')">
                <i class="fas fa-eye" id="togglePasswordConfirmIcon"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-danger w-100 mb-3">
        <i class="fas fa-key me-2"></i>Actualizar contraseña
    </button>
    
    <div class="text-center">
        <a href="{{ route('login') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión
        </a>
        
        <div class="mt-3">
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-home me-1"></i>Volver al inicio
            </a>
        </div>
    </div>
</form>

{{-- Información de seguridad --}}
<div class="mt-4">
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-shield-alt me-2"></i>
        <strong>Información de seguridad:</strong><br>
        <small>Este enlace es válido por tiempo limitado y solo funciona una vez. Si tienes problemas, solicita un nuevo enlace de recuperación.</small>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('toggle' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1).replace('-', '') + 'Icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection