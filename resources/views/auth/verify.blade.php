@extends('layouts.auth')

@section('title', 'Verificación de Correo')

@section('content')
    <h3 class="mb-4 text-center">{{ __('Verifica tu Correo Electrónico') }}</h3>

    @if (session('resent'))
        <div class="alert alert-success text-center" role="alert">
            {{ __('Un nuevo enlace de verificación ha sido enviado a tu correo.') }}
        </div>
    @endif

    <p class="text-center mb-4">
        {{ __('Antes de continuar, por favor revisa tu correo electrónico para el enlace de verificación.') }} <br>
        {{ __('Si no recibiste el correo, puedes solicitar uno nuevo:') }}
    </p>

    <form method="POST" action="{{ route('verification.resend') }}" class="d-grid">
        @csrf
        <button type="submit" class="btn btn-primary">
            {{ __('Reenviar enlace de verificación') }}
        </button>
    </form>

    <div class="mt-3 text-center">
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            {{ __('Cerrar sesión') }}
        </a>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
@endsection