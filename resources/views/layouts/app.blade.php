<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGAE - @yield('titulo')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            /* Fondo degradado oscuro/morado visto en los diseños */
            background: linear-gradient(135deg, #2b2b36 0%, #4a4a5a 50%, #1e1e24 100%);
            background-attachment: fixed;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .top-navbar {
            background-color: #1a1a1a;
            color: white;
            padding: 10px 20px;
            font-weight: bold;
            font-size: 1.2rem;
            text-transform: uppercase;
            border-bottom: 3px solid #333;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-results__option {
            color: #1a1a1a !important;
        }
    </style>
</head>
<body>

    <div class="top-navbar d-flex justify-content-between align-items-center">
        <div>SISTEMA DE GESTIÓN ADMINISTRATIVA ESCOLAR (SGAE)</div>
        <div>@yield('usuario_header')</div>
    </div>

    <div class="container-fluid p-0">
        @yield('contenido')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>