@extends('layouts.app')

@section('title', 'Buscar Certificado')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/certificates-search.css') }}">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">🔍 Buscar Certificado</h2>
                            <p class="text-muted">
                                Ingrese su <strong>número de cédula</strong>
                            </p>
                        </div>

                        <form action="{{ route('certificates.search') }}" method="POST" id="certificateSearchForm">
                            @csrf

                            <div class="mb-4">
                                <label for="query" class="form-label fw-semibold">Número de Cédula</label>
                                <input type="text" name="query" id="query" class="form-control form-control-lg"
                                    placeholder="Ej: 1234567890" value="{{ old('query') }}" required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg search-btn">
                                <i class="bi bi-search me-2"></i> Buscar Certificado
                            </button>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                                {{ __('← Volver al Inicio') }}
                            </a>
                            </a>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="text-muted mb-0">
                                ¿Tienes cuenta?
                                <a href="{{ route('login') }}" class="fw-bold text-decoration-none link-primary">
                                    Ver mis certificados
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/certificates-search.js') }}"></script>
@endsection