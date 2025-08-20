<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carnet</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 30px;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .card {
            width: 840px;
            height: 350px;
            padding: 20px;
            box-sizing: border-box;
            display: block;
            background: url('{{ storage_path("app/public/" . ($config->carnet_background_image ?? "images/default-bg.jpg")) }}') no-repeat center center;
            background-size: contain;
        }

        .row {
            display: table;
            width: 100%;
            height: 100%;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .left {
            width: 30%;
            text-align: center;
            padding-right: 15px;
        }

        .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid #333;
            margin: 0 auto 20px;
            background-size: cover;
            background-position: center;
            background-color: #fff;
        }

        .photo {
            width: 160px;
            height: 200px;
            margin: 0 auto;
            object-fit: cover;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .right {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            text-align: center;
        }

        .course {
            font-size: 18px;
            font-weight: bold;
            margin: 5px auto 10px auto;
            text-align: center;
            line-height: 1.2;
            max-width: 100%;
        }

        .name {
            font-size: 18px;
            font-weight: bold;
            margin: 25px 0 20px 0;
            text-align: center;
        }

        .data-table {
            width: 80%;
            margin: 0 auto 20px auto;
            font-size: 14px;
            border-collapse: collapse;
        }

        .data-table td {
            width: 50%;
            text-align: center;
            padding: 6px 0;
        }

        .footer {
            font-size: 14px;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="row">
            <div class="col left">
                @if($config->company_logo)
                    <div class="logo" style="background-image: url('{{ storage_path('app/public/' . $config->company_logo) }}');"></div>
                @else
                    <div class="logo" style="background-image: url('{{ public_path('images/logo.png') }}');"></div>
                @endif

                @if ($certificate->holder->photo_path)
                    <img src="{{ storage_path('app/public/' . $certificate->holder->photo_path) }}" class="photo" alt="Foto">
                @else
                    <div class="photo"></div>
                @endif
            </div>

            <div class="col right">
                <div class="title">{{ $config->certificate_title ?? 'COLSERTRANS' }}</div>

                <div class="course">{{ $certificate->course->name }}</div>

                <div class="name">
                    {{ $certificate->holder->first_names }} {{ $certificate->holder->last_names }}
                </div>

                <table class="data-table">
                    <tr>
                        <td>
                            {{ $certificate->holder->identification_type }}:
                            {{ $certificate->holder->identification_number }}
                        </td>
                        <td>
                            RH: {{ $certificate->holder->blood_type ?? 'O+' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            SERIAL: {{ $certificate->series_number ?? 'DWH-0006' }}
                        </td>
                        <td>
                            {{ $certificate->course->duration_hours }} HORAS
                        </td>
                    </tr>
                </table>

                <div class="footer">
                    APROBÓ EL {{ \Carbon\Carbon::parse($certificate->issue_date)->format('d/m/Y') }}
                    VÁLIDO HASTA {{ \Carbon\Carbon::parse($certificate->expiry_date)->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>