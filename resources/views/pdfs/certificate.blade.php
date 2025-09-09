<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Certificado</title>
  <style>
    /* A4 apaisado sin márgenes */
    @page { size: A4 landscape; margin: 0; }

    html, body { margin:0; padding:0; font-family: DejaVu Sans, sans-serif; }
    * { box-sizing: border-box; }

    /* Fondo como <img> (más compatible que background-image) */
    .bg {
      position: fixed;
      top: 0; left: 0;
      width: 297mm;   /* A4 landscape */
      height: 210mm;
      z-index: -1;
    }

    .content {
      position: relative;
      padding: 20mm 22mm;
      min-height: 210mm;
      text-align: center;
      color: #000;
    }

    h1 { margin: 0 0 6mm 0; font-size: 22pt; }
    p  { margin: 3mm 0; }
    .name { font-weight: 700; font-size: 20pt; margin: 6mm 0; text-transform: uppercase; }

    table { width: 100%; border-collapse: collapse; }
    td { text-align: center; vertical-align: bottom; padding: 0 10mm; }
    .sig { height: 18mm; margin-bottom: 2mm; }
    .line { border-bottom: 1px solid #000; height: 0; width: 70%; margin: 0 auto 2mm; }
    .small { font-size: 10pt; }
  </style>
</head>
<body>
@php
    use Illuminate\Support\Facades\Storage;

    // Helper: path -> data URI base64 (evita problemas de rutas/permisos)
    $toDataUri = function ($path) {
        if (!$path || !file_exists($path)) return null;
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    };

    // Rutas absolutas (usa defaults si no hay config)
    $bgPath   = $config->background_image
                  ? Storage::disk('public')->path($config->background_image)
                  : public_path('images/default-bg.jpg');

    $logoPath = $config->company_logo
                  ? Storage::disk('public')->path($config->company_logo)
                  : public_path('images/logo.png');

    $sig1Path = $config->signature_1_image ? Storage::disk('public')->path($config->signature_1_image) : null;
    $sig2Path = $config->signature_2_image ? Storage::disk('public')->path($config->signature_2_image) : null;

    // Data URIs
    $bgData   = $toDataUri($bgPath);
    $logoData = $toDataUri($logoPath);
    $sig1Data = $toDataUri($sig1Path);
    $sig2Data = $toDataUri($sig2Path);
@endphp

{{-- Fondo a página completa --}}
@if($bgData)
  <img class="bg" src="{{ $bgData }}" alt="Fondo">
@endif

<div class="content">
  {{-- (Opcional) logo arriba
  @if($logoData)
    <img src="{{ $logoData }}" style="height: 20mm; display:block; margin: 0 auto 4mm;">
  @endif
  --}}

  <h1>{{ $config->certificate_title ?? 'CERTIFICADO DE FINALIZACIÓN' }}</h1>
  <p>{{ $config->intro_text ?? 'Por medio del presente certificamos que' }}</p>

  <p>CONSTANCIA OTORGADA A</p>
  <p class="name">
    {{ $certificate->holder->first_names }} {{ $certificate->holder->last_names }}
  </p>

  <p>
    {{ $certificate->holder->identification_type }}
    {{ $certificate->holder->identification_number }}
    de {{ $certificate->holder->identification_place }}
  </p>

  <p>ASISTIÓ Y APROBÓ EL CURSO</p>
  <p>{{ $certificate->course->name }} ({{ $certificate->course->duration_hours }} horas)</p>

  <p>
    Aprobó el {{ \Carbon\Carbon::parse($certificate->issue_date)->format('d/m/Y') }}
    — válido hasta {{ \Carbon\Carbon::parse($certificate->expiry_date)->format('d/m/Y') }}
  </p>

  <br><br>

  <table>
    <tr>
      <td>
        @if($sig1Data)<img class="sig" src="{{ $sig1Data }}" alt="Firma 1">@endif
        <div class="line"></div>
        <div>{{ $config->signature_1_name }}</div>
        <div class="small">{{ $config->signature_1_position }}</div>
      </td>
      <td>
        @if($sig2Data)<img class="sig" src="{{ $sig2Data }}" alt="Firma 2">@endif
        <div class="line"></div>
        <div>{{ $config->signature_2_name }}</div>
        <div class="small">{{ $config->signature_2_position }}</div>
      </td>
    </tr>
  </table>

  <br>
  <p class="small">{{ $config->additional_text ?? '' }}</p>
</div>
</body>
</html>
