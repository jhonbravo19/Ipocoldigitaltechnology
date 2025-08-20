@extends('layouts.admin')

@section('content')
    <div class="p-5 text-center"
        style="background: url('{{ $config->background_image_url ?? '' }}') no-repeat center center; background-size: cover;">

        @if($config->company_logo)
            <img src="{{ $config->company_logo_url }}" alt="Logo" class="mb-3" style="max-height: 80px;">
        @endif

        <h1 class="fw-bold">{{ $config->certificate_title }}</h1>

        <div class="my-4">
            <p>{{ $config->intro_text }}</p>
            <h2 class="fw-bold text-uppercase">{{ $sampleHolder->first_names }} {{ $sampleHolder->last_names }}</h2>
            <p class="mb-0">Por haber completado el curso:</p>
            <h4 class="fw-bold">{{ $sampleCourse->name }}</h4>
            <p class="text-muted">{{ $config->additional_text }}</p>
        </div>

        <div class="row mt-5">
            @if($config->signature_1_image)
                <div class="col-md-6">
                    <img src="{{ $config->signature_1_image_url }}" style="max-height: 60px;">
                    <p class="fw-bold mb-0">{{ $config->signature_1_name }}</p>
                    <small>{{ $config->signature_1_position }}</small>
                </div>
            @endif
            @if($config->signature_2_image)
                <div class="col-md-6">
                    <img src="{{ $config->signature_2_image_url }}" style="max-height: 60px;">
                    <p class="fw-bold mb-0">{{ $config->signature_2_name }}</p>
                    <small>{{ $config->signature_2_position }}</small>
                </div>
            @endif
        </div>
    </div>
@endsection