@extends('layouts.admin')

@section('title', 'Configuración de Plantillas de Certificados')

@section('content')
    <div class="container py-4">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0">Configuración de Plantillas de Certificados</h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.template.preview') }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Vista Previa
                        </a>
                        <form method="POST" action="{{ route('admin.template.reset') }}">
                            @csrf
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('¿Estás seguro de que quieres resetear la configuración?')">
                                <i class="fas fa-undo"></i> Resetear
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.template.config.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Textos del Certificado</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Título del Certificado *</label>
                                    <input type="text" name="certificate_title" required
                                        value="{{ old('certificate_title', $config->certificate_title) }}"
                                        class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Texto de Introducción</label>
                                    <textarea name="intro_text" rows="3"
                                        class="form-control">{{ old('intro_text', $config->intro_text) }}</textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Texto Adicional</label>
                                    <textarea name="additional_text" rows="3"
                                        class="form-control">{{ old('additional_text', $config->additional_text) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Firmas</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre Firma 1</label>
                                    <input type="text" name="signature_1_name"
                                        value="{{ old('signature_1_name', $config->signature_1_name) }}"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cargo Firma 1</label>
                                    <input type="text" name="signature_1_position"
                                        value="{{ old('signature_1_position', $config->signature_1_position) }}"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nombre Firma 2</label>
                                    <input type="text" name="signature_2_name"
                                        value="{{ old('signature_2_name', $config->signature_2_name) }}"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cargo Firma 2</label>
                                    <input type="text" name="signature_2_position"
                                        value="{{ old('signature_2_position', $config->signature_2_position) }}"
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Imágenes del Certificado y Carnet</h5>
                            <div class="row g-3">

                                <div class="col-12">
                                    <h6 class="text-muted border-bottom pb-1 mb-3">Firmas</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Imagen Firma 1</label>
                                    @if($config->signature_1_image)
                                        <img src="{{ $config->signature_1_image_url }}" class="img-thumbnail mb-2"
                                            style="height:60px;">
                                        <button type="button" class="btn btn-link text-danger p-0"
                                            onclick="deleteImage('signature_1_image')">
                                            Eliminar
                                        </button>
                                    @endif
                                    <input type="file" name="signature_1_image" accept="image/*" class="form-control mt-2">
                                    <small class="text-muted">Máximo 2MB. JPG, PNG, GIF</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Imagen Firma 2</label>
                                    @if($config->signature_2_image)
                                        <img src="{{ $config->signature_2_image_url }}" class="img-thumbnail mb-2"
                                            style="height:60px;">
                                        <button type="button" class="btn btn-link text-danger p-0"
                                            onclick="deleteImage('signature_2_image')">
                                            Eliminar
                                        </button>
                                    @endif
                                    <input type="file" name="signature_2_image" accept="image/*" class="form-control mt-2">
                                    <small class="text-muted">Máximo 2MB. JPG, PNG, GIF</small>
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="text-muted border-bottom pb-1 mb-3">Fondos</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Imagen de Fondo del Carnet</label>
                                    @if($config->carnet_background_image)
                                                <div class="img-thumbnail mb-2" style="background: url('{{ $config->carnet_background_image_url }}') no-repeat center center; 
                                           background-size: cover; height: 80px;">
                                                </div>
                                                <button type="button" class="btn btn-link text-danger p-0"
                                                    onclick="deleteImage('carnet_background_image')">
                                                    Eliminar
                                                </button>
                                    @endif
                                    <input type="file" name="carnet_background_image" accept="image/*"
                                        class="form-control mt-2">
                                    <small class="text-muted">Máximo 5MB. JPG, PNG, GIF</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Imagen de Fondo del Certificado</label>
                                    @if($config->background_image)
                                                <div class="img-thumbnail mb-2" style="background: url('{{ $config->background_image_url }}') no-repeat center center; 
                                           background-size: cover; height: 80px;">
                                                </div>
                                                <button type="button" class="btn btn-link text-danger p-0"
                                                    onclick="deleteImage('background_image')">
                                                    Eliminar
                                                </button>
                                    @endif
                                    <input type="file" name="background_image" accept="image/*" class="form-control mt-2">
                                    <small class="text-muted">Máximo 5MB. JPG, PNG, GIF</small>
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="text-muted border-bottom pb-1 mb-3">Logo</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Logo de la Empresa</label>
                                    @if($config->company_logo)
                                        <img src="{{ $config->company_logo_url }}" class="img-thumbnail mb-2"
                                            style="height:80px;">
                                        <button type="button" class="btn btn-link text-danger p-0"
                                            onclick="deleteImage('company_logo')">
                                            Eliminar
                                        </button>
                                    @endif
                                    <input type="file" name="company_logo" accept="image/*" class="form-control mt-2">
                                    <small class="text-muted">Máximo 2MB. JPG, PNG, GIF</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4 py-2">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteImage(field) {
            if (confirm('¿Estás seguro de que quieres eliminar esta imagen?')) {
                fetch('{{ route("admin.template.delete-image") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ field: field })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert('Error al eliminar la imagen: ' + data.error);
                    })
                    .catch(() => alert('Error al eliminar la imagen'));
            }
        }
    </script>
@endsection