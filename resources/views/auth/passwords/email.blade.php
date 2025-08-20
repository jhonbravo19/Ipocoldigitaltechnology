@extends('layouts.auth')

@section('title', 'Recuperar contraseña')

@section('content')
<div class="text-center mb-4">
    <h4 class="mb-3">🔑 Recuperar contraseña</h4>
    <p class="text-muted">Te enviaremos un enlace para restablecer tu contraseña</p>
</div>

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
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

    <button type="submit" class="btn btn-danger w-100 mb-3">
        <i class="fas fa-paper-plane me-2"></i>Enviar enlace de recuperación
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

{{-- Información adicional --}}
<div class="mt-4">
    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>¿No recibes el correo?</strong><br>
        <small>Revisa tu carpeta de spam o correo no deseado. El enlace expirará en 60 minutos.</small>
    </div>
</div>
@endsection
