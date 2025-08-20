<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Certificado</title>
    <style>
        @page {
            margin: 0cm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .container {
            border: 5px solid #444;
            box-sizing: border-box;
            width: calc(90% - 120px);
            height: calc(100vh - 120px);
            margin: 100px auto;
            padding: 40px;
            background: url('{{ storage_path("app/public/" . ($config->background_image ?? "images/default-bg.jpg")) }}') no-repeat center center;
            background-size: cover;
        }

        .row {
            display: table;
            width: 100%;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .left-col {
            width: 25%;
            text-align: center;
            padding-right: 15px;
        }

        .logo {
            width: 160px;
            height: 160px;
            border-radius: 40%;
            margin: 0 auto 15px;
            background: url('{{ storage_path("app/public/" . ($config->background_image ?? "images/default-bg.jpg")) }}') no-repeat center center;
            background-size: contain;
        }

        .left-box {
            border: 1px solid #aaa;
            min-height: 400px;
            padding: 10px;
            font-size: 12px;
            background: #ffffffc7;
        }

        .right-col {
            width: 75%;
            padding-left: 15px;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .info {
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.3;
        }

        .highlight {
            font-size: 20px;
            font-weight: bold;
            margin: 12px 0;
            text-transform: uppercase;
        }

        .course {
            font-size: 18px;
            margin: 12px 0;
        }

        .footer {
            margin-top: 25px;
            display: table;
            width: 100%;
        }

        .firma {
            display: table-cell;
            text-align: center;
            width: 50%;
            vertical-align: top;
            padding: 20px;
        }

        .firma img {
            height: 70px;
            margin-bottom: 10px;
        }

        .firma-line {
            border-bottom: 1px solid #000;
            width: 70%;
            margin: 0 auto 8px auto;
        }

        .extra {
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
            border-top: 1px solid #aaa;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col left">
                @if($config->company_logo)
                    <div class="logo" style="background-image: url('{{ storage_path('app/public/' . $config->company_logo) }}');"></div>
                @else
                    <div class="logo" style="background-image: url('{{ public_path('images/logo.png') }}');"></div>
                @endif
            </div>

            <div class="col right-col">
                <h1>{{ $config->certificate_title ?? 'CERTIFICADO DE FINALIZACIÓN' }}</h1>
                <p class="info">{{ $config->intro_text ?? 'Texto resolución y fechas' }}</p>

                <p class="info">CERTIFICA QUE</p>

                <p class="highlight">
                    {{ $certificate->holder->first_names }} {{ $certificate->holder->last_names }}
                </p>

                <p class="info">
                    {{ $certificate->holder->identification_type }}
                    {{ $certificate->holder->identification_number }} de
                    {{ $certificate->holder->identification_place }}
                </p>

                <p class="info">ASISTIÓ Y APROBÓ EL CURSO DE</p>

                <p class="course">{{ $certificate->course->name }}</p>

                <p class="info">
                    Con una intensidad horaria de {{ $certificate->course->duration_hours }} horas
                </p>
                <p class="info">
                    Aprobó el {{ \Carbon\Carbon::parse($certificate->issue_date)->format('d/m/Y') }}
                    válido hasta {{ \Carbon\Carbon::parse($certificate->expiry_date)->format('d/m/Y') }}
                </p>


                <div class="footer">
                    <div class="firma">
                        @if($config->signature_1_image)
                            <img src="{{ storage_path('app/public/' . $config->signature_1_image) }}" alt="Firma 1"><br>
                        @endif
                        <div class="firma-line"></div>
                        {{ $config->signature_1_name }} <br>
                        <small>{{ $config->signature_1_position }}</small>
                    </div>
                    <div class="firma">
                        @if($config->signature_2_image)
                            <img src="{{ storage_path('app/public/' . $config->signature_2_image) }}" alt="Firma 2"><br>
                        @endif
                        <div class="firma-line"></div>
                        {{ $config->signature_2_name }} <br>
                        <small>{{ $config->signature_2_position }}</small>
                    </div>
                </div>


                <div class="extra">
                    {{ $config->additional_text ?? '' }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>