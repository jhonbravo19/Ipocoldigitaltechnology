@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">Cursos disponibles</h2>
        <a href="{{ route('admin.courses.create') }}" class="btn btn-success mb-3">Nuevo curso</a>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Duración (horas)</th>
                    <th>Prefijo serie</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                    <tr>
                        <td>{{ $course->name }}</td>
                        <td>{{ $course->description }}</td>
                        <td>{{ $course->duration_hours }}</td>
                        <td>{{ $course->serial_prefix }}</td>
                        <td>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Eliminar este curso?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $courses->links() }}
    </div>
@endsection