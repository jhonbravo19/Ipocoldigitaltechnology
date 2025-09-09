<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Certificado (Plain)</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12pt; }
    table { width: 100%; border-collapse: collapse; }
    td { text-align: center; padding: 8px; }
    .title { font-size: 18pt; font-weight: bold; }
    .name { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
    .line { border-bottom: 1px solid #000; height: 10px; }
  </style>
</head>
<body>
  <table>
    <tr><td class="title">{{ $config->certificate_title ?? 'Certificado' }}</td></tr>
    <tr><td>{{ $config->intro_text ?? '' }}</td></tr>
    <tr><td>CONSTANCIA OTORGADA A</td></tr>
    <tr><td class="name">{{ $certificate->holder->first_names }} {{ $certificate->holder->last_names }}</td></tr>
    <tr><td>
      {{ $certificate->holder->identification_type }}
      {{ $certificate->holder->identification_number }} de
      {{ $certificate->holder->identification_place }}
    </td></tr>
    <tr><td>ASISTIÓ Y APROBÓ EL CURSO</td></tr>
    <tr><td>{{ $certificate->course->name }} ({{ $certificate->course->duration_hours }} horas)</td></tr>
    <tr><td>
      Aprobó el {{ \Carbon\Carbon::parse($certificate->issue_date)->format('d/m/Y') }}
      — válido hasta {{ \Carbon\Carbon::parse($certificate->expiry_date)->format('d/m/Y') }}
    </td></tr>
  </table>
  <br><br>
  <table>
    <tr>
      <td>
        <div class="line"></div>
        {{ $config->signature_1_name }}<br><small>{{ $config->signature_1_position }}</small>
      </td>
      <td>
        <div class="line"></div>
        {{ $config->signature_2_name }}<br><small>{{ $config->signature_2_position }}</small>
      </td>
    </tr>
  </table>
  <br>
  <div style="text-align:center">{{ $config->additional_text ?? '' }}</div>
</body>
</html>
