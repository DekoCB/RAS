<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carnet Estudiante</title>
    <link rel="stylesheet" href="{{ url('assets/css/plugins/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('css/secretaria-dashboard.css') }}">
    <style>
        body {
            margin: 0;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    @include('secretaria.estudiantes.carnet', [
        'estudiante' => $estudiante,
        'matricula' => $matricula,
        'qr' => $qr,
    ])

    @if ($autoPrint ?? false)
        <script>
            window.addEventListener('load', () => {
                window.print();
                window.addEventListener('afterprint', () => window.close());
            });
        </script>
    @endif
</body>
</html>
