@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')
<div class="text-center mb-4">
    <h4 class="mb-3">🔐 Iniciar sesión</h4>
    <p class="text-muted">Accede a tu cuenta</p>
</div>

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" 
                   class="form-control @error('email') is-invalid @enderror" 
                   required autofocus placeholder="tu@email.com">
        </div>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input id="password" type="password" name="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   required placeholder="Tu contraseña">
            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                <i class="fas fa-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 form-check">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">
            Recordarme
        </label>
    </div>

    <button type="submit" class="btn btn-danger w-100 mb-3">
        <i class="fas fa-sign-in-alt me-2"></i>Iniciar sesión
    </button>
    
    <div class="text-center">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-decoration-none">
                <i class="fas fa-key me-1"></i>¿Olvidaste tu contraseña?
            </a>
        @endif
        
        @if (Route::has('register'))
            <div class="mt-2">
                ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-decoration-none">
                    <i class="fas fa-user-plus me-1"></i>Regístrate aquí
                </a>
            </div>
        @endif
        
        <div class="mt-3">
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-home me-1"></i>Volver al inicio
            </a>
        </div>
    </div>
</form>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('toggle' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1) + 'Icon');
    
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