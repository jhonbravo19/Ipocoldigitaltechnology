<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Carnet • Stress Test</title>
<style>
  /* ===== Dimensiones CR80 (85.6 x 54 mm) ===== */
  html, body { margin:0; padding:0; }
  body { font-family: DejaVu Sans, sans-serif; color:#0b1220; }

  .card {
    width: 85.6mm; height: 54mm; position: relative; overflow: hidden;
    /* Marco múltiple (estrés visual) */
    border: 1.1mm solid #0f172a;
    outline: 0.7mm double #b91c1c;
    box-shadow: 0 0 0 1mm #fff inset;
    background: #ffffff;
  }

  /* ===== Fondos de seguridad ===== */
  .bg-band {
    position:absolute; left:0; right:0; top:8mm; height: 10mm;
    background: #f2f5ff;
    border-top: 0.4mm dashed #9ca3af; border-bottom: 0.4mm dashed #9ca3af;
  }
  .watermark {
    position:absolute; inset: 0;
    display: table; width:100%; height:100%;
    color: rgba(15,23,42,.06);
    font-weight: 700; font-size: 18mm; letter-spacing: 1mm; text-align:center;
  }
  .watermark span{ display: table-cell; vertical-align: middle; }

  /* ===== Contenido principal con tablitas (mejor soporte DomPDF) ===== */
  .pad { position: relative; z-index:2; padding: 5mm 6mm 7mm; height: 100%; }

  .head { width:100%; border-collapse: collapse; }
  .head td { vertical-align: middle; }
  .logo{ width: 16mm; height: 16mm; border:0.4mm solid #0f172a; padding:1mm; background:#fff; }
  .ttl  { font-size: 4.4mm; font-weight: 700; }
  .sttl { font-size: 2.8mm; color:#475569; margin-top: 0.8mm; }

  .spacer { height: 2mm; }

  .cols { width:100%; border-collapse: collapse; }
  .cols td { vertical-align: top; }
  .left { width: 30mm; }
  .right { padding-left: 3mm; }

  .photo {
    width: 100%; height: 28mm; background:#e5e7eb;
    border: 0.5mm solid #0b1220; border-radius: 2mm;
  }

  .holo-wrap{
    margin-top: 2mm; height: 11mm; position: relative;
    border: 0.5mm solid #0f172a; border-radius: 1.2mm; overflow:hidden;
    background: #f1f5f9;
  }
  .holo-ring{
    position:absolute; right:2mm; top:50%; margin-top:-4mm;
    width: 8mm; height: 8mm; border-radius: 99mm;
    border: 0.6mm solid #0f172a; outline: 0.6mm dotted #b91c1c;
  }
  .holo-micro {
    position:absolute; left:2mm; right:12mm; top:1.8mm;
    font-size: 1.8mm; color:#475569; white-space:nowrap; overflow:hidden;
  }

  .tbl { width:100%; border-collapse: collapse; margin-top: 1mm; }
  .tbl tr:nth-child(odd)  { background: #f8fafc; }
  .tbl tr:nth-child(even) { background: #eef2ff; }
  .tbl td {
    font-size: 3mm; padding: 1.2mm 1.2mm;
    border-bottom: 0.2mm solid #cbd5e1;
  }
  .lbl { color:#475569; width: 18mm; font-weight: 600; }
  .val { font-weight: 700; }

  .foot {
    position:absolute; left:6mm; right:6mm; bottom: 5mm;
    display: table; width: calc(100% - 12mm);
  }
  .foot-row { display: table-row; }
  .foot-cell { display: table-cell; vertical-align: top; }

  .meta {
    font-size: 2.6mm; color:#0f172a;
    border-top: 0.35mm dashed #0f172a; padding-top: 1.2mm;
  }
  .box {
    border: 0.5mm solid #0f172a; border-radius: 1mm; height: 12mm; margin-left: 2mm; position: relative; background:#fff;
  }
  .box .caption {
    position:absolute; top:-3.2mm; left: 2mm; background:#fff; padding: 0 1mm;
    font-size: 2.4mm; color:#0f172a;
  }
  .sign { margin-right: 2mm; }

  .microline {
    position:absolute; left:0; right:0; bottom:0;
    height: 3.2mm; font-size: 2mm; color:#334155;
    border-top: 0.35mm dashed #0f172a; background:#f1f5f9;
    padding: 0.6mm 2mm; white-space: nowrap; overflow:hidden;
  }
</style>
</head>
<body>
@php
  $holder = $certificate->holder;
  $course = $certificate->course;

  // Imágenes desde storage público: construir rutas absolutas de sistema
  $logo = !empty($config?->company_logo) ? public_path('storage/'.$config->company_logo) : null;
  $bg   = !empty($config?->carnet_background_image) ? public_path('storage/'.$config->carnet_background_image) : null;
  $foto = !empty($holder?->photo_path) ? public_path('storage/'.$holder->photo_path) : null;

  $issue = $certificate->issue_date?->format('d/m/Y') ?? '';
  $exp   = $certificate->expiry_date?->format('d/m/Y') ?? '';
@endphp

<div class="card">
  {{-- Fondo opcional de tu configuración --}}
  @if($bg && file_exists($bg))
    <img src="file://{{ $bg }}" alt="bg" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.12;">
  @endif

  <div class="bg-band"></div>
  <div class="watermark"><span>{{ strtoupper($config->certificate_title ?? 'COLSERTRANS') }}</span></div>

  <div class="pad">
    {{-- CABECERA (logo + títulos) --}}
    <table class="head">
      <tr>
        <td style="width:18mm;">
          @if($logo && file_exists($logo))
            <img class="logo" src="file://{{ $logo }}" alt="logo">
          @else
            <div class="logo" style="display:table; text-align:center;"><span style="display:table-cell; vertical-align:middle; font-size:3mm; color:#64748b;">LOGO</span></div>
          @endif
        </td>
        <td>
          <div class="ttl">{{ $config->certificate_title ?? 'COLSERTRANS' }}</div>
          <div class="sttl">Carnet de certificación</div>
        </td>
      </tr>
    </table>

    <div class="spacer"></div>

    {{-- DOS COLUMNAS: IZQ (foto+holo) / DER (tabla datos) --}}
    <table class="cols">
      <tr>
        <td class="left">
          @if($foto && file_exists($foto))
            <img class="photo" src="file://{{ $foto }}" alt="foto">
          @else
            <div class="photo" style="display:table; text-align:center;">
              <span style="display:table-cell; vertical-align:middle; color:#64748b; font-size:3mm;">SIN FOTO</span>
            </div>
          @endif

          <div class="holo-wrap">
            <div class="holo-micro">*** VALIDACIÓN • SEGURIDAD • COLSERTRANS • {{ date('Y') }} ***</div>
            <div class="holo-ring"></div>
          </div>
        </td>
        <td class="right">
          <table class="tbl">
            <tr><td class="lbl">Nombres</td><td class="val">{{ $holder->first_names ?? '' }}</td></tr>
            <tr><td class="lbl">Apellidos</td><td class="val">{{ $holder->last_names ?? '' }}</td></tr>
            <tr><td class="lbl">CC</td><td class="val">{{ $holder->identification_number ?? '' }}</td></tr>
            <tr><td class="lbl">Curso</td><td class="val">{{ $course->name ?? '' }}</td></tr>
            <tr><td class="lbl">Horas</td><td class="val">{{ $course->duration_hours ?? '' }}</td></tr>
            <tr><td class="lbl">Sangre</td><td class="val">{{ $holder->blood_type ?? 'O+' }}</td></tr>
            @if(!empty($holder?->has_drivers_license))
              <tr><td class="lbl">Licencia</td><td class="val">{{ $holder->has_drivers_license }} {{ $holder->drivers_license_category }}</td></tr>
            @endif
          </table>
        </td>
      </tr>
    </table>
  </div>

  {{-- PIE: metadatos + áreas de firma/sello --}}
  <div class="foot">
    <div class="foot-row">
      <div class="foot-cell" style="width: 40%;">
        <div class="meta">
          Serie: {{ $certificate->series_number ?? '' }}<br>
          Emisión: {{ $issue }} — Vence: {{ $exp }}
        </div>
      </div>
      <div class="foot-cell sign">
        <div class="box">
          <div class="caption">Firma</div>
        </div>
      </div>
      <div class="foot-cell">
        <div class="box">
          <div class="caption">Sello</div>
        </div>
      </div>
    </div>
  </div>

  <div class="microline">
    *** MICROTEXT *** USO INTERNO DE VALIDACIÓN • PROPIEDAD COLSERTRANS • NO VÁLIDO SIN FIRMA Y SELLO • {{ strtoupper($holder->last_names ?? '') }} • {{ strtoupper($course->name ?? '') }} • {{ $certificate->id }} • {{ now()->format('Y-m-d H:i') }} ***
  </div>
</div>
</body>
</html>
