<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medición y Pesado - BakeStock</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(160deg, #F5F0E8 0%, #C9A87C 50%, #A0522D 100%);
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            width: 100%;
            background: linear-gradient(90deg, #3B1F0E, #6B3A2A, #A0522D, #C9A87C, #F5F0E8);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 30px;
            padding: 14px 40px;
            box-shadow: 0 4px 12px rgba(59, 31, 14, 0.4);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #F5F0E8;
            letter-spacing: 2px;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: #F5F0E8;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 6px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover,
        .nav-links a.activo {
            background-color: #F5F0E8;
            color: #3B1F0E;
        }

        /* ===== CONTENIDO ===== */
        .contenido {
            padding: 50px 40px;
            text-align: center;
        }

        .contenido h1 {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: 4px;
            background: linear-gradient(90deg, #3B1F0E, #6B3A2A, #C9A87C);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }

        .contenido p {
            font-size: 1.1rem;
            color: #3B1F0E;
            opacity: 0.8;
        }
    </style>
</head>
<body>

    <!-- NAVBAR CON BOTONES -->
    <nav class="navbar">
        <div class="nav-logo">🍞</div>
        <ul class="nav-links">
            <li><a href="medicionYPesado.php" class="activo"> Medición</a></li>
            <li><a href="#">Corte</a></li>
            <li><a href="#"> Mezcla</a></li>
        </ul>
    </nav>

    <!-- CONTENIDO -->
    <div class="contenido">
        <h1>MEDICIÓN Y PESADO</h1>
        <p>Control de utensilios de medición y pesado</p>
    </div>

</body>
</html>
