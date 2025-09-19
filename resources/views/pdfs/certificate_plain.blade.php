<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>{{ $config->certificate_title ?? 'Certificado' }}</title>
  <style>
    /* ====== Compatibilidad DomPDF ======
       - Sin @import ni fuentes externas
       - CSS inline
       - Evitar transform/fixed/filters complejos
    */
    @page { margin: 28mm 22mm 28mm 22mm; }
    * { box-sizing: border-box; }
    body{ background:#ffffff; margin:0; font-family: DejaVu Sans, Arial, sans-serif; font-size:12pt; color:#111827; }

    /* “Hoja” con relleno para simular A4 en pantalla (no afecta PDF) */
    .page-pad{ padding: 6px; position: relative; }

    /* Marco triple tipo diploma */
    .frame-outer{ border:6px solid #1f2937; padding:12px; position:relative; }
    .frame-mid{ border:3px solid #b45309; padding:10px; }
    .frame-inner{ border:1.5px dashed #9ca3af; padding:22px; position:relative; }

    /* Esquinas decorativas */
    .corner{ position:absolute; width:26px; height:26px; border:3px solid #1f2937; }
    .corner.tl{ top:8px; left:8px; border-right:none; border-bottom:none; }
    .corner.tr{ top:8px; right:8px; border-left:none; border-bottom:none; }
    .corner.bl{ bottom:8px; left:8px; border-right:none; border-top:none; }
    .corner.br{ bottom:8px; right:8px; border-left:none; border-top:none; }

    /* Cinta superior y “sello” */
    .ribbon{ position:absolute; top:-6px; left:-6px; right:-6px; height:12px; background:#b91c1c; }
    .badge{ position:absolute; top:18px; right:18px; width:92px; height:92px; border:2px solid #b45309; border-radius:50%; text-align:center; line-height:92px; font-weight:700; color:#b45309; }

    /* Marca de agua (SVG simple) */
    .watermark{
      position:absolute; inset:0; text-align:center; opacity:.07; pointer-events:none;
    }
    .wm-wrap{ display:inline-block; margin-top:60px; }
    .watermark svg{ width:420px; height:420px; }

    /* Tipografía y bloques */
    .brand{ text-align:center; letter-spacing:1.6px; color:#6b7280; font-size:12pt; margin-top:4px; }
    .title{ text-align:center; font-size:28pt; letter-spacing:4px; font-weight:800; color:#111827; text-transform:uppercase; margin:10px 0 4px; }
    .subtitle{ text-align:center; font-size:12pt; color:#374151; margin:0 0 16px; }
    .intro{ text-align:center; font-size:11.5pt; line-height:1.5; margin:10px 16px 18px; color:#1f2937; }
    .label{ text-align:center; color:#9ca3af; letter-spacing:1.6px; font-size:10.5pt; margin-top:8px; }
    .name{ text-align:center; font-size:22pt; font-weight:800; text-transform:uppercase; color:#0f172a; margin:6px 0 4px; word-break:break-word; }
    .info{ text-align:center; margin:6px 0; font-size:11pt; color:#111827; }

    /* Bloque dividido en 3 (sin flex/grid) */
    .split{ display:table; width:100%; margin-top:12px; font-size:10.5pt; color:#111827; border-top:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; }
    .split .cell{ display:table-cell; width:33.33%; padding:8px 6px; text-align:center; }
    .split .cell strong{ display:block; font-size:11pt; }

    /* Firmas */
    .sign{ width:100%; margin-top:24px; border-collapse:collapse; }
    .sign td{ width:50%; text-align:center; vertical-align:bottom; padding:12px 10px 0; }
    .sig-line{ border-top:1px solid #111827; height:1px; margin:40px 24px 8px; }
    .sig-name{ font-weight:700; font-size:11pt; color:#111827; }
    .sig-role{ font-size:10pt; color:#6b7280; }

    /* Verificación con QR simulado en SVG */
    .verify{ margin:14px auto 0; width:82%; border:1px solid #e5e7eb; padding:10px 12px; display:table; }
    .verify .col{ display:table-cell; vertical-align:middle; }
    .verify .col.qr{ width:92px; text-align:center; }
    .qrbox{ width:74px; height:74px; border:1px solid #111827; padding:6px; }
    .qrbox svg{ width:100%; height:100%; }

    .foot{ text-align:center; margin-top:14px; font-size:10pt; color:#6b7280; }
  </style>
</head>
<body>
  <div class="page-pad">
    <div class="frame-outer">
      <div class="ribbon"></div>
      <div class="frame-mid">
        <div class="frame-inner">
          <span class="corner tl"></span>
          <span class="corner tr"></span>
          <span class="corner bl"></span>
          <span class="corner br"></span>

          <div class="badge">{{ $config->badge_text ?? 'IPOCOL' }}</div>

          <!-- Marca de agua -->
          <div class="watermark">
            <div class="wm-wrap">
              <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="100" cy="100" r="90" fill="none" stroke="#111827" stroke-width="6"/>
                <circle cx="100" cy="100" r="70" fill="none" stroke="#111827" stroke-width="2" stroke-dasharray="6 6"/>
                <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"
                      font-size="22" font-weight="700" fill="#111827">
                  {{ strtoupper($config->watermark_text ?? 'CERTIFICADO') }}
                </text>
              </svg>
            </div>
          </div>

          <div class="brand">{{ $config->brand_name ?? 'IPOCOL DIGITAL TECHNOLOGY' }}</div>
          <div class="title">{{ $config->certificate_title ?? 'Certificado' }}</div>
          <div class="subtitle">{{ $config->subtitle ?? 'Constancia de aprobación' }}</div>

          <div class="intro">
            {{ $config->intro_text ?? 'Por medio de la presente se deja constancia de que la persona que se identifica enseguida asistió y aprobó el programa de formación indicado, cumpliendo los requisitos académicos y administrativos.' }}
          </div>

          <div class="label">OTORGADO A</div>
          <div class="name">
            {{ trim(($certificate->holder->first_names ?? '').' '.($certificate->holder->last_names ?? '')) }}
          </div>

          <div class="info">
            {{ $certificate->holder->identification_type ?? '' }}
            {{ $certificate->holder->identification_number ?? '' }}
            @if(!empty($certificate->holder->identification_place))
              de {{ $certificate->holder->identification_place }}
            @endif
          </div>

          <div class="info">
            <strong>Curso:</strong>
            {{ $certificate->course->name ?? 'N/A' }}
            — {{ $certificate->course->duration_hours ?? 0 }} horas
          </div>

          <div class="info">
            @if(!empty($certificate->issue_date))
              Aprobó el {{ \Carbon\Carbon::parse($certificate->issue_date)->format('d/m/Y') }}
            @endif
            @if(!empty($certificate->expiry_date))
              — válido hasta {{ \Carbon\Carbon::parse($certificate->expiry_date)->format('d/m/Y') }}
            @endif
          </div>

          <div class="split">
            <div class="cell">
              <strong>Serie</strong>
              {{ $certificate->series_number ?? ($config->series_prefix ?? 'SER').'-'.($certificate->id ?? '0000') }}
            </div>
            <div class="cell">
              <strong>Emisor</strong>
              {{ optional($certificate->issuer)->name ?? ($config->issuer_name ?? 'Dirección Académica') }}
            </div>
            <div class="cell">
              <strong>Ciudad</strong>
              {{ $config->city ?? 'Bogotá D.C.' }}
            </div>
          </div>

          <table class="sign">
            <tr>
              <td>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $config->signature_1_name ?? 'Firma 1' }}</div>
                <div class="sig-role"><small>{{ $config->signature_1_position ?? 'Cargo 1' }}</small></div>
              </td>
              <td>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $config->signature_2_name ?? 'Firma 2' }}</div>
                <div class="sig-role"><small>{{ $config->signature_2_position ?? 'Cargo 2' }}</small></div>
              </td>
            </tr>
          </table>

          <div class="verify">
            <div class="col qr">
              <div class="qrbox">
                <!-- QR simulado en SVG (valido para DomPDF) -->
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <rect x="0" y="0" width="100" height="100" fill="#fff"/>
                  <rect x="5" y="5" width="20" height="20" fill="#000"/>
                  <rect x="75" y="5" width="20" height="20" fill="#000"/>
                  <rect x="5" y="75" width="20" height="20" fill="#000"/>
                  <rect x="30" y="30" width="8" height="8" fill="#000"/>
                  <rect x="42" y="30" width="8" height="8" fill="#000"/>
                  <rect x="54" y="30" width="8" height="8" fill="#000"/>
                  <rect x="30" y="42" width="8" height="8" fill="#000"/>
                  <rect x="54" y="42" width="8" height="8" fill="#000"/>
                  <rect x="30" y="54" width="8" height="8" fill="#000"/>
                  <rect x="42" y="54" width="8" height="8" fill="#000"/>
                  <rect x="54" y="54" width="8" height="8" fill="#000"/>
                </svg>
              </div>
            </div>
            <div class="col" style="padding-left:12px;">
              <div style="font-size:10pt; color:#111827;">
                Verifique en <strong>{{ $config->verify_url ?? 'https://ipocol.edu/validar' }}</strong><br>
                Código: <strong>{{ $config->verify_code ?? 'ABCD-1234' }}</strong>
                — Serie: <strong>{{ $certificate->series_number ?? 'SER-0000' }}</strong>
              </div>
            </div>
          </div>

          @if(!empty($config->additional_text))
            <div class="foot">{{ $config->additional_text }}</div>
          @endif

        </div>
      </div>
    </div>
  </div>
</body>
</html>
