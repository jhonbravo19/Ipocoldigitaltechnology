<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Carnet</title>
<style>
  /* Tamaño tarjeta CR80 aprox (ajusta si usas setPaper custom) */
  html,body{ margin:0; padding:0; }
  .card{
    width: 86mm; height: 54mm;
    position: relative;
    font-family: DejaVu Sans, sans-serif;
    color: #0f172a;
    overflow: hidden;
  }
  .bg{
    position:absolute; inset:0;
  }
  .content{
    position: relative; z-index:2;
    padding: 6mm 7mm;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4mm;
  }
  .brand{
    display:flex; align-items:center; gap:4mm;
    grid-column: 1 / -1;
  }
  .brand img{ height:14mm; }
  .photo{
    width:100%; height:28mm; object-fit: cover; border-radius: 3mm;
    border: 1px solid rgba(15,23,42,.15);
  }
  .field{ font-size: 3.2mm; line-height: 1.2; margin-bottom: 1.5mm; }
  .label{ font-weight: 600; opacity:.75; }
  .value{ font-weight: 600; }
  .footer{
    position:absolute; left:7mm; right:7mm; bottom:4mm;
    display:flex; justify-content: space-between; font-size:3mm; opacity:.85;
  }
</style>
</head>
<body>
@php
  $holder = $certificate->holder;
  $course = $certificate->course;

  // Helpers de imágenes desde storage público
  $logo = !empty($config?->company_logo) ? public_path('storage/'.$config->company_logo) : null;
  $bg   = !empty($config?->carnet_background_image) ? public_path('storage/'.$config->carnet_background_image) : null;
  $foto = !empty($holder?->photo_path) ? public_path('storage/'.$holder->photo_path) : null;

  $issue = $certificate->issue_date?->format('d/m/Y') ?? '';
  $exp   = $certificate->expiry_date?->format('d/m/Y') ?? '';
@endphp

<div class="card">
  @if($bg && file_exists($bg))
    <img class="bg" src="file://{{ $bg }}" alt="bg">
  @endif

  <div class="content">
    <div class="brand">
      @if($logo && file_exists($logo))
        <img src="file://{{ $logo }}" alt="logo">
      @endif
      <div>
        <div class="field"><span class="value">{{ $config->certificate_title ?? 'COLSERTRANS' }}</span></div>
        <div class="field" style="font-size:3mm; opacity:.8;">Carnet de certificación</div>
      </div>
    </div>

    <div>
      @if($foto && file_exists($foto))
        <img class="photo" src="file://{{ $foto }}" alt="foto">
      @else
        <div class="photo" style="display:flex;align-items:center;justify-content:center;opacity:.6;">
          SIN FOTO
        </div>
      @endif
    </div>

    <div>
      <div class="field"><span class="label">Nombres:</span> <span class="value">{{ $holder->first_names ?? '' }}</span></div>
      <div class="field"><span class="label">Apellidos:</span> <span class="value">{{ $holder->last_names ?? '' }}</span></div>
      <div class="field"><span class="label">CC:</span> <span class="value">{{ $holder->identification_number ?? '' }}</span></div>
      <div class="field"><span class="label">Curso:</span> <span class="value">{{ $course->name ?? '' }}</span></div>
      <div class="field"><span class="label">Horas:</span> <span class="value">{{ $course->duration_hours ?? '' }}</span></div>
      <div class="field"><span class="label">Sangre:</span> <span class="value">{{ $holder->blood_type ?? 'O+' }}</span></div>
      @if(!empty($holder?->has_drivers_license))
        <div class="field"><span class="label">Licencia:</span> <span class="value">{{ $holder->has_drivers_license }} {{ $holder->drivers_license_category }}</span></div>
      @endif
    </div>
  </div>

  <div class="footer">
    <div>Serie: {{ $certificate->series_number ?? '' }}</div>
    <div>Emisión: {{ $issue }}</div>
    <div>Vence: {{ $exp }}</div>
  </div>
</div>
</body>
</html>
