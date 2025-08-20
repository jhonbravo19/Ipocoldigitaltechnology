@extends('layouts.admin')

@section('title', 'Editar Certificado')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Editar Certificado #{{ $certificate->series_number }}</h1>
            <a href="{{ route('admin.certificates.show', $certificate) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Ups!</strong> Corrige los siguientes errores:
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Certificado</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.certificates.update', $certificate) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="course_id" class="form-label">Curso *</label>
                            <select name="course_id" id="course_id"
                                class="form-select @error('course_id') is-invalid @enderror" required>
                                <option value="">Seleccione un curso</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ $certificate->course_id == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="issue_date" class="form-label">Fecha de emisión *</label>
                            <input type="date" name="issue_date" id="issue_date"
                                class="form-control @error('issue_date') is-invalid @enderror"
                                value="{{ $certificate->issue_date->format('Y-m-d') }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>
                    <h5 class="mt-4">Datos del Titular</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_names" class="form-label">Nombres *</label>
                            <input type="text" name="first_names" id="first_names"
                                class="form-control @error('first_names') is-invalid @enderror"
                                value="{{ $certificate->holder->first_names }}" pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+"
                                title="Solo se permiten letras y espacios"
                                oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\u00f1\u00d1\s]/g, '')" required>
                            @error('first_names')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_names" class="form-label">Apellidos *</label>
                            <input type="text" name="last_names" id="last_names"
                                class="form-control @error('last_names') is-invalid @enderror"
                                value="{{ $certificate->holder->last_names }}" pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+"
                                title="Solo se permiten letras y espacios"
                                oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\u00f1\u00d1\s]/g, '')" required>
                            @error('last_names')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="identification_type" class="form-label">Tipo ID *</label>
                            <select name="identification_type" id="identification_type"
                                class="form-select @error('identification_type') is-invalid @enderror" required>
                                <option value="">Seleccione</option>
                                @foreach($idTypes as $key => $value)
                                    <option value="{{ $key }}" {{ old('identification_type', $certificate->holder->identification_type) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('identification_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="identification_number" class="form-label">Número de identificación *</label>
                            <input type="text" name="identification_number" id="identification_number"
                                class="form-control @error('identification_number') is-invalid @enderror"
                                value="{{ $certificate->holder->identification_number }}" pattern="[0-9]+"
                                title="Solo se permiten números" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                maxlength="15" required>
                            @error('identification_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="identification_place" class="form-label">Lugar de expedición *</label>
                            <input type="text" name="identification_place" id="identification_place"
                                class="form-control @error('identification_place') is-invalid @enderror"
                                value="{{ $certificate->holder->identification_place }}"
                                pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+" title="Solo se permiten letras y espacios"
                                oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\u00f1\u00d1\s]/g, '')" required>
                            @error('identification_place')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="blood_type" class="form-label">Tipo de sangre *</label>
                            <select name="blood_type" id="blood_type"
                                class="form-select @error('blood_type') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach($bloodTypes as $value)
                                    <option value="{{ $value }}" {{ old('blood_type', $certificate->holder->blood_type) == $value ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('blood_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="photo" class="form-label">Foto</label>
                            <input type="file" name="photo" id="photo"
                                class="form-control @error('photo') is-invalid @enderror"
                                accept="image/jpeg,image/png,image/jpg">
                            @if($certificate->holder->photo_path)
                                <small class="d-block mt-1 text-muted">
                                    Actual: <a href="{{ asset('storage/' . $certificate->holder->photo_path) }}"
                                        target="_blank">Ver foto</a>
                                </small>
                            @endif
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="has_drivers_license" class="form-label">¿Tiene licencia de conducir?</label>
                            <select id="has_drivers_license" name="has_drivers_license" class="form-select">
                                <option value="NO" {{ old('has_drivers_license', $certificate->holder->has_drivers_license ?? 'NO') == 'NO' ? 'selected' : '' }}>NO</option>
                                <option value="SI" {{ old('has_drivers_license', $certificate->holder->has_drivers_license ?? 'NO') == 'SI' ? 'selected' : '' }}>SI</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="drivers_license_category" class="form-label">Categoría licencia</label>
                            <select id="drivers_license_category" name="drivers_license_category" class="form-select"
                                {{ (old('has_drivers_license', $certificate->holder->has_drivers_license ?? 'NO') == 'NO') ? 'disabled' : '' }}>
                                <option value="">-- Seleccione --</option>
                                @foreach(['A1','A2','B1','B2','C1','C2'] as $cat)
                                    <option value="{{ $cat }}" {{ old('drivers_license_category', $certificate->holder->drivers_license_category) == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Estado *</label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror"
                            required>
                            <option value="active" {{ $certificate->status == 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="inactive" {{ $certificate->status == 'inactive' ? 'selected' : '' }}>Inactivo
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.certificates.show', $certificate) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar certificado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const textFields = ['first_names', 'last_names', 'identification_place'];
                textFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        field.addEventListener('input', function () {
                            this.value = this.value.toUpperCase();
                        });
                    }
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const hasLicenseSelect = document.getElementById('has_drivers_license');
                const categorySelect = document.getElementById('drivers_license_category');
                
                function toggleCategoryField() {
                    if (hasLicenseSelect.value === 'NO') {
                        categorySelect.disabled = true;
                        categorySelect.value = '';
                    } else {
                        categorySelect.disabled = false;
                    }
                }
            
                toggleCategoryField();
                
                hasLicenseSelect.addEventListener('change', toggleCategoryField);
            });
        </script>
    @endpush
@endsection