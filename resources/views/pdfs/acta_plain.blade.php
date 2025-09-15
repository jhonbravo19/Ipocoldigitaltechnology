@php
  // Rutas absolutas listas para DomPDF
$holder      = $certificate->holder ?? null;
$course      = $certificate->course ?? null;
$fullName    = trim(($holder->first_names ?? '').' '.($holder->last_names ?? ''));
$docType     = $holder->identification_type ?? 'CC';
$docNumber   = $holder->identification_number ?? '_______';
$docString   = trim($docType.' '.$docNumber);
$place       = $holder->identification_place ?? ($config->course_place ?? '_______');
$courseName  = $course->name ?? '—';
$logo = public_path('storage/logos/empresa.png');
$sig1 = public_path('storage/firmas/firma_capacitador.png');
$sig1Name = $config->signature_1_name ?? 'YOLANDA DEAQUIZ';
$sig1Role = 'DIRECTOR';
$sig2 = public_path('storage/firmas/firma_gerente.png');
$sig2Name = $config->signature_2_name ?? 'LISETH VARGAS';
$sig2Role = 'PROFESIONAL SST'; 
$bg   = public_path('storage/backgrounds/Logo.png'); // marca de agua opcional

$duration_hours = (int) ($certificate->course->duration_hours ?? 0);
$day   = $certificate->issue_date?->format('d') ?? '';
$month = $certificate->issue_date?->translatedFormat('F') ?? '';
$year  = $certificate->issue_date?->format('Y') ?? '';

  // Espera un arreglo tipo: [['tema'=>'...','horas'=>5,'objetivo'=>'...'], ...]
  // Si viene null, lo convertimos a []
  $modules = $certificate->course->modules ?? [];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Acta</title>
<style>
  /* Márgenes tipo carta institucional */
  @page { margin: 25mm 20mm; }
  *{ box-sizing:border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #111827; }

  /* Marca de agua */
  .wm{
    position: fixed; inset:0; z-index:-1; opacity:.05;
    display:flex; align-items:center; justify-content:center;
  }
  .wm img{ width: 480px; object-fit:contain; }

  /* ENCABEZADO */
<style>
.hdr-grid{
    width:100%;
    border:1px solid #0f172a;
    border-collapse:collapse;
    margin-bottom:12px;
    table-layout: fixed;
    font-size:10pt;
  }
  .hdr-grid td{
    border:1px solid #0f172a;
    vertical-align:middle;
    padding:6px 8px;
  }

  .hdr-left{  width:28%; }
  .hdr-mid{   width:42%; text-align:center; }
  .hdr-right{ width:30%; text-align:right; padding-right:12px; }

  .logoBox{ display:flex; justify-content:center; align-items:center; }
  .logoBox img{ height:34px; max-width:90px; object-fit:contain; display:block; }

  .title{    font-weight:800; font-size:12.5pt; line-height:1.15; }
  .subtitle{ font-weight:800; font-size:12pt;   line-height:1.15; }
  .formato-txt{ font-size:13pt; font-weight:600; }

  /* pila de 3 líneas dentro de la celda derecha */
  .meta-stack{
    display:flex; flex-direction:column; align-items:flex-end;
    gap:4px; line-height:1.35; font-size:10pt;
  }
  .meta-stack b{ font-weight:800; }

  /* TÍTULOS */
  .pretitle{ text-align:center; font-size:10pt; margin-bottom:6px; font-weight:700; }
  h1{ font-size:15pt; text-align:center; margin:12px 0 6px; font-weight:800; letter-spacing:.2px; }

  /* TABLA */
  table{ width:100%; border-collapse: collapse; margin-top:12px; font-size:10pt; }
  th,td{ border:1px solid #0f172a; padding:7px 8px; vertical-align:top; }
  th{
    background:#2f59ff; /* azul más vivo */
    color:#fff; text-align:center; font-weight:800;
  }
  td{ text-align:left; }
  .c{ text-align:center; }

  /* FIRMAS */
.signs-table{
  width:100%;
  border-collapse:separate; /* sin unirse a otras tablas */
  border-spacing:0;         /* sin espacios */
  margin-top:18px;
}
.signs-table td{
  width:50%;
  vertical-align:top;
  text-align:center;
  padding:0 10px;
}

.sig-imgwrap{
  height:70px;              /* altura uniforme */
  display:block;
}
.sig-img{
  max-height:60px;
  display:block;
  margin:0 auto;
  object-fit:contain;
}

.sig-line{
  width:70%;
  height:0;
  border-top:1.5px solid #0f172a;
  margin:6px auto 6px;
}
.sig-name{
  font-size:10pt; font-weight:800; text-transform:uppercase; line-height:1.1;
}
.sig-role{
  font-size:9pt; font-weight:700; text-transform:uppercase; line-height:1.1;
  margin-top:2px;
}

  /* FOOTER */
  footer{
    position: fixed; bottom: 20mm; left:20mm; right:20mm;
    font-size:9pt; text-align:center; line-height:1.35; color:#111827;
  }
  footer a{ color:#2f59ff; text-decoration:none; }
</style>
</head>
<body>

{{-- Marca de agua (quítala si no la quieres) --}}
@if(file_exists($bg))
  <div class="wm"><img src="file://{{ $bg }}" alt="marca"></div>
@endif

{{-- ENCABEZADO --}}
<table class="hdr-grid">
  <tr>
    <!-- Fila 1 -->
    <td class="hdr-left">
      <div class="logoBox">
        @if(file_exists($logo))
          <img src="file://{{ $logo }}" alt="logo">
        @else
          <div style="border:1px solid #999; width:100px; height:34px; display:flex; align-items:center; justify-content:center;">LOGO</div>
        @endif
      </div>
    </td>

    <td class="hdr-mid">
      <div class="title">PLAN DE CAPACITACIÓN TEÓRICO - PRÁCTICA</div>
    </td>

    <!-- Celda derecha ocupa 2 filas y contiene las 3 líneas -->
    <td class="hdr-right" rowspan="2">
      <div class="meta-stack">
        <div><b>CÓDIGO:</b> {{ $config->format_code ?? 'FM-FC-01' }}</div>
        <div><b>VERSIÓN:</b> {{ $config->format_version ?? '02' }}</div>
        <div><b>FECHA:</b> {{ $certificate->issue_date?->format('d/m/Y') ?? '—' }}</div>
      </div>
    </td>
  </tr>

  <tr>
    <!-- Fila 2 -->
    <td class="hdr-left">
      <div class="subtitle">formato</div>
    </td>
    <td class="hdr-mid">
      <div class="subtitle">PROCESO DE FORMACIÓN CONTINUA</div>
    </td>
  </tr>
</table>
<br>
<div class="pretitle">
  PLAN DE CAPACITACIÓN TEÓRICO - PRÁCTICA<br>
  PROCESO DE FORMACIÓN CONTINUA
</div>

<h1>PROGRAMA: {{ strtoupper($certificate->course->name ?? '—') }}</h1>
<br>
<p>
  El día <b>{{ $certificate->issue_date
      ? $certificate->issue_date->locale('es')->isoFormat('DD [de] MMMM [de] YYYY')
      : '___ de ________ de ____' }}</b>, se llevó a cabo el curso de
  <b>{{ strtoupper($certificate->course->name ?? '_______________________________') }}</b>.
</p>
<div style="text-align:center; margin:12px 0 4px;">
  <div style="
    display:inline-block;
    padding:10px 22px;
    border:2.5px solid #2f59ff;   /* color del borde */
    border-radius:20mm;            /* hace el efecto de óvalo */
    min-width:95mm;                /* ancho mínimo para que se vea como en el ejemplo */
  ">
    <div style="font-size:12pt; font-weight:700; text-transform:uppercase; letter-spacing:.2px;">
      {{ strtoupper(trim(($certificate->holder->first_names ?? '').' '.($certificate->holder->last_names ?? ''))) }}
    </div>
    <div style="font-size:11pt; margin-top:2px;">
      {{ strtoupper(($certificate->holder->identification_type ?? 'CC')) }}
      {{ $certificate->holder->identification_number ?? '' }}
    </div>
  </div>
</div>

@php
  // Intensidad horaria
  $hours = (int)($course->duration_hours ?? 0);
  $hoursTxt = $hours > 0 ? ($hours.' '.($hours === 1 ? 'hora' : 'horas')) : '______________';

  // Fecha larga en español (fallback con guiones)
  $dateFull = $certificate->issue_date
      ? $certificate->issue_date->locale('es')->isoFormat('DD [de] MMMM [de] YYYY')
      : '____ de __________ de __________';
@endphp

<p>
  El curso <b>{{ strtoupper($courseName) }}</b>, con una intensidad horaria de
  <b>{{ $hoursTxt }}</b>.
</p>

<p><b>Objetivo General:</b>
  <br>
  <br>
  Capacitar a los participantes para adquirir, fortalecer y demostrar competencias teóricas y prácticas
  que les permitan desempeñarse de manera eficiente, segura y responsable en el área de formación
  correspondiente, aplicando los conocimientos y habilidades adquiridas en contextos reales, para
  contribuir al desarrollo personal, profesional y organizacional.
</p>

<p>
  Se firma la presente <b>acta</b> en señal de conformidad por parte de los asistentes.
</p>

<p>
  Dado el día <b>{{ $dateFull }}</b>.
</p>

<br>
{{-- FIRMAS --}}

<table style="width:100%; border-collapse:collapse; border:none; margin-top:18px;">
  <tr>
    <!-- SIN línea al centro: sin border-right -->
    <td style="width:50%; vertical-align:top; text-align:center; padding:6px 10px; border:none;">
      <div style="height:70px;">
        @if(isset($sig1) && file_exists($sig1))
          <img src="file://{{ $sig1 }}" alt="Firma 1"
               style="max-height:75px; display:block; margin:0 auto; object-fit:contain;">
        @endif
      </div>
      <div style="width:70%; height:0; border-top:1.5px solid #0f172a; margin:6px auto;"></div>
      <div style="font-size:10pt; font-weight:800; text-transform:uppercase; line-height:1.1;">
        {{ $sig1Name }}
      </div>
      @if(!empty($sig1Role))
        <div style="font-size:9pt; font-weight:700; text-transform:uppercase; line-height:1.1; margin-top:2px;">
          {{ $sig1Role }}
        </div>
      @endif
    </td>

    <td style="width:50%; vertical-align:top; text-align:center; padding:6px 10px; border:none;">
      <div style="height:70px;">
        @if(isset($sig2) && file_exists($sig2))
          <img src="file://{{ $sig2 }}" alt="Firma 2"
               style="max-height:90px; display:block; margin:0 auto; object-fit:contain;">
        @endif
      </div>
      <div style="width:70%; height:0; border-top:1.5px solid #0f172a; margin:6px auto;"></div>
      <div style="font-size:10pt; font-weight:800; text-transform:uppercase; line-height:1.1;">
        {{ $sig2Name }}
      </div>
      @if(!empty($sig2Role))
        <div style="font-size:9pt; font-weight:700; text-transform:uppercase; line-height:1.1; margin-top:2px;">
          {{ $sig2Role }}
        </div>
      @endif
    </td>
  </tr>
</table>

</body>
</html>
