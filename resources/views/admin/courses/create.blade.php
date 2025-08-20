@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">Nuevo Curso</h2>
        <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nombre del curso</label>
                <input type="text" name="name" id="name" class="form-control" required
                    value="{{ old('name', $course->name ?? '') }}" oninput="this.value = this.value.toUpperCase();">
                @error('name')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="duration_hours" class="form-label">Duración (horas)</label>
                <input type="number" name="duration_hours" id="duration_hours" class="form-control" min="1" required
                    value="{{ old('duration_hours') }}">
                @error('duration_hours')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="serial_prefix" class="form-label">Prefijo de serie</label>
                <input type="text" name="serial_prefix" id="serial_prefix" class="form-control" maxlength="10" required
                    value="{{ old('serial_prefix', $course->serial_prefix ?? '') }}"
                    oninput="this.value = this.value.toUpperCase();">
                @error('serial_prefix')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="manual_file" class="form-label">Manual del curso (PDF o DOCX)</label>
                <input type="file" name="manual_file" id="manual_file" class="form-control" accept=".pdf,.docx">
                @error('manual_file')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="card_back_file" class="form-label">Carnet reverso (PDF, JPG o PNG)</label>
                <input type="file" name="card_back_file" id="card_back_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                @error('card_back_file')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="acta_template_file" class="form-label">Plantilla de acta (DOCX)</label>
                <input type="file" name="acta_template_file" id="acta_template_file" class="form-control" accept=".docx">
                @error('acta_template_file')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success">Crear curso</button>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection
