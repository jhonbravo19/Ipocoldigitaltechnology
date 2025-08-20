@extends('layouts.admin')

@section('title', 'Crear Certificado')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Crear Nuevo Certificado</h1>
        <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Información del Certificado</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.certificates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="course_id" class="form-label">Curso *</label>
                            <select name="course_id" id="course_id"
                                class="form-select @error('course_id') is-invalid @enderror" required>
                                <option value="">Seleccionar curso...</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_names" class="form-label">Nombres *</label>
                                <input type="text" name="first_names" id="first_names"
                                    class="form-control @error('first_names') is-invalid @enderror"
                                    value="{{ old('first_names') }}" pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+"
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
                                    value="{{ old('last_names') }}" pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+"
                                    title="Solo se permiten letras y espacios"
                                    oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\u00f1\u00d1\s]/g, '')" required>
                                @error('last_names')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="identification_type" class="form-label">Tipo de ID *</label>
                                <select name="identification_type" id="identification_type"
                                    class="form-select @error('identification_type') is-invalid @enderror" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($idTypes as $key => $value)
                                        <option value="{{ $key }}" {{ old('identification_type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('identification_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="identification_number" class="form-label">Número de ID *</label>
                                <input type="text" name="identification_number" id="identification_number"
                                    class="form-control @error('identification_number') is-invalid @enderror"
                                    value="{{ old('identification_number') }}" pattern="[0-9]+"
                                    title="Solo se permiten números"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="15" required>
                                @error('identification_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="identification_place" class="form-label">Lugar de expedición *</label>
                                <input type="text" name="identification_place" id="identification_place"
                                    class="form-control @error('identification_place') is-invalid @enderror"
                                    value="{{ old('identification_place') }}" pattern="[A-Za-zÀ-ÿ\u00f1\u00d1\s]+"
                                    title="Solo se permiten letras y espacios"
                                    oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\u00f1\u00d1\s]/g, '')" required>
                                @error('identification_place')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="blood_type" class="form-label">Tipo de sangre *</label>
                                <select name="blood_type" id="blood_type"
                                    class="form-select @error('blood_type') is-invalid @enderror" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type)
                                        <option value="{{ $type }}" {{ old('blood_type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('blood_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issue_date" class="form-label">Fecha de emisión *</label>
                                <input type="date" name="issue_date" id="issue_date"
                                    class="form-control @error('issue_date') is-invalid @enderror"
                                    value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                @error('issue_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="has_drivers_license" class="form-label">¿Tiene licencia de conducir?</label>
                                <select id="has_drivers_license" name="has_drivers_license" class="form-select">
                                    <option value="NO" {{ old('has_drivers_license', 'NO') == 'NO' ? 'selected' : '' }}>NO</option>
                                    <option value="SI" {{ old('has_drivers_license') == 'SI' ? 'selected' : '' }}>SI</option>
                                </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="drivers_license_category" class="form-label">Categoría licencia</label>
                                <select id="drivers_license_category" name="drivers_license_category" class="form-select"
                                    {{ old('has_drivers_license', 'NO') == 'NO' ? 'disabled' : '' }}>
                                    <option value="">-- Seleccione --</option>
                                    @foreach(['A1','A2','B1','B2', 'B3','C1','C2', 'C3'] as $cat)
                                        <option value="{{ $cat }}" {{ old('drivers_license_category') == $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Foto del certificado (opcional)</label>
                            <input type="file" name="photo" id="photo"
                                class="form-control @error('photo') is-invalid @enderror"
                                accept="image/jpeg,image/png,image/jpg">
                            <div class="form-text">Formato: JPG, PNG. Tamaño máximo: 2MB</div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary me-md-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Crear Certificado
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

                    const form = document.querySelector('form');
                    form.addEventListener('submit', function (e) {
                        const idNumber = document.getElementById('identification_number').value;
                        const firstName = document.getElementById('first_names').value;
                        const lastName = document.getElementById('last_names').value;
                        const place = document.getElementById('identification_place').value;

                        if (!/^\d+$/.test(idNumber)) {
                            e.preventDefault();
                            alert('El número de identificación debe contener solo números.');
                            return false;
                        }

                        const namePattern = /^[A-Za-zÀ-ÿ\u00f1\u00d1\s]+$/;
                        if (!namePattern.test(firstName)) {
                            e.preventDefault();
                            alert('Los nombres deben contener solo letras y espacios.');
                            return false;
                        }

                        if (!namePattern.test(lastName)) {
                            e.preventDefault();
                            alert('Los apellidos deben contener solo letras y espacios.');
                            return false;
                        }

                        if (!namePattern.test(place)) {
                            e.preventDefault();
                            alert('El lugar de expedición debe contener solo letras y espacios.');
                            return false;
                        }
                    });
                });

                document.getElementById('has_drivers_license').addEventListener('change', function() {
                    let categoryField = document.getElementById('drivers_license_category');
                    if (this.value === 'SI') {
                        categoryField.removeAttribute('disabled');
                    } else {
                        categoryField.setAttribute('disabled', 'disabled');
                        categoryField.value = '';
                    }
                });

            </script>
        @endpush
@endsection