<?php
session_start();

/* Conexión con la base de datos */
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=bakestock;charset=utf8mb4", "root", ""
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    /* Conservar los productos que todavía existan en la sesión */
    if (!isset($_SESSION['inventario'])) {
        $_SESSION['inventario'] = [
            [
                "nombre" => "Horno Convector 10 Charolas",
                "categoria" => "Maquinaria",
                "stock_actual" => 1,
                "stock_minimo" => 1,
                "nota" => "Requiere servicio pronto"
            ],
            [
                "nombre" => "Charolas Zintro Perforadas",
                "categoria" => "Utensilios",
                "stock_actual" => 40,
                "stock_minimo" => 50,
                "nota" => "Mesa de armado"
            ]
        ];
    }

    /*
     * Migrar los productos de la sesión solo si la tabla
     *    está vacía. Así evitamos duplicarlos si ya hay datos.
     */
    $totalProductos = (int) $pdo
        ->query("SELECT COUNT(*) FROM inventario")
        ->fetchColumn();

    if ($totalProductos === 0 && !empty($_SESSION['inventario'])) {
        $pdo->beginTransaction();

        $sqlMigracion = $pdo->prepare(
            "INSERT INTO inventario
            (nombre, categoria, stock_actual, stock_minimo, nota)
            VALUES (:nombre, :categoria, :actual, :minimo, :nota)"
        );

        foreach ($_SESSION['inventario'] as $item) {
            $sqlMigracion->execute([
                ':nombre' => $item['nombre'],
                ':categoria' => $item['categoria'],
                ':actual' => $item['stock_actual'],
                ':minimo' => $item['stock_minimo'],
                ':nota' => $item['nota'] ?? ''
            ]);
        }

        $pdo->commit();
    }

    /* Guardar un producto nuevo en MySQL */
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        ($_POST['accion'] ?? '') === 'guardar'
    ) {
        $categoriasPermitidas = ['Maquinaria', 'Utensilios'];

        $nombre = trim($_POST['nombre'] ?? '');
        $categoria = $_POST['categoria'] ?? 'Maquinaria';

        $stockActual = filter_var(
            $_POST['stock_actual'] ?? null,
            FILTER_VALIDATE_INT
        );

        $stockMinimo = filter_var(
            $_POST['stock_minimo'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $nombre === '' ||
            !in_array($categoria, $categoriasPermitidas, true) ||
            $stockActual === false ||
            $stockActual === null ||
            $stockMinimo === false ||
            $stockMinimo === null ||
            $stockActual < 0 ||
            $stockMinimo < 0
        ) {
            http_response_code(400);
            exit('Error: revisa los datos del producto.');
        }

        $sqlGuardar = $pdo->prepare(
            "INSERT INTO inventario
            (nombre, categoria, stock_actual, stock_minimo, nota)
            VALUES (:nombre, :categoria, :actual, :minimo, :nota)"
        );

        $sqlGuardar->execute([
            ':nombre' => $nombre,
            ':categoria' => $categoria,
            ':actual' => $stockActual,
            ':minimo' => $stockMinimo,
            ':nota' => trim($_POST['nota'] ?? '')
        ]);

        header(
            "Location: ?vista=panel&accion_panel=ver_tablas&seccion="
            . urlencode($categoria)
        );
        exit;
    }
    
    /* Eliminar un producto de MySQL */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['accion'] ?? '') === 'eliminar'
) {
    $nombreEliminar = trim($_POST['nombre'] ?? '');
    $categoriaEliminar = $_POST['categoria'] ?? '';

    $categoriasPermitidas = ['Maquinaria', 'Utensilios'];

    if (
        $nombreEliminar !== '' &&
        in_array($categoriaEliminar, $categoriasPermitidas, true)
    ) {
        $stmt = $pdo->prepare(
            "DELETE FROM inventario
             WHERE nombre = :nombre
             AND categoria = :categoria
             LIMIT 1"
        );

        $stmt->execute([
            ':nombre' => $nombreEliminar,
            ':categoria' => $categoriaEliminar
        ]);
    }

    header("Location: ?vista=panel&accion_panel=eliminar_vista");
    exit;
}

/* Obtener los productos guardados en MySQL */
$inventario = $pdo->query(
    "SELECT * FROM inventario ORDER BY nombre ASC"
)->fetchAll();


} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    exit(
        'No se pudo conectar con la base de datos. '
        . 'Verifica que MySQL esté iniciado y que la base de datos '
        . 'bakestock exista.'
    );
}

$vista = $_GET['vista'] ?? 'bienvenida';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario - Panadería</title>
    <style>
        :root { 
            --cafe-oscuro: #3B1F0E;  /* Mocha */
            --cafe-claro:  #6B3A2A;  /* Brown */
            --crema:       #F5F0E8;  /* Ivory */
            --dorado:      #C9A87C;  /* Cream */
            --texto:       #2C1A0E;  /* Mocha oscuro */
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background-color: #F5F0E8; color: var(--texto); }
        

        .bienvenida-container h1 { font-size: 3.5rem; margin-bottom: 15px; font-weight: bold; letter-spacing: 2px; }
        .bienvenida-container p { font-size: 1.3rem; margin-bottom: 35px; color: #E6CCB2; }
        .btn-entrar { 
              width: 220px;
            height: 50px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(
                to right, #77530a, #ffd277, #77530a, #77530a, #ffd277, #77530a
            );
            background-size: 250%;
            background-position: left;
            color: #ffd277;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-position 1s, transform 0.2s;
            overflow: hidden;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: 0 auto;
            }
            .btn-entrar::before {
                position: absolute;
                content: "Entrar al Sistema →";
                color: #ffd277;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 97%;
                height: 90%;
                border-radius: 8px;
                background-color: rgba(0, 0, 0, 0.88);
                transition: background-color 0.5s;
                font-size: 1rem;
            }
            .btn-entrar:hover { background-position: right; }
            .btn-entrar:hover::before {
                background-color: rgba(35, 22, 8, 0.9);
            }

            .btn-entrar:active {
                transform: scale(0.95);
            }
         .bienvenida-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 35px 20px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(160deg, #3B1F0E, #6B3A2A, #A0522D, #C9A87C);
            color: #F5F0E8;
            text-align: center;
        }

/* CARRUSEL ELEGANTE: CAFÉ OSCURO Y DORADO*/

        .carrusel-fondo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            width: min(90%, 1000px);
            height: min(55vh, 420px);
            min-height: 250px;

            overflow: hidden;
            border-radius: 22px;
            border: 2px solid #C9A44C;

            z-index: 1;

            background-color: #24150F;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.50),
                0 0 18px rgba(201, 164, 76, 0.20);
        }

        /* Fotografías */
        .carrusel-fondo .slide-imagen {
            position: absolute;
            inset: 0;

            display: block;
            width: 100%;
            height: 100%;

            object-fit: cover;
            object-position: center;

            opacity: 0;
            transform: scale(1.04);

            transition:
                opacity 1.2s ease-in-out,
                transform 4.5s ease-in-out;

            will-change: opacity, transform;
        }

        /* Fotografía activa */
        .carrusel-fondo .slide-imagen.activo {
            opacity: 1;
            transform: scale(1);
        }

        /* Capa café para que el texto se lea mejor */
        .carrusel-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            width: min(90%, 1000px);
            height: min(55vh, 420px);
            min-height: 250px;

            border-radius: 22px;

            background: linear-gradient(
                135deg,
                rgba(29, 17, 12, 0.72),
                rgba(53, 31, 20, 0.42),
                rgba(22, 13, 9, 0.72)
            );

            z-index: 2;
            pointer-events: none;
        }

        /* Contenedor de flechas e indicadores */
        .carrusel-controles {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            width: min(90%, 1000px);
            height: min(55vh, 420px);
            min-height: 250px;

            z-index: 4;
            pointer-events: none;
        }

        /* Flechas de navegación */
        .carrusel-flecha {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);

            display: flex;
            justify-content: center;
            align-items: center;

            width: 48px;
            height: 48px;

            border: 1px solid rgba(231, 197, 112, 0.85);
            border-radius: 50%;

            background: rgba(38, 23, 15, 0.82);
            color: #F2D58A;

            font-size: 32px;
            line-height: 1;

            cursor: pointer;
            pointer-events: auto;

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);

            transition:
                background 0.25s ease,
                color 0.25s ease,
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .carrusel-flecha.anterior {
            left: 16px;
        }

        .carrusel-flecha.siguiente {
            right: 16px;
        }

        .carrusel-flecha:hover {
            background: #C9A44C;
            color: #24150F;

            transform: translateY(-50%) scale(1.08);

            box-shadow: 0 0 18px rgba(201, 164, 76, 0.4);
        }

        .carrusel-flecha:focus-visible,
        .carrusel-indicador:focus-visible {
            outline: 3px solid #F2D58A;
            outline-offset: 4px;
        }

        /* Indicadores inferiores */
        .carrusel-indicadores {
            position: absolute;
            left: 50%;
            bottom: 16px;
            transform: translateX(-50%);

            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;

            width: calc(100% - 110px);

            pointer-events: auto;
        }

        /* Cada indicador */
        .carrusel-indicador {
            width: 10px;
            height: 10px;
            padding: 0;

            border: 1px solid #E7C570;
            border-radius: 50%;

            background: rgba(255, 255, 255, 0.40);

            cursor: pointer;

            transition:
                width 0.3s ease,
                background 0.3s ease,
                border-radius 0.3s ease,
                box-shadow 0.3s ease;
        }

        /* Indicador seleccionado */
        .carrusel-indicador.activo {
            width: 28px;

            border-radius: 10px;
            background: #D4AF59;

            box-shadow: 0 0 10px rgba(212, 175, 89, 0.65);
        }

        /* Adaptación para celulares */
        @media (max-width: 600px) {
            .carrusel-fondo,
            .carrusel-overlay,
            .carrusel-controles {
                width: 92%;
                height: 42vh;
                min-height: 240px;
            }

        .carrusel-fondo,
                .carrusel-overlay {
                border-radius: 16px;
            }

            .carrusel-flecha {
                width: 38px;
                height: 38px;
                font-size: 27px;
            }

            .carrusel-flecha.anterior {
                left: 9px;
            }

            .carrusel-flecha.siguiente {
                right: 9px;
            }

            .carrusel-indicadores {
                bottom: 12px;
                gap: 8px;
                width: calc(100% - 80px);
            }

            .carrusel-indicador {
                width: 8px;
                height: 8px;
            }

            .carrusel-indicador.activo {
                width: 22px;
            }
        }

        /* Respeta la preferencia de movimiento reducido */
        @media (prefers-reduced-motion: reduce) {
            .carrusel-fondo .slide-imagen,
            .carrusel-flecha,
            .carrusel-indicador {
                transition: none;
            }
        }
        
            /* Texto y botón siempre delante del carrusel */
            .bienvenida-contenido {
                position: relative;
                z-index: 3;
                width: 100%;
                max-width: 900px;
                padding: 20px;
            }

            /* El carrusel permanece detrás del contenido */
            .carrusel-fondo {
                z-index: 1;
            }

            /* La capa oscura queda entre las fotos y el texto */
            .carrusel-overlay {
                z-index: 2;
            }

        /* Flechas e indicadores por encima de las fotos */
        .carrusel-controles {
            z-index: 4;
            }
        
    </style>
</head>
<body>

<?php if ($vista === 'bienvenida'): ?>
    <div class="bienvenida-container" onclick="iniciarMusicaPorInteraccion()">
        <audio id="musicaPanaderia" loop src="musicaPanaderia.mp3"></audio>

        <div class="bienvenida-contenido">
        <h1>¡BakeStock!</h1>
        <p>Sistema para el Control de Maquinaria y Utensilios</p>
        <p style="font-size: 0.9rem; color:#D4A373; 
        margin-top: -20px; margin-bottom: 20px;"> Haz un clic en cualquier parte de la pantalla para activar el ambiente</p>

        <a href="?vista=panel" class="btn-entrar">Entrar al Sistema →</a>

        <div style="display:flex; gap:16px; justify-content:center; margin-top:20px;">
            <a href="?vista=panel&accion_panel=ver_tablas&seccion=Maquinaria" 
               style="background:linear-gradient(90deg,#3B1F0E,#6B3A2A); color:#F5F0E8; padding:12px 24px; border-radius:20px; text-decoration:none; font-weight:700; letter-spacing:1px; border:2px solid #C9A87C; transition:all 0.3s ease;">
               ⚙️ Maquinaria
            </a>
            <a href="?vista=panel&accion_panel=ver_tablas&seccion=Utensilios"
               style="background:linear-gradient(90deg,#3B1F0E,#6B3A2A); color:#F5F0E8; padding:12px 24px; border-radius:20px; text-decoration:none; font-weight:700; letter-spacing:1px; border:2px solid #C9A87C; transition:all 0.3s ease;">
                Utensilios
            </a>
        </div>
        </div>
        
        <div class="carrusel-fondo">
            <img class="slide-imagen activo" src="pan1.jfif">
            <img class="slide-imagen" src="pan2.jfif">
            <img class="slide-imagen" src="pan3.jfif">
            <img class="slide-imagen" src="pan4.jfif">
            <img class="slide-imagen" src="pan5.jfif">
            <img class="slide-imagen" src="pan6.jfif">
            <img class="slide-imagen" src="pan7.jfif">
        </div>
        <!-- Capa oscura protectora de texto -->
        <div class="carrusel-overlay"></div>

        <div class="carrusel-controles">
            <button
                type="button"
                class="carrusel-flecha anterior"
                aria-label="Mostrar imagen anterior">
                &#10094;
            </button>

            <button
            type="button"
            class="carrusel-flecha siguiente"
            aria-label="Mostrar imagen siguiente">
            &#10095;
        </button>

    <!-- Indicadores de las siete imágenes -->
    <div class="carrusel-indicadores">

        <button type="button"
            class="carrusel-indicador activo"
            data-slide="0"
            aria-label="Mostrar imagen 1"
            aria-pressed="true"></button>

        <button type="button"
            class="carrusel-indicador"
            data-slide="1"
            aria-label="Mostrar imagen 2"
            aria-pressed="false"></button>

        <button type="button"
            class="carrusel-indicador"
            data-slide="2"
            aria-label="Mostrar imagen 3"
            aria-pressed="false"></button>

        <button type="button"
            class="carrusel-indicador"
            data-slide="3"
            aria-label="Mostrar imagen 4"
            aria-pressed="false"></button>

        <button type="button"
            class="carrusel-indicador"
            data-slide="4"
            aria-label="Mostrar imagen 5"
            aria-pressed="false"></button>

        <button type="button"
            class="carrusel-indicador"
            data-slide="5"
            aria-label="Mostrar imagen 6"
            aria-pressed="false"></button>

        <button type="button"
            class="carrusel-indicador"
            data-slide="6"
            aria-label="Mostrar imagen 7"
            aria-pressed="false"></button>

    </div>
</div>
        </div>
    </div>
        
<?php endif; ?>

<?php 
if ($vista === 'panel'): 
    $subVista = isset($_GET['accion_panel']) ? $_GET['accion_panel'] : 'ver_tablas';
    $categoriasPermitidas = ['Maquinaria', 'Utensilios'];
    $seccionActual = $_GET['seccion'] ?? 'Maquinaria';
    if (!in_array($seccionActual, $categoriasPermitidas, true)) {
        $seccionActual = 'Maquinaria';
    }
    function obtenerAlertaStock($actual, $minimo) {
        if ($actual == 0) { 
            return '<span class="badge red">⚠️ SIN STOCK</span>';
        }
        if ($actual < $minimo) {
            return '<span class="badge yellow">🟡 Stock Bajo</span>';
        }
        return '<span class="badge green">✅ OK</span>';
    }
?>
    <style>
        .layout { display: flex; min-height: 100vh; }
        
        /* Menú Lateral Izquierdo */
        .sidebar { width: 280px; background: linear-gradient(180deg, #3B1F0E, #6B3A2A); color: #F5F0E8; padding: 25px 15px; display: flex; flex-direction: column; gap: 20px; }
        .sidebar h2 { font-size: 1.4rem; border-bottom: 2px solid #C9A87C; padding-bottom: 10px; margin-bottom: 10px; text-align: center; color: #C9A87C; }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .sidebar-menu a { color: #F5F0E8; text-decoration: none; padding: 12px 15px; border-radius: 8px; display: block; font-weight: 500; transition: 0.2s; }
        .sidebar-menu a:hover { background-color: #A0522D; color: #F5F0E8; padding-left: 20px; }
        .sidebar-menu a { color: #E6CCB2; text-decoration: none; padding: 12px 15px; border-radius: 8px; display: block; font-weight: 500; transition: 0.2s; }
        .sidebar-menu a:hover { background-color: var(--cafe-claro); color: white; padding-left: 20px; }
        
        /* Contenedor de Tablas */
        .main-content { flex: 1; padding: 40px; background-color: #F5F0E8; }
        .header-panel { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #C9A87C; padding-bottom: 15px; }
        .header-panel h1 { color: #3B1F0E; }
        
        .seccion-inventario { margin-bottom: 40px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .seccion-inventario h3 { color: var(--cafe-claro); margin-bottom: 15px; font-size: 1.3rem; border-left: 4px solid var(--dorado); padding-left: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #E6CCB2; font-size: 0.95rem; }
        th { background-color: #F4EAE1; color: var(--cafe-oscuro); }
        
        /* Badges del Semáforo */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; }
        .badge.green { background-color: #D4EDDA; color: #155724; }
        .badge.yellow { background-color: #FFF3CD; color: #856404; }
        .badge.red { background-color: #F8D7DA; color: #721C24; }
        
        .btn-volver { background-color: #A68A64; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-size: 0.9rem; display: inline-block; }

            @media print {
                /* Ocultar menú, barra de búsqueda y botones */
                .sidebar,
                .buscador-container,
                #buscadorInput,
                .btn-volver {
                    display: none !important;
                }

                /* Usar todo el ancho de la hoja */
                .main-content {
                    padding: 0 !important;
                    margin: 0 !important;
                    background-color: white !important;
                    width: 100% !important;
                }

                /* Ocultar el botón de imprimir */
                .header-panel button {
                    display: none !important;
                }

                /* Imprimir el contenido con texto legible */
                body,
                table,
                td,
                th {
                    color: #000 !important;
                    background-color: white !important;
                }

                th {
                    background-color: #eee !important;
                    border-bottom: 2px solid #333 !important;
                }
            }
                /* Mostrar las tablas tanto en pantalla como al imprimir */
                .tabla-para-imprimir {
                    display: block !important;
                    width: 100%;
                    overflow-x: auto;
                }

                /* Al imprimir: ocultar tarjetas y mostrar tablas */
                @media print {
                    .tabla-para-imprimir {
                        display: block !important;
                    }

                    .tabla-para-imprimir table {
                        display: table !important;
                        width: 100%;
                        border-collapse: collapse;
                    }
                }
    </style>
    <div class="layout">
        <aside class="sidebar">
            <h2>🥖 Pan Panel</h2>
            <ul class="sidebar-menu">
    <li><a href="?vista=panel&accion_panel=ver_tablas&seccion=Utensilios" style="<?php echo ($seccionActual === 'Utensilios' && $subVista === 'ver_tablas') ? 'background-color: var(--cafe-claro); color: white;' : ''; ?>">🛠️ Ver Utensilios</a></li> 

    <li style="margin-top: 15px; font-size: 0.85rem; color: var(--dorado); font-weight: bold; padding-left: 15px; letter-spacing: 1px;">AÑADIR AL INVENTARIO</li>
    <li><a href="?vista=panel&accion_panel=agregar&cat=Utensilios">➕ Agregar Utensilios</a></li>

    <!-- Botón con opciones de Utensilios -->
    <li style="margin-top: 15px;">
        <button onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'" 
            style="width:100%; background: linear-gradient(90deg,#3B1F0E,#6B3A2A); color:#F5F0E8; border: 1px solid #C9A87C; padding:12px 15px; border-radius:8px; font-weight:700; cursor:pointer; text-align:left; font-size:0.95rem;">
            🛠️ Utensilios ▾
        </button>
        <ul style="display:none; list-style:none; background:rgba(59,31,14,0.15); border-radius:8px; margin-top:5px; padding:5px;">
            <li><a href="?vista=panel&accion_panel=ver_tablas&seccion=Medicion" style="color:#F5F0E8; display:block; padding:10px 15px; border-radius:6px; text-decoration:none; transition:0.2s;">⚖️ Medición y Pesado</a></li>
            <li><a href="?vista=panel&accion_panel=ver_tablas&seccion=Mezcla" style="color:#F5F0E8; display:block; padding:10px 15px; border-radius:6px; text-decoration:none; transition:0.2s;">🥣 Mezcla y Preparación</a></li>
            <li><a href="?vista=panel&accion_panel=ver_tablas&seccion=Corte" style="color:#F5F0E8; display:block; padding:10px 15px; border-radius:6px; text-decoration:none; transition:0.2s;">🔪 Corte, División y Formado</a></li>
            <li><a href="?vista=panel&accion_panel=ver_tablas&seccion=Horneado" style="color:#F5F0E8; display:block; padding:10px 15px; border-radius:6px; text-decoration:none; transition:0.2s;">🔥 Horneado y Reposo</a></li>
        </ul>
    </li>

    <li style="margin-top: 15px; font-size: 0.85rem; color: var(--dorado); font-weight: bold; padding-left: 15px; letter-spacing: 1px;">ACCIONES CRÍTICAS</li>
    <li><a href="?vista=panel&accion_panel=eliminar_vista" style="color: #D90429;">🗑️ Eliminar Producto</a></li>
</ul>

            <div style="margin-top: auto; text-align: center;">
                <a href="?vista=bienvenida" class="btn-volver" style="background-color: transparent; border: 1px solid #E6CCB2; color: #E6CCB2;">Salir a Portada</a>
            </div>
        </aside>
        <main class="main-content">
            <?php if ($subVista === 'ver_tablas'): ?>
                <div class="header-panel">
                    <!--cambia el titulo segun la ventana -->
                    <h1>Control de <?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <!-- P Boton de imprimir -->
                    <button onclick="window.print()" style="background-color: var(--cafe-oscuro); color: white; padding: 10px 20px; border: 2px solid var(--dorado); border-radius: 8px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; font-family: inherit;">
                        🖨️ Imprimir Hoja
                    </button>
                </div>

                <!-- Buscador que solo afectara a esta ventana activa -->
                <div style="margin-bottom: 25px; width: 100%;">
                    <input type="text" id="buscadorInput" onkeyup="filtrarInventario()" placeholder="🔍 Escribe para buscar en <?php echo htmlspecialchars(strtolower($seccionActual), ENT_QUOTES, 'UTF-8'); ?> en tiempo real..." style="width: 100%; padding: 12px 20px; border: 2px solid var(--dorado); border-radius: 25px; font-size: 1rem; outline: none; box-shadow: 0 2px 5px rgba(0,0,0,0.05); font-family: inherit;">
                </div>

                <div class="seccion-inventario contenedor-tabla-categoria tabla-para-imprimir">
                    <h3>Listado Activo de <?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre del Artículo</th>
                                <th>Stock Actual</th>
                                <th>Mínimo Requerido</th>
                                <th>Estado</th>
                                <th>Notas / Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hayProductos = false;
                            foreach ($inventario as $indice => $item): 
                                // OJO: Si el artículo no pertenece a la ventana/sección activa, lo ignoramos
                                if ($item['categoria'] !== $seccionActual) continue;
                                $hayProductos = true;?>
                                <tr class="fila-producto">
                                    <td class="nombre-producto">
                                    <?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td>
                                        <span style="font-weight:bold; display:inline-block; width:25px; text-align:center;"><?php echo $item['stock_actual']; ?></span>
                                    </td>
                                    <td><?php echo $item['stock_minimo']; ?></td>
                                    <td><?php echo obtenerAlertaStock($item['stock_actual'], $item['stock_minimo']); ?></td>
                                    <td><i style="color: #666;"><?php echo htmlspecialchars($item['nota'], ENT_QUOTES, 'UTF-8'); ?></i></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <tr class="mensaje-no-existe" style="display: none;">
                                <td colspan="5" style="text-align: center; color: #D90429; font-weight: bold; padding: 15px;">
                                    ⚠️ No se encontraron coincidencias en esta ventana.
                                </td>
                            </tr>
                            <?php if (!$hayProductos): ?>
                                <tr><td colspan="5" style="text-align: center; color: #999;">No hay registros cargados en esta área.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($subVista === 'agregar'): 
                // Detectamos qué categoría seleccionó el usuario desde el menú izquierdo
                $categoriasPermitidas = ['Maquinaria', 'Utensilios'];
                $categoriaSeleccionada = $_GET['cat'] ?? 'Maquinaria';
                if (!in_array($categoriaSeleccionada, $categoriasPermitidas, true)) {
                    $categoriaSeleccionada = 'Maquinaria';
                }
            ?>
                <!-- VISTA: FORMULARIO DINÁMICO PARA AGREGAR ARTÍCULOS -->
                <div class="header-panel">
                    <h1>Añadir al Inventario</h1>
                    <a href="?vista=panel&accion_panel=ver_tablas" class="btn-volver">← Cancelar y Regresar</a>
                </div>

                <style>
                    .form-grupo { margin-bottom: 20px; display: flex; flex-direction: column; gap: 5px; }
                    .form-grupo label { font-weight: bold; color: var(--cafe-oscuro); }
                    .form-grupo input { padding: 12px; border: 1px solid #CBA36E; border-radius: 6px; font-size: 1rem; background-color: white; }
                    .btn-guardar { background-color: var(--cafe-oscuro); color: white; padding: 14px 20px; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; transition: 0.2s; }
                    .btn-guardar:hover { background-color: var(--cafe-claro); }
                </style>

                <div class="seccion-inventario" style="max-width: 600px; margin: 0 auto;">
                    <h3 style="margin-bottom: 20px;">Registrar en el área de: <span style="color: var(--cafe-oscuro); text-transform: uppercase;"><?php echo htmlspecialchars($categoriaSeleccionada, ENT_QUOTES, 'UTF-8'); ?></span></h3>
                    
                    <!-- Formulario que envía los datos mediante método POST al bloque que procesará el guardado -->
                    <form action="?vista=panel" method="POST">
                        <!-- Campos ocultos para enviar la acción y la categoría automática -->
                        <input type="hidden" name="accion" value="guardar">
                        <input type="hidden" name="categoria" value="<?php echo htmlspecialchars( $categoriaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="form-grupo">
                            <label for="nombre">Nombre del Artículo / Equipo:</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Ej. Amasadora, Charolas..." required>
                        </div>

                        <div class="form-grupo">
                            <label for="stock_actual">Cantidad Actual en Existencia:</label>
                            <input type="number" id="stock_actual" name="stock_actual" min="0" placeholder="0" required>
                        </div>

                        <div class="form-grupo">
                            <label for="stock_minimo">Stock Mínimo (Alerta de Resurtido):</label>
                            <input type="number" id="stock_minimo" name="stock_minimo" min="0" placeholder="5" required>
                        </div>



                        <div class="form-grupo">
                            <label for="nota">Notas, Ubicación o Estado:</label>
                            <input type="text" id="nota" name="nota" placeholder="Ej. Estante A, Requiere mantenimiento...">
                        </div>

                        <button type="submit" class="btn-guardar">💾 Guardar en el Inventario</button>
                    </form>
                </div>
            <?php endif; ?>
            <?php if ($subVista === 'eliminar_vista'): ?>
                <div class="header-panel">
                    <h1>Remover Productos del Inventario</h1>
                    <a href="?vista=panel&accion_panel=ver_tablas" class="btn-volver">← Volver al inventario</a>
                </div>

                <div class="seccion-inventario">
                    <h3 style="color: #D90429; border-left: 4px solid #D90429; padding-left: 10px; margin-bottom: 15px;">Lista de Control de Baja</h3>
                    <p style="margin-bottom: 20px; font-size: 0.95rem; color: #666;">Haz clic en el botón de eliminar correspondiente al artículo que deseas dar de baja permanentemente.</p>

                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #F4EAE1; color: var(--cafe-oscuro);">
                                <th style="padding: 12px;">Categoría</th>
                                <th style="padding: 12px;">Nombre del Artículo</th>
                                <th style="padding: 12px;">Stock</th>
                                <th style="padding: 12px; text-align: center;">Acción Crítica</th>
                            </tr>
                            </thead>
                        <tbody>
                            <?php if (count($inventario) > 0): ?>
                                <?php foreach ($inventario as $indice => $item): ?>
                                    <tr style="border-bottom: 1px solid #E6CCB2;">
                                        <td style="padding: 12px;">
                                            <?php echo htmlspecialchars($item['categoria'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 12px;">
                                            <strong> <?php echo htmlspecialchars( $item['nombre'], ENT_QUOTES, 'UTF-8'); ?> </strong>
                                        </td>
                                        <td style="padding: 12px;"><?php echo $item['stock_actual']; ?> unidades</td>
                                        <td style="text-align: center; padding: 12px;">
                                    <form method="POST" action="?vista=panel&accion_panel=eliminar_vista"
                                        style="display: inline;"
                                        onsubmit="return confirm('¿Estás segura de que deseas eliminar este producto permanentemente?');">

                                        <input type="hidden" name="accion" value="eliminar">

                                        <input
                                            type="hidden"
                                            name="nombre"
                                            value="<?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?>">

                                        <input
                                            type="hidden"
                                            name="categoria"
                                            value="<?php echo htmlspecialchars($item['categoria'], ENT_QUOTES, 'UTF-8'); ?>">

                                        <button
                                            type="submit"
                                            style="background-color: #D90429; color: white; padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; font-size: 0.85rem; cursor: pointer;">
                                            🗑️ Borrar
                                            </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align: center; color: #999; padding: 30px;">No hay ningún producto registrado en el sistema.</td></tr>
                            <?php endif; ?>
                        </tbody>
                            </table>
                </div>
                 <?php endif; ?>
        </main>
    </div>
        <?php endif; // Cierre del panel principal ?>
<script>
    function filtrarInventario() {
    const input = document.getElementById("buscadorInput");

    if (!input) {
        return;
    }

    const filtro = input.value.trim().toLowerCase();

    const tablas = document.querySelectorAll(
        ".contenedor-tabla-categoria"
    );

    tablas.forEach(function (bloque) {
        const filas = bloque.querySelectorAll(".fila-producto");
        const mensaje = bloque.querySelector(".mensaje-no-existe");
        let coincidencias = 0;

        filas.forEach(function (fila) {
            const celda = fila.querySelector(".nombre-producto");
            const nombre = celda
                ? celda.textContent.toLowerCase()
                : "";

            const coincide = nombre.includes(filtro);

            fila.style.display = coincide ? "" : "none";

            if (coincide) {
                coincidencias++;
            }
        });

        if (mensaje) {
            mensaje.style.display =
                filtro !== "" && coincidencias === 0
                    ? ""
                    : "none";
        }
    });
}
        </script>
        <script>
        function iniciarMusicaPorInteraccion() {
            const musica = document.getElementById("musicaPanaderia");
            
            if (musica && musica.paused) {
        musica.play().catch(function(error) {
            console.log("El navegador bloqueó el audio temporalmente: ", error);
        });
    }
}

// Opcional: Si el usuario da clic directo en el botón de entrar, intentamos reproducir una milésima de segundo antes de cambiar de página
document.querySelector('.btn-entrar')?.addEventListener('click', function() {
    iniciarMusicaPorInteraccion();
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll(
        ".carrusel-fondo .slide-imagen"
    );

    const indicadores = document.querySelectorAll(
        ".carrusel-indicador"
    );

    const botonAnterior = document.querySelector(
        ".carrusel-flecha.anterior"
    );

    const botonSiguiente = document.querySelector(
        ".carrusel-flecha.siguiente"
    );

    const zonaImagenes = document.querySelector(".carrusel-fondo");
    const zonaControles = document.querySelector(".carrusel-controles");

    // Si estamos en el panel del inventario, no hay carrusel.
    if (slides.length === 0) return;

    let indiceSlide = 0;
    let temporizadorCarrusel = null;
    const intervaloCarrusel = 4000;

    function mostrarSlide(nuevoIndice) {
        indiceSlide =
            (nuevoIndice + slides.length) % slides.length;

        slides.forEach(function (slide, indice) {
            const activo = indice === indiceSlide;

            slide.classList.toggle("activo", activo);
            slide.setAttribute(
                "aria-hidden",
                activo ? "false" : "true"
            );
        });

        indicadores.forEach(function (indicador, indice) {
            const activo = indice === indiceSlide;

            indicador.classList.toggle("activo", activo);
            indicador.setAttribute(
                "aria-pressed",
                activo ? "true" : "false"
            );
        });
    }

    function cambiarImagenCarrusel() {
        mostrarSlide(indiceSlide + 1);
    }

    function detenerTemporizadorCarrusel() {
        if (temporizadorCarrusel !== null) {
            clearInterval(temporizadorCarrusel);
            temporizadorCarrusel = null;
        }
    }

    function iniciarTemporizadorCarrusel() {
        if (slides.length <= 1) return;
        if (temporizadorCarrusel !== null) return;

        temporizadorCarrusel = setInterval(
            cambiarImagenCarrusel,
            intervaloCarrusel
        );
    }

    function reiniciarTemporizadorCarrusel() {
        detenerTemporizadorCarrusel();
        iniciarTemporizadorCarrusel();
    }

    if (botonAnterior) {
        botonAnterior.addEventListener("click", function (evento) {
            evento.stopPropagation();
            mostrarSlide(indiceSlide - 1);
            reiniciarTemporizadorCarrusel();
        });
    }

    if (botonSiguiente) {
        botonSiguiente.addEventListener("click", function (evento) {
            evento.stopPropagation();
            mostrarSlide(indiceSlide + 1);
            reiniciarTemporizadorCarrusel();
        });
    }

    indicadores.forEach(function (indicador) {
        indicador.addEventListener("click", function (evento) {
            evento.stopPropagation();

            const destino = Number(
                indicador.getAttribute("data-slide")
            );

            if (
                Number.isInteger(destino) &&
                destino >= 0 &&
                destino < slides.length
            ) {
                mostrarSlide(destino);
                reiniciarTemporizadorCarrusel();
            }
        });
    });

    // Pausar el cambio automático mientras se usan los controles.
    [zonaImagenes, zonaControles].forEach(function (zona) {
        if (!zona) return;

        zona.addEventListener(
            "mouseenter",
            detenerTemporizadorCarrusel
        );

        zona.addEventListener(
            "mouseleave",
            iniciarTemporizadorCarrusel
        );

        zona.addEventListener(
            "focusin",
            detenerTemporizadorCarrusel
        );

        zona.addEventListener("focusout", function (evento) {
            if (!zona.contains(evento.relatedTarget)) {
                iniciarTemporizadorCarrusel();
            }
        });
    });

    // Mostrar la primera imagen e iniciar el cambio automático.
    mostrarSlide(0);
    iniciarTemporizadorCarrusel();
});
</script>

</body>
</html>

