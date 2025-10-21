<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestiónPro</title>
    <link rel="icon" type="image/png" href="{{ asset('diagram-project.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --dashboard-primary: #4361ee;
            --dashboard-light: #f5f7fa;
            --dashboard-dark: #212529;
            --dashboard-radius: 12px;
        }

        body {
            background-color: var(--dashboard-light);
            color: var(--dashboard-dark);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hero {
            background-color: white;
            color: var(--dashboard-dark);
            border-radius: var(--dashboard-radius);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            padding: 50px;
            margin: 50px auto;
            max-width: 800px;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            flex-direction: column;
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .btn-custom-primary {
            background-color: var(--dashboard-primary);
            border-color: var(--dashboard-primary);
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-custom-primary:hover {
            background-color: #3855d0;
            border-color: #3855d0;
            color: white;
        }

        .web-access-link {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--dashboard-primary);
            text-decoration: none;
            padding: 10px 0;
            margin-top: 30px;
            display: flex;
            align-items: center;
            transition: color 0.2s ease;
        }
        .web-access-link:hover {
            color: #3855d0;
            text-decoration: underline;
        }

        footer {
            background: transparent;
            color: #6c757d;
            padding: 1.5rem 0;
            width: 100%;
            text-align: center;
        }
    </style>
    <script src="https://kit.fontawesome.com/cb4d844d5a.js" crossorigin="anonymous"></script>
</head>
<body>

<section class="hero container">
    <h1 class="mb-3">Bienvenido a <span class="fw-bold">GestiónPro</span></h1>
    <p>Organiza, supervisa y gestiona tus proyectos de manera eficiente. Accede a tus reportes, progreso y tareas en tiempo real, desde cualquier dispositivo.</p>

    <h2 class="h5 mt-3 text-secondary">Descarga nuestra aplicación:</h2>
    <div class="download-buttons mt-4 d-flex justify-content-center flex-wrap">
        <a href="#" class="btn btn-custom-primary me-3 mb-2">
            <i class="fa-brands fa-android me-3"></i></i>Descargar para Android
        </a>
        <a href="#" class="btn btn-outline-primary mb-2" style="--bs-btn-border-color: var(--dashboard-primary); --bs-btn-color: var(--dashboard-primary); --bs-btn-hover-bg: #e9ecef; padding: 10px 20px;">
            <i class="fa-brands fa-apple me-3"></i>Descargar para iOS
        </a>
    </div>

    <a href="sitio_web_principal_url_aqui" class="web-access-link mt-5">
        <i class="bi bi-box-arrow-in-right me-2"></i>
        O accede al sitio web
    </a>
</section>

<footer>
    <small>© 2025 GestiónPro</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
