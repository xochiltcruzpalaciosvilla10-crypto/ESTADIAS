<?php
session_start();
/* CERRAR SESIÓN */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['tipo_auth'] ?? '') === 'logout'
) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit;
}

/* COMPROBAR SI EL USUARIO INICIÓ SESIÓN */
if (empty($_SESSION['usuario'])) {
    require __DIR__ . '/login.php';
    exit;
}

/* Conexión con la base de datos */
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=bakestock;charset=utf8mb4", "root", ""
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    /* FUNCIÓN PARA REGISTRAR EVENTOS */
function registrarEvento($pdo, $accion, $descripcion) {

    $usuario = $_SESSION['usuario'] ?? 'Desconocido';

    $sqlEvento = $pdo->prepare("
        INSERT INTO eventos
        (usuario, accion, descripcion, fecha_hora)
        VALUES (:usuario, :accion, :descripcion, NOW())
    ");

    $sqlEvento->execute([
        ':usuario' => $usuario,
        ':accion' => $accion,
        ':descripcion' => $descripcion
    ]);
}

    /* Conservar los productos que todavía existan en la sesión */
    if (!isset($_SESSION['inventario'])) {
        $_SESSION['inventario'] = [
            [
                "nombre" => "Horno Convector 10 Charolas",
                "categoria" => "Maquinaria",
                "stock_actual" => 1,
                "stock_minimo" => 1,
                "nota" => "Requiere servicio pronto"
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
        $categoriasPermitidas = ['Maquinaria'];

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
        registrarEvento(
    $pdo,
    'AGREGAR',
    "Agregó el producto: $nombre. Categoría: $categoria."
);

        header(
            "Location: ?vista=panel&accion_panel=ver_tablas&seccion="
            . urlencode($categoria)
        );
        exit;
    }
    

/* EDITAR UN PRODUCTO DE MYSQL */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['accion'] ?? '') === 'editar'
) {
    $nombreAnterior = trim($_POST['nombre_anterior'] ?? '');
    $categoria = $_POST['categoria'] ?? '';

    $nombre = trim($_POST['nombre'] ?? '');

    $categoriasPermitidas = ['Maquinaria'];

    $stockActual = filter_var(
        $_POST['stock_actual'] ?? null,
        FILTER_VALIDATE_INT
    );

    $stockMinimo = filter_var(
        $_POST['stock_minimo'] ?? null,
        FILTER_VALIDATE_INT
    );

    $nota = trim($_POST['nota'] ?? '');

    if (
        $nombreAnterior === '' ||
        $nombre === '' ||
        !in_array($categoria, $categoriasPermitidas, true) ||
        $stockActual === false ||
        $stockMinimo === false ||
        $stockActual < 0 ||
        $stockMinimo < 0
    ) {
        http_response_code(400);
        exit('Error: revisa los datos del producto.');
    }

    $sqlEditar = $pdo->prepare(
        "UPDATE inventario
         SET nombre = :nombre,
             stock_actual = :actual,
             stock_minimo = :minimo,
             nota = :nota
         WHERE nombre = :nombreAnterior
         AND categoria = :categoria
         LIMIT 1"
    );

    $sqlEditar->execute([
        ':nombre' => $nombre,
        ':actual' => $stockActual,
        ':minimo' => $stockMinimo,
        ':nota' => $nota,
        ':nombreAnterior' => $nombreAnterior,
        ':categoria' => $categoria
    ]);
    registrarEvento(
    $pdo,
    'EDITAR',
    "Editó el producto: $nombreAnterior. Nuevo nombre: $nombre."
);

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

    $categoriasPermitidas = ['Maquinaria'];

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
        if ($stmt->rowCount() > 0) {
    registrarEvento(
        $pdo,
        'ELIMINAR',
        "Eliminó el producto: $nombreEliminar. Categoría: $categoriaEliminar."
    );
}
    }

    header("Location: ?vista=panel&accion_panel=eliminar_vista");
    exit;
}

/* ELIMINAR UN EVENTO INDIVIDUAL */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['accion'] ?? '') === 'eliminar_evento'
) {
    $idEvento = filter_var(
        $_POST['id_evento'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($idEvento !== false && $idEvento !== null && $idEvento > 0) {
        $eliminarEvento = $pdo->prepare(
            "DELETE FROM eventos WHERE id = :id LIMIT 1"
        );

        $eliminarEvento->execute([
            ':id' => $idEvento
        ]);
    }

    header("Location: ?vista=panel&accion_panel=eventos");
    exit;
}

/* ELIMINAR TODO EL HISTORIAL */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['accion'] ?? '') === 'limpiar_eventos'
) {
    $pdo->exec("DELETE FROM eventos");

    header("Location: ?vista=panel&accion_panel=eventos");
    exit;
}


/* Obtener los productos guardados en MySQL */
$inventario = $pdo->query(
    "SELECT * FROM inventario ORDER BY nombre ASC"
)->fetchAll();


/* RESUMEN DEL INVENTARIO */
$totalProductos = count($inventario);
$productosStockBajo = 0;
$productosSinStock = 0;

foreach ($inventario as $producto) {
    $actual = (int) ($producto['stock_actual'] ?? 0);
    $minimo = (int) ($producto['stock_minimo'] ?? 0);

    if ($actual === 0) {
        $productosSinStock++;
    } elseif ($actual < $minimo) {
        $productosStockBajo++;
    }
}



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

$vistasPermitidas = ['bienvenida', 'panel'];

if (!in_array($vista, $vistasPermitidas, true)) {
    $vista = 'bienvenida';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <title>Sistema de Inventario - Panadería</title>
    <style>
        .titulo-maquinaria {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    font-weight: 800;
    color: #4A3525;
    letter-spacing: 1px;
    text-align: center;
}
        .titulo-eventos {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 800;
            color: #4A3525;
            letter-spacing: 1px;
            text-align: center;
        }
        .titulo-maqui {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 800;
            color: #4A3525;
            letter-spacing: 1px;
            text-align: center;
        }
        .titulo-eliminar {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 800;
            color: #4A3525;
            letter-spacing: 1px;
            text-align: center;
        }
        :root { 
            --cafe-oscuro: #4A3525; 
            --cafe-claro: #8C6239; 
            --crema: #FDFBF7; 
            --dorado: #D4A373; 
            --texto: #333; 
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background-color: #F4EAE1; color: var(--texto); }
        

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
            position: relative; /* Obligatorio para que funcione el fondo absoluto */
            overflow: hidden;
            background-color: var(--cafe-oscuro);
            color: white;
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
            
/* CURSOR PERSONALIZADO: DESTELLOS DORADOS */
@media (hover: hover) and (pointer: fine) {
    .bienvenida-container,
    .bienvenida-container * {
        cursor: none !important;
    }

    .cursor-dorado {
        position: fixed;
        top: 0;
        left: 0;
        width: 28px;
        height: 28px;
        z-index: 99999;
        pointer-events: none;
        opacity: 0;
        transform: translate(-50%, -50%);
        transition: opacity 0.15s ease;
    }

    .cursor-dorado.visible {
        opacity: 1;
    }

    .cursor-dorado .estrella {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 22px;
        height: 22px;
        background: linear-gradient(
            135deg,
            #fff4bd,
            #ffd45e,
            #c58a18
        );
        clip-path: polygon(
            50% 0%, 62% 36%, 100% 50%,
            62% 64%, 50% 100%, 38% 64%,
            0% 50%, 38% 36%
        );
        filter: drop-shadow(0 0 5px #f5c451);
    }

    .cursor-dorado .brillo {
        position: absolute;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #fff0a6;
        box-shadow: 0 0 7px #ffd45e;
    }

    .cursor-dorado .brillo-uno {
        top: 0;
        right: 0;
    }

    .cursor-dorado .brillo-dos {
        bottom: 0;
        left: 0;
        width: 4px;
        height: 4px;
    }
}

/* ===== BAKestock: BORDE DORADO BRILLANTE ===== */

.sidebar {
    position: relative;
    isolation: isolate;
    border: 2px solid #8c6239;
    border-radius: 0 16px 16px 0;
}

/* Marco dorado con una luz que recorre el contorno */
.sidebar::before {
    content: "";
    position: absolute;
    inset: -2px;
    border-radius: inherit;
    padding: 2px;
    pointer-events: none;
    z-index: 20;

    background: conic-gradient(
        from var(--angulo-borde, 0deg),
        transparent 0deg,
        transparent 280deg,
        #d4a373 300deg,
        #fff4c2 315deg,
        #ffd45e 330deg,
        #d4a373 340deg,
        transparent 350deg,
        transparent 360deg
    );

    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;

    animation: giroBordeDorado 2.5s linear infinite;
}

/* Animar el ángulo del brillo, no girar el menú */
@property --angulo-borde {
    syntax: "<angle>";
    initial-value: 0deg;
    inherits: false;
}

@keyframes giroBordeDorado {
    to {
        --angulo-borde: 360deg;
    }
}

/* Resplandor cálido */
.sidebar::after {
    content: "";
    position: absolute;
    inset: -3px;
    border: 1px solid rgba(255, 212, 94, 0.35);
    border-radius: inherit;
    pointer-events: none;
    z-index: 19;

    animation: haloDoradoRapido 1.5s ease-in-out infinite alternate;
}

@keyframes haloDoradoRapido {
    from {
        box-shadow: 0 0 3px rgba(212, 163, 115, 0.2);
    }

    to {
        box-shadow:
            0 0 10px rgba(255, 212, 94, 0.55),
            0 0 18px rgba(212, 163, 115, 0.25);
    }
}

/* Accesibilidad */
@media (prefers-reduced-motion: reduce) {
    .sidebar::before,
    .sidebar::after {
        animation: none;
    }

    .sidebar::before {
        background: #d4a373;
    }
}

/* ==========================================
   BAKestock - TABLAS ELEGANTES DORADAS
   Solo para el panel de inventario
   ========================================== */

.main-content {
    --cafe-oscuro: #4A3525;
    --cafe-claro: #8C6239;
    --crema: #FDFBF7;
    --dorado: #D4A373;
    --beige: #F4EAE1;
    --dorado-suave: #E8D0AE;
}

/* CONTENEDOR DE LA TABLA */
.main-content table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: var(--crema);
    border: 1px solid var(--dorado);
    border-radius: 12px;
    box-shadow: 0 5px 16px rgba(74, 53, 37, 0.08);
    overflow: hidden;
}

/* ENCABEZADOS CAFÉ CON LETRAS CREMA */
.main-content table thead th {
    background: var(--cafe-oscuro);
    color: var(--crema);
    padding: 15px 16px;
    text-align: left;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.3px;
    border-bottom: 3px solid var(--dorado);
}

/* CELDAS */
.main-content table tbody td {
    padding: 13px 16px;
    color: var(--cafe-oscuro);
    border-bottom: 1px solid var(--dorado-suave);
    vertical-align: middle;
    transition: background-color 0.2s ease;
}

/* FILAS ALTERNADAS: CREMA Y BEIGE */
.main-content table tbody tr:nth-child(odd) {
    background: var(--crema);
}

.main-content table tbody tr:nth-child(even) {
    background: var(--beige);
}

/* RESALTADO SUAVE AL PASAR EL CURSOR */
.main-content table tbody tr:hover {
    background: #F1DFC5;
}

/* ÚLTIMA FILA */
.main-content table tbody tr:last-child td {
    border-bottom: none;
}

/* ==========================================
   BOTONES CAFÉ CON DETALLES DORADOS
   ========================================== */

.main-content button,
.main-content .btn,
.main-content input[type="submit"],
.main-content input[type="button"] {
    background: var(--cafe-oscuro);
    color: var(--crema);
    border: 1px solid var(--dorado);
    border-radius: 8px;
    padding: 10px 16px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;

    transition:
        background-color 0.25s ease,
        color 0.25s ease,
        border-color 0.25s ease,
        box-shadow 0.25s ease,
        transform 0.2s ease;
}

/* HOVER DORADO ELEGANTE */
.main-content button:hover,
.main-content .btn:hover,
.main-content input[type="submit"]:hover,
.main-content input[type="button"]:hover {
    background: var(--dorado);
    color: var(--cafe-oscuro);
    border-color: var(--cafe-claro);
    box-shadow: 0 4px 10px rgba(140, 98, 57, 0.18);
    transform: translateY(-1px);
}

/* AL PRESIONAR */
.main-content button:active,
.main-content .btn:active,
.main-content input[type="submit"]:active,
.main-content input[type="button"]:active {
    transform: translateY(0) scale(0.98);
}

/* FOCO PARA NAVEGACIÓN CON TECLADO */
.main-content button:focus-visible,
.main-content .btn:focus-visible,
.main-content input[type="submit"]:focus-visible,
.main-content input[type="button"]:focus-visible {
    outline: 2px solid var(--dorado);
    outline-offset: 3px;
}

/* DISEÑO RESPONSIVO */
@media (max-width: 768px) {
    .main-content table {
        font-size: 13px;
    }

    .main-content table thead th,
    .main-content table tbody td {
        padding: 9px 10px;
    }

    .main-content button,
    .main-content .btn,
    .main-content input[type="submit"],
    .main-content input[type="button"] {
        padding: 8px 12px;
    }
}

/* RESPETAR MOVIMIENTO REDUCIDO */
@media (prefers-reduced-motion: reduce) {
    .main-content table tbody td,
    .main-content button,
    .main-content .btn,
    .main-content input[type="submit"],
    .main-content input[type="button"] {
        transition: none;
    }
}

/* ==========================================
   BAKestock - MENU LATERAL ELEGANTE DORADO
   Iconos, linea activa y animaciones suaves
   ========================================== */

.sidebar {
    --menu-cafe: #4A3525;
    --menu-cafe-claro: #8C6239;
    --menu-crema: #FDFBF7;
    --menu-dorado: #D4A373;
    --menu-beige: #F4EAE1;
}

/* Enlaces y botones del menú */
.sidebar a,
.sidebar button {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;

    box-sizing: border-box;
    width: 100%;
    padding: 12px 16px;

    color: var(--menu-crema);
    background: transparent;
    border: 1px solid transparent;
    border-radius: 8px;

    font-family: inherit;
    font-size: 15px;
    font-weight: 500;
    text-align: left;
    text-decoration: none;

    cursor: pointer;

    transition:
        background-color 0.25s ease,
        color 0.25s ease,
        border-color 0.25s ease,
        padding-left 0.25s ease,
        box-shadow 0.25s ease;
}

/* Línea dorada: oculta por defecto */
.sidebar a::before,
.sidebar button::before {
    content: "";
    position: absolute;
    top: 22%;
    bottom: 22%;
    left: 0;

    width: 3px;
    border-radius: 0 4px 4px 0;
    background: var(--menu-dorado);

    transform: scaleY(0);
    transform-origin: center;

    transition: transform 0.25s ease;
}

/* Hover elegante */
.sidebar a:hover,
.sidebar button:hover {
    color: #FFE6BD;
    background: rgba(212, 163, 115, 0.12);
    border-color: rgba(212, 163, 115, 0.22);
    padding-left: 20px;
}

/* Línea visible al pasar el cursor */
.sidebar a:hover::before,
.sidebar button:hover::before {
    transform: scaleY(1);
}

/* Iconos de panadería dentro del menú */
.sidebar a .icono-menu,
.sidebar button .icono-menu {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    flex: 0 0 24px;
    width: 24px;
    height: 24px;

    color: var(--menu-dorado);
    font-size: 19px;
    line-height: 1;

    transition:
        color 0.25s ease,
        transform 0.25s ease;
}

/* Movimiento sutil del icono */
.sidebar a:hover .icono-menu,
.sidebar button:hover .icono-menu {
    color: #FFE6BD;
    transform: translateY(-1px);
}

/* Opción seleccionada: admite varias formas de marcarla */
.sidebar a.activo,
.sidebar button.activo,
.sidebar a.active,
.sidebar button.active,
.sidebar a[aria-current="page"] {
    color: #FFE6BD;
    background: rgba(212, 163, 115, 0.16);
    border-color: rgba(212, 163, 115, 0.28);
    box-shadow: inset 0 0 12px rgba(212, 163, 115, 0.04);
}

/* Línea dorada de la opción activa */
.sidebar a.activo::before,
.sidebar button.activo::before,
.sidebar a.active::before,
.sidebar button.active::before,
.sidebar a[aria-current="page"]::before {
    transform: scaleY(1);
}

/* Resaltar icono de la opción activa */
.sidebar a.activo .icono-menu,
.sidebar button.activo .icono-menu,
.sidebar a.active .icono-menu,
.sidebar button.active .icono-menu,
.sidebar a[aria-current="page"] .icono-menu {
    color: #FFE6BD;
}

/* Accesibilidad con teclado */
.sidebar a:focus-visible,
.sidebar button:focus-visible {
    outline: 2px solid var(--menu-dorado);
    outline-offset: 3px;
}

/* Respetar las preferencias de movimiento reducido */
@media (prefers-reduced-motion: reduce) {
    .sidebar a,
    .sidebar button,
    .sidebar a::before,
    .sidebar button::before,
    .sidebar a .icono-menu,
    .sidebar button .icono-menu {
        transition: none;
    }
}

/* Borde elegante para los botones del menú lateral */
.sidebar a,
.sidebar button {
    border: 1px solid rgba(212, 163, 115, 0.30);
    border-radius: 10px;
    box-shadow:
        0 2px 5px rgba(0, 0, 0, 0.10),
        inset 0 0 0 1px rgba(255, 255, 255, 0.02);
    transition: all 0.25s ease;
}

/* Efecto al pasar el cursor */
.sidebar a:hover,
.sidebar button:hover {
    border-color: #D4A373;
    background: linear-gradient(
        135deg,
        rgba(212, 163, 115, 0.22),
        rgba(140, 98, 57, 0.18)
    );
    box-shadow:
        0 0 10px rgba(212, 163, 115, 0.18),
        inset 0 0 0 1px rgba(212, 163, 115, 0.10);
    transform: translateX(3px);
}

/* Botón o enlace seleccionado */
.sidebar a.activo,
.sidebar button.activo,
.sidebar a.active,
.sidebar button.active,
.sidebar a[aria-current="page"] {
    border: 1px solid #D4A373;
    background: rgba(212, 163, 115, 0.18);
    box-shadow:
        0 0 12px rgba(212, 163, 115, 0.16),
        inset 0 0 0 1px rgba(212, 163, 115, 0.08);
}

/* Mantener sombreada la opción seleccionada */
.sidebar a.activo,
.sidebar button.activo,
.sidebar a.active,
.sidebar button.active,
.sidebar a[aria-current="page"] {
    color: #FFE6BD;
    background: linear-gradient(
        135deg,
        rgba(212, 163, 115, 0.28),
        rgba(140, 98, 57, 0.22)
    );
    border: 1px solid #D4A373;
    border-left: 4px solid #D4A373;
    border-radius: 10px;
    box-shadow: 0 0 12px rgba(212, 163, 115, 0.20);
}

/* Mantener el icono dorado en la opción seleccionada */
.sidebar a.activo .icono-menu,
.sidebar button.activo .icono-menu,
.sidebar a.active .icono-menu,
.sidebar button.active .icono-menu,
.sidebar a[aria-current="page"] .icono-menu {
    color: #FFE6BD;
}
.btn-cerrar-sesion {
    background-color: #8B2E2E;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

.btn-cerrar-sesion:hover {
    background-color: #641E1E;
}
    </style>
</head>
<body>

<?php if ($vista === 'bienvenida'): ?>
    <div class="bienvenida-container" onclick="iniciarMusicaPorInteraccion()">
        <audio id="musicaPanaderia" loop src="musicaPanaderia.mp3"></audio>

        <div class="bienvenida-contenido">
        <h1>¡BakeStock!</h1>
        <p>Sistema para el Control de Maquinaria</p>
        <p style="font-size: 0.9rem; color:#D4A373; 
        margin-top: -20px; margin-bottom: 20px;"> Haz un clic en cualquier parte de la pantalla para activar el ambiente</p>

        <a href="?vista=panel" class="btn-entrar">Entrar al Sistema →</a>
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
    $categoriasPermitidas = ['Maquinaria'];
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
        .layout { display: 
        flex; min-height: 100vh; }
        
        /* Menú Lateral Izquierdo */
        .sidebar { width: 280px; background-color: var(--cafe-oscuro); color: white; padding: 25px 15px; display: flex; flex-direction: column; gap: 20px; }
        .sidebar h2 { font-size: 1.4rem; border-bottom: 2px solid var(--dorado); padding-bottom: 10px; margin-bottom: 10px; text-align: center; color: var(--dorado); }
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .sidebar-menu a { color: #E6CCB2; text-decoration: none; padding: 12px 15px; border-radius: 8px; display: block; font-weight: 500; transition: 0.2s; }
        .sidebar-menu a:hover { background-color: var(--cafe-claro); color: white; padding-left: 20px; }
        
        /* Contenedor de Tablas */
        .main-content { flex: 1; padding: 40px; background-color: var(--crema); }
        .header-panel { display: flex; justify-content: space-between; 
        align-items: center; margin-bottom: 30px; 
        border-bottom: 2px solid #E6CCB2; padding-bottom: 15px; }
        .header-panel h1 { color: var(--cafe-oscuro); }
        
        .seccion-inventario { margin-bottom: 40px; 
        background: white; padding: 20px; 
        border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
    }
        .seccion-inventario h3 { color: var(--cafe-claro); margin-bottom: 15px; font-size: 1.3rem; border-left: 4px solid var(--dorado); padding-left: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: left; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #E6CCB2; font-size: 0.95rem; }
        th { background-color: #F4EAE1; color: var(--cafe-oscuro); }
        
        /* Badges del Semáforo */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; }
        .badge.green { background-color: #D4EDDA; color: #155724; }
        .badge.yellow { background-color: #FFF3CD; color: #856404; }
        .badge.red { background-color: #F8D7DA; color: #721C24; }
        
       /* ==================================
   BOTÓN VOLVER - EFECTO BAKestock
   ================================== */

.main-content .header-panel .btn-volver {
    position: relative;
    isolation: isolate;
    overflow: visible;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 13px 24px;

    background: transparent;
    color: #8C6239 !important;

    border: 2px solid #E6CCB2;
    border-radius: 9px;

    font-size: 15px;
    font-weight: bold;
    text-decoration: none;

    cursor: pointer;
    transition: all 0.5s ease;
}

/* Fondo desplazado */
.main-content .header-panel .btn-volver::after {
    content: "";
    position: absolute;

    width: 100%;
    height: 100%;

    top: 8px;
    left: 8px;

    background: #D5A084;
    border-radius: 9px;

    z-index: -1;
    transition: all 0.5s ease;
}

/* Movimiento del fondo */
.main-content .header-panel .btn-volver:hover::after {
    top: 0;
    left: 0;
}

/* Texto */
.texto-volver {
    position: relative;
    z-index: 2;
}

/* Primera decoración */
.main-content .header-panel .btn-volver .icon-1 {
    position: absolute;
    top: 5px;
    right: 12px;

    font-size: 17px;
    z-index: -1;

    transform: rotate(-25deg) translate(100%, 20%);
    transition: all 0.5s ease;
}

.main-content .header-panel .btn-volver:hover .icon-1 {
    transform: rotate(10deg) translate(100%, -45%) scale(1.5);
}

/* Segunda decoración */
.main-content .header-panel .btn-volver .icon-2 {
    position: absolute;
    top: 15px;
    left: 8px;

    font-size: 18px;
    z-index: -1;

    transition: all 0.5s ease;
}

.main-content .header-panel .btn-volver:hover .icon-2 {
    top: -22px;
    left: -25px;
    transform: rotate(65deg) scale(1.6);
    z-index: 1;
}

/* Al presionar */
.main-content .header-panel .btn-volver:active {
    transform: scale(0.96);
}

/* Accesibilidad */
@media (prefers-reduced-motion: reduce) {
    .main-content .header-panel .btn-volver,
    .main-content .header-panel .btn-volver::after,
    .main-content .header-panel .btn-volver .icon-1,
    .main-content .header-panel .btn-volver .icon-2 {
        transition: none !important;
    }
}

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
                    /* Ocultar la columna ACCIONES al imprimir */
                    .tabla-para-imprimir th:last-child,
                    .tabla-para-imprimir td:last-child {
                        display: none !important;
                    }       
                }

/* ===== BAKestock: PANEL ANIMADO ===== */

.main-content {
    background: linear-gradient(145deg, #fffaf3, #f5e8d8);
    min-height: 100vh;
    padding: 28px;
    box-sizing: border-box;
}

/* Animación suave al mostrar el contenido */
.main-content > * {
    animation: aparecerPanel 0.45s ease both;
}

@keyframes aparecerPanel {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Encabezados */
.main-content h1,
.main-content h2,
.main-content h3 {
    color: #4a3525;
}

.main-content h2 {
    border-left: 5px solid #d4a373;
    padding-left: 12px;
}

/* Botones y enlaces con apariencia de botón */
.main-content button,
.main-content .btn,
.main-content input[type="submit"],
.sidebar a {
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background-color 0.2s ease;
}

.main-content button:hover,
.main-content .btn:hover,
.main-content input[type="submit"]:hover,
.sidebar a:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(74, 53, 37, 0.16);
}

/* Tablas */
.main-content table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    overflow: hidden;
    background: #fffdf9;
    border-radius: 12px;
    box-shadow: 0 5px 18px rgba(74, 53, 37, 0.08);
}

.main-content table th {
    background: #5b402d;
    color: #fff8ee;
    padding: 13px;
    text-align: left;
}

.main-content table td {
    padding: 12px;
    border-bottom: 1px solid #eee0cf;
}

.main-content table tbody tr {
    transition: background-color 0.2s ease;
}

.main-content table tbody tr:hover {
    background: #fff0d9;
}

/* Indicadores del inventario */
.resumen-inventario {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin: 22px 0 28px;
}

.tarjeta-resumen {
    position: relative;
    overflow: hidden;
    padding: 22px;
    border-radius: 18px;
    background: #fffdf9;
    border: 1px solid #f0dfca;
    box-shadow: 0 6px 18px rgba(74, 53, 37, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tarjeta-resumen:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(74, 53, 37, 0.13);
}

.tarjeta-resumen .resumen-icono {
    font-size: 30px;
    margin-bottom: 10px;
}

.tarjeta-resumen .resumen-etiqueta {
    color: #785e49;
    font-size: 14px;
    margin-bottom: 8px;
}

.tarjeta-resumen .resumen-numero {
    color: #4a3525;
    font-size: clamp(25px, 3vw, 36px);
    font-weight: 800;
}

.tarjeta-resumen.total {
    border-top: 5px solid #d4a373;
}

.tarjeta-resumen.bajo {
    border-top: 5px solid #e9a23b;
}

.tarjeta-resumen.agotado {
    border-top: 5px solid #c95d4b;
}

/* Adaptación para pantallas pequeñas */
@media (max-width: 800px) {
    .main-content {
        padding: 16px;
    }

    .resumen-inventario {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .main-content table {
        font-size: 13px;
    }

    .main-content table th,
    .main-content table td {
        padding: 9px 7px;
    }
}

/* BOTÓN EDITAR ANIMADO - BAKESTOCK */

.btn-editar {
    border: none;
    outline: none;
    background-color: #3A3A3A;
    width: 145px;
    height: 48px;
    font-size: 15px;
    color: white;
    font-weight: 600;
    border-radius: 10px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    position: relative;
    text-decoration: none;
    transition: all 0.3s;
    isolation: isolate;
}

.btn-editar::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(212, 163, 115, 0.25);
    box-shadow: 0 8px 32px rgba(74, 53, 37, 0.35);
    width: 102%;
    height: 108%;
    z-index: -1;
    border-radius: inherit;
    transition: all 0.3s;
}

.btn-editar .gradient-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 102%;
    height: 108%;
    overflow: hidden;
    border-radius: inherit;
    z-index: -2;
    filter: blur(8px);
    transition: all 0.3s;
}

.btn-editar .gradient {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 150%;
    aspect-ratio: 1;
    border-radius: 100%;
    background-image: linear-gradient(
        90deg,
        #4A3525,
        #8C6239,
        #D4A373,
        #FDFBF7,
        #D4A373,
        #8C6239,
        #4A3525
    );
    animation: girarBakeStock 3s linear infinite;
    filter: blur(8px);
}

.btn-editar .label {
    width: 130%;
    height: 100%;
    text-align: center;
    line-height: 70px;
    border-radius: 10px;
    background-color: #4A3525;
    background-image: linear-gradient(
        180deg,
        #5A402D 0%,
        #3A281D 100%
    );
    color: #FDFBF7;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1;
}

.btn-editar:hover {
    transform: scale(1.05);
}

.btn-editar:hover .gradient-container {
    transform: translate(-50%, -50%) scale(0.98);
    filter: blur(5px);
}

.btn-editar:hover .gradient {
    filter: blur(5px);
}

@keyframes girarBakeStock {
    0% {
        transform: translate(-50%, -50%) rotate(0deg);
    }

    100% {
        transform: translate(-50%, -50%) rotate(360deg);
    }
}
/* =====================================
   FONDOS PERSONALIZADOS DE BAKESTOCK
   ===================================== */

.main-content.fondo-apartado {
    min-height: 100vh;
    width: 100%;
    box-sizing: border-box;
    background-size: cover !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    background-attachment: fixed;
}

/* Ver maquinaria */
.main-content.fondo-ver_tablas {
    background-image:
        linear-gradient(rgba(35, 22, 12, 0.48), rgba(35, 22, 12, 0.48)),
        url("fondo9.jfif") !important;
}

/* Tabla de eventos */
.main-content.fondo-eventos {
    background-image:
        linear-gradient(rgba(35, 22, 12, 0.48), rgba(35, 22, 12, 0.48)),
        url("fondo9.jfif") !important;
}

/* Agregar maquinaria */
.main-content.fondo-agregar {
    background-image:
        linear-gradient(rgba(35, 22, 12, 0.48), rgba(35, 22, 12, 0.48)),
        url("fondo9.jfif") !important;
}

/* Eliminar productos */
.main-content.fondo-eliminar_vista {
    background-image:
        linear-gradient(rgba(35, 22, 12, 0.48), rgba(35, 22, 12, 0.48)),
        url("fondo9.jfif") !important;
}

/* Editar maquinaria */
.main-content.fondo-editar {
    background-image:
        linear-gradient(rgba(35, 22, 12, 0.48), rgba(35, 22, 12, 0.48)),
        url("fondo9.jfif") !important;
}

/* Encabezados legibles sobre las imágenes */
.main-content .header-panel {
    background: rgba(253, 251, 247, 0.90);
    padding: 18px 20px;
    border-radius: 14px;
}

/* =====================================
   BAKESTOCK - DISEÑO PREMIUM
===================================== */

:root {
    --cafe-oscuro: #351d12;
    --cafe-medio: #56331f;
    --cafe-claro: #805637;
    --dorado: #d6a85f;
    --dorado-claro: #f0d7a6;
    --crema: #fbf5ec;
    --blanco: #ffffff;
    --texto: #38271d;
    --sombra: 0 12px 30px rgba(53, 29, 18, 0.10);
}

/* Fondo general */
body {
    background: linear-gradient(135deg, #fdf8f0, #f3e5d3);
    color: var(--texto);
    font-family: 'Inter', sans-serif;
}

/* Menú lateral */
.sidebar {
    background: linear-gradient(180deg, #432817, #2d190f);
    border-right: 2px solid var(--dorado);
    box-shadow: 5px 0 25px rgba(53, 29, 18, 0.15);
    border-radius: 0 18px 18px 0;
    padding: 22px 16px;
}

/* Título del menú */
.sidebar h2,
.sidebar h3 {
    color: var(--dorado);
    letter-spacing: 0.5px;
}

/* Enlaces y botones del menú */
.sidebar a,
.sidebar button {
    border-radius: 12px;
    transition: all 0.25s ease;
}

.sidebar a:hover,
.sidebar button:hover {
    background: rgba(214, 168, 95, 0.18);
    color: var(--dorado-claro);
    transform: translateX(4px);
}

/* Contenido principal */
.main-content {
    padding: 30px;
    min-height: 100vh;
}

/* Encabezados */
.main-content h1 {
    color: var(--cafe-oscuro);
    font-size: 30px;
    font-weight: 800;
    padding-bottom: 14px;
    border-bottom: 2px solid var(--dorado-claro);
    margin-bottom: 25px;
}

.main-content h2,
.main-content h3 {
    color: var(--cafe-medio);
}

/* Formularios y contenedores */
.main-content form,
.main-content .formulario,
.main-content .contenedor-formulario {
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid #ead8c3;
    border-radius: 18px;
    padding: 28px;
    box-shadow: var(--sombra);
}

/* Etiquetas */
.main-content label {
    display: block;
    color: var(--cafe-medio);
    font-weight: 700;
    margin-bottom: 8px;
}

/* Campos */
.main-content input:not([type="submit"]),
.main-content select,
.main-content textarea {
    width: 100%;
    padding: 13px 15px;
    border: 1.5px solid #dfc6a9;
    border-radius: 10px;
    background: #fffdfa;
    color: var(--texto);
    outline: none;
    transition: all 0.25s ease;
}

.main-content input:focus,
.main-content select:focus,
.main-content textarea:focus {
    border-color: var(--dorado);
    box-shadow: 0 0 0 4px rgba(214, 168, 95, 0.15);
}

/* Botones */
.main-content button,
.main-content input[type="submit"] {
    background: linear-gradient(135deg, #684126, #3b2114);
    color: white;
    border: 1px solid var(--dorado);
    border-radius: 11px;
    padding: 12px 20px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s ease;
}

.main-content button:hover,
.main-content input[type="submit"]:hover {
    background: linear-gradient(135deg, #9a6939, #56331f);
    box-shadow: 0 7px 18px rgba(86, 51, 31, 0.2);
    transform: translateY(-2px);
}

/* Tablas */
.main-content table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: var(--sombra);
}

.main-content th {
    background: var(--cafe-oscuro);
    color: var(--dorado-claro);
    padding: 15px;
    text-align: left;
}

.main-content td {
    padding: 14px 15px;
    border-bottom: 1px solid #eee2d5;
}

.main-content tr:hover td {
    background: #fff8ed;
}

/* Contenedores de información */
.main-content .contenedor,
.main-content .seccion,
.main-content .tabla-contenedor {
    background: rgba(255,255,255,0.92);
    border-radius: 16px;
    padding: 22px;
    box-shadow: var(--sombra);
    border: 1px solid #ead8c3;
}

/* Adaptación a móviles */
@media (max-width: 768px) {
    .main-content {
        padding: 16px;
    }

    .main-content h1 {
        font-size: 23px;
    }

    .main-content form {
        padding: 18px;
    }

    .main-content table {
        font-size: 13px;
    }
}
/* Borde premium para Añadir al Inventario */
.formulario-inventario {
    border: 2px solid #D6A85F !important;
    border-radius: 18px;
    box-shadow:
        0 0 0 4px rgba(214, 168, 95, 0.12),
        0 12px 30px rgba(53, 29, 18, 0.12);
}

/* TARJETA 3D */
.tarjeta-resumen-3d {
    height: 230px;
    perspective: 1000px;
    cursor: pointer;
}

/* CONTENIDO QUE GIRA */
.tarjeta-resumen-contenido {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.8s ease;
    transform-style: preserve-3d;
}

.tarjeta-resumen-3d:hover .tarjeta-resumen-contenido {
    transform: rotateY(180deg);
}

/* CARA DELANTERA Y TRASERA */
.tarjeta-resumen-frente,
.tarjeta-resumen-reverso {
    position: absolute;
    inset: 0;
    border-radius: 20px;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 22px;
    text-align: center;
    border: 2px solid #D4A373;
    box-shadow: 0 10px 25px rgba(53, 29, 18, 0.18);
}

/* FRENTE */
.tarjeta-resumen-frente {
    background: linear-gradient(145deg, #fffaf2, #ead7c1);
    color: #4A3525;
}

/* REVERSO */
.tarjeta-resumen-reverso {
    background: linear-gradient(145deg, #4A3525, #24170f);
    color: #FDFBF7;
    transform: rotateY(180deg);
}

.tarjeta-resumen-reverso h3 {
    color: #D4A373;
    font-size: 23px;
    margin-bottom: 12px;
}

.tarjeta-resumen-reverso p {
    font-size: 15px;
    line-height: 1.6;
}

.tarjeta-resumen-3d .resumen-icono {
    font-size: 35px;
    margin-bottom: 10px;
}

.tarjeta-resumen-3d .resumen-etiqueta {
    font-weight: 700;
    font-size: 15px;
}

.tarjeta-resumen-3d .resumen-numero {
    font-size: 38px;
    font-weight: 800;
    color: #8C6239;
    margin: 8px 0;
}

.tarjeta-resumen-frente small {
    font-size: 11px;
    color: #80664e;
}

/* ADAPTACIÓN A PANTALLAS PEQUEÑAS */
@media (max-width: 800px) {
    .resumen-inventario {
        grid-template-columns: 1fr;
    }
}
/* TARJETAS PREMIUM 3D */
.tarjeta-resumen-3d {
    position: relative;
    height: 230px;
    perspective: 1000px;
    border-radius: 20px;
    isolation: isolate;
}

/* BORDE DORADO ANIMADO */
.tarjeta-resumen-3d::before {
    content: "";
    position: absolute;
    inset: -3px;
    border-radius: 23px;
    background: conic-gradient(
        from var(--angulo-borde),
        #4A3525,
        #D4A373,
        #FFF0C2,
        #8C6239,
        #D4A373,
        #4A3525
    );
    animation: girar-borde 4s linear infinite;
    z-index: -1;
}

@property --angulo-borde {
    syntax: "<angle>";
    initial-value: 0deg;
    inherits: false;
}

@keyframes girar-borde {
    to {
        --angulo-borde: 360deg;
    }
}

/* ==================================
   BOTÓN ELIMINAR - BAKestock
   ================================== */

.main-content .btn-eliminar-producto {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;

    padding: 11px 20px !important;

    background: linear-gradient(135deg, #ff5265, #c91835) !important;
    color: white !important;

    border: 1px solid rgba(255,255,255,0.25) !important;
    border-radius: 12px !important;

    font-size: 14px !important;
    font-weight: 700 !important;
    letter-spacing: 0.4px;

    cursor: pointer;

    box-shadow: 0 5px 12px rgba(201, 24, 53, 0.25);

    transition: all 0.3s ease !important;
}

/* Icono */
.main-content .btn-eliminar-producto .icono-basura {
    font-size: 17px;
    transition: transform 0.3s ease;
}

/* Al pasar el cursor */
.main-content .btn-eliminar-producto:hover {
    background: linear-gradient(135deg, #e52d49, #a80024) !important;

    transform: translateY(-3px) scale(1.04);

    box-shadow: 0 9px 18px rgba(201, 24, 53, 0.35);
}

/* Movimiento del icono */
.main-content .btn-eliminar-producto:hover .icono-basura {
    transform: rotate(-12deg) scale(1.2);
}

/* Al presionar */
.main-content .btn-eliminar-producto:active {
    transform: scale(0.94);
    box-shadow: 0 2px 5px rgba(201, 24, 53, 0.25);
}

/* Accesibilidad */
.main-content .btn-eliminar-producto:focus-visible {
    outline: 3px solid #ff8795;
    outline-offset: 4px;
}

@media (prefers-reduced-motion: reduce) {
    .main-content .btn-eliminar-producto,
    .main-content .btn-eliminar-producto .icono-basura {
        transition: none !important;
    }
}
/* QUITAR EL RECUADRO BLANCO DE ELIMINAR */

.main-content .form-eliminar-producto {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;

    margin: 0 !important;
    padding: 0 !important;

    background: transparent !important;
    border: none !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

/* Evitar que las celdas agreguen recuadros */
.main-content .seccion-inventario td {
    background: transparent;
}

/* Mantener el botón rojo independiente */
.main-content .btn-eliminar-producto {
    position: relative;
    z-index: 1;
}

/* =====================================
   REGISTRO DE EVENTOS - DISEÑO BAKესტOCK
   ===================================== */

.main-content .eventos-panel {
    background: #FDFBF7;
    border: 1px solid #D4A373;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 8px 24px rgba(74, 53, 37, 0.10);
}

.main-content .eventos-encabezado {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    padding: 25px;
    margin-bottom: 22px;
    border-radius: 16px;
    background: linear-gradient(120deg, #4A3525, #8C6239);
    color: #FDFBF7;
}

.main-content .eventos-titulo {
    display: flex;
    align-items: center;
    gap: 16px;
}

.main-content .eventos-icono {
    display: grid;
    place-items: center;
    width: 65px;
    height: 65px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #D4A373;
    font-size: 32px;
}

.main-content .eventos-encabezado h1 {
    color: #FDFBF7;
    font-family: 'Playfair Display', serif;
    font-size: clamp(24px, 3vw, 36px);
}

.main-content .eventos-encabezado p {
    margin-top: 6px;
    color: #F4EAE1;
    font-style: italic;
}

.main-content .eventos-encabezado .btn-volver {
    color: #4A3525 !important;
    background: #F4C27A;
    border: 1px solid #D4A373;
    padding: 12px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
}

.main-content .eventos-encabezado .btn-volver:hover {
    background: #FDFBF7;
}

.main-content .eventos-acciones {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 18px;
}

.main-content .btn-limpiar-eventos {
    background: #8B2E2E;
    color: white;
    border: 1px solid #D4A373;
    border-radius: 10px;
    padding: 12px 18px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.25s ease;
}

.main-content .btn-limpiar-eventos:hover {
    background: #641E1E;
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(100, 30, 30, 0.2);
}

.main-content .eventos-tabla-contenedor {
    overflow-x: auto;
    border: 1px solid #D4A373;
    border-radius: 14px;
}

.main-content .eventos-tabla {
    width: 100%;
    min-width: 650px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    background: #FDFBF7;
}

.main-content .eventos-tabla thead th {
    padding: 16px;
    background: #4A3525;
    color: #FDFBF7;
    border-bottom: 3px solid #D4A373;
    text-align: left;
}

.main-content .eventos-tabla tbody td {
    padding: 15px 16px;
    color: #4A3525;
    border-bottom: 1px solid #E8D0AE;
}

.main-content .eventos-tabla tbody tr:nth-child(even) {
    background: #F4EAE1;
}

.main-content .eventos-tabla tbody tr:hover {
    background: #F1DFC5;
}

.main-content .eventos-tabla tbody tr:last-child td {
    border-bottom: none;
}

.main-content .etiqueta-accion {
    display: inline-block;
    padding: 7px 13px;
    border-radius: 20px;
    background: #F4C27A;
    color: #4A3525;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.4px;
}

.main-content .eventos-vacio {
    padding: 30px !important;
    text-align: center;
    color: #8C6239 !important;
    font-style: italic;
}

.main-content .eventos-pie {
    margin-top: 22px;
    padding: 16px;
    border: 1px solid #D4A373;
    border-radius: 12px;
    background: #F9E8D3;
    color: #4A3525;
    text-align: center;
    font-style: italic;
}

@media (max-width: 700px) {
    .main-content .eventos-panel {
        padding: 14px;
    }

    .main-content .eventos-encabezado {
        padding: 18px;
    }

    .main-content .eventos-encabezado .btn-volver {
        width: 100%;
        text-align: center;
    }

    .main-content .eventos-acciones form,
    .main-content .btn-limpiar-eventos {
        width: 100%;
    }
}
    </style>
    <div class="layout">
        <aside class="sidebar">
            <h2>🥖 Pan Panel</h2>
            <ul class="sidebar-menu">
<li> <a href="?vista=panel&accion_panel=ver_tablas&seccion=Maquinaria" class="<?php echo ($seccionActual === 'Maquinaria' && $subVista === 'ver_tablas') ? 'activo' : ''; ?>" aria-current="<?php echo ($seccionActual === 'Maquinaria' && $subVista === 'ver_tablas') ? 'page' : 'false'; ?>">
        ⚙️ Ver Maquinaria
    </a>
</li>
        <a href="?vista=panel&accion_panel=eventos" class="menu-item <?php echo ($subVista === 'eventos') ? 'activo' : ''; ?>">
    📋 Tabla de eventos
</a>


    <li style="margin-top: 15px; font-size: 0.85rem; color: var(--dorado); font-weight: bold; padding-left: 15px; letter-spacing: 1px;">AÑADIR AL INVENTARIO</li>
    <li> <a href="?vista=panel&accion_panel=agregar&cat=Maquinaria" class="<?php echo ($subVista === 'agregar') ? 'activo' : ''; ?>">
        ➕ Agregar Maquinaria
    </a>
</li>

    <li style="margin-top: 15px; font-size: 0.85rem; color: var(--dorado); font-weight: bold; padding-left: 15px; letter-spacing: 1px;">ACCIONES CRÍTICAS</li>
    <li><a href="?vista=panel&accion_panel=eliminar_vista" style="color: #D90429;">🗑️ Eliminar Producto</a></li>
</ul>

        <div style="margin-top: auto; text-align: center;">

                <!-- Conservar el botón de portada -->
                <a href="?vista=bienvenida" class="btn-volver"
                   style="background-color: transparent;
                   border: 1px solid #E6CCB2;
                   color: #E6CCB2;">
                    Salir a Portada
                </a>
                <form method="POST" action="inventario_panaderia.php" style="margin-top: 12px;">
    <input type="hidden" name="tipo_auth" value="logout">
    <button type="submit" class="btn-cerrar-sesion">
        Cerrar sesión
    </button>
</form>

            </div>
        </aside>
        
<main class="main-content fondo-apartado fondo-<?= htmlspecialchars($subVista, ENT_QUOTES, 'UTF-8') ?>">

    <?php if ($subVista === 'eventos'): ?>

        <?php
        $eventos = $pdo->query("
        SELECT id, usuario, accion, descripcion, fecha_hora
        FROM eventos
        ORDER BY fecha_hora DESC")->fetchAll();
        ?>

        <div class="header-panel">
            <h1>Eventos</h1><h1 class="titulo-eventos">📋 Registro de Eventos</h1>

            <a href="?vista=panel&accion_panel=ver_tablas" 
               class="btn-volver">
                ← Volver al inventario
                <span class="icon-1" aria-hidden="true">🌾</span>
                <span class="icon-2" aria-hidden="true">🍃</span>
            </a>
        </div>

<div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">

    <form method="POST"
          onsubmit="return confirm('¿Estás segura de que deseas eliminar TODO el historial de eventos? Esta acción no se puede deshacer.');">

        <input type="hidden" name="accion" value="limpiar_eventos">

        <button type="submit"
                style="background: #8B2E2E; color: white; border: 1px solid #D4A373;">
            🗑️ Limpiar historial completo
        </button>

    </form>

</div>


        <div class="seccion-inventario"
             style="overflow-x: auto;">

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Fecha y hora</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($eventos)): ?>
                        <tr>
                            <td colspan="4"
                                style="text-align: center; padding: 20px;">
                                Todavía no hay eventos registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($eventos as $evento): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        $evento['usuario'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $evento['accion'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $evento['descripcion'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>
                                    
                                <td>
                                    <?= htmlspecialchars(
                                        $evento['fecha_hora'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <form method="POST"
                                        onsubmit="return confirm('¿Deseas eliminar este evento?');">

                                        <input type="hidden" name="accion" value="eliminar_evento">

                                        <input type="hidden"
                                            name="id_evento"
                                            value="<?= (int)$evento['id'] ?>">

                                        <button type="submit"
                                                style="background: #8B2E2E; color: white;">
                                            🗑️ Eliminar
                                        </button>

                                    </form>
                                </td>
                                                    
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        
<!-- RESUMEN VISUAL DE BAKESTOCK -->
<section class="resumen-inventario" aria-label="Resumen del inventario">

    <article class="tarjeta-resumen-3d total">
        <div class="tarjeta-resumen-contenido">
            <div class="tarjeta-resumen-frente">
                <div class="resumen-icono">🥖</div>
                <div class="resumen-etiqueta">Productos registrados</div>
                <div class="resumen-numero">
                    <?= number_format($totalProductos) ?>
                </div>
                <small>Pasa el cursor para girar</small>
            </div>

            <div class="tarjeta-resumen-reverso">
                <h3>Inventario total</h3>
                <p>Consulta la cantidad de productos registrados en BakeStock.</p>
            </div>
        </div>
    </article>

    <article class="tarjeta-resumen-3d bajo">
        <div class="tarjeta-resumen-contenido">
            <div class="tarjeta-resumen-frente">
                <div class="resumen-icono">🟠</div>
                <div class="resumen-etiqueta">Productos con stock bajo</div>
                <div class="resumen-numero">
                    <?= number_format($productosStockBajo) ?>
                </div>
                <small>Pasa el cursor para girar</small>
            </div>

            <div class="tarjeta-resumen-reverso">
                <h3>Stock bajo</h3>
                <p>Estos productos necesitan atención para evitar quedarse sin existencias.</p>
            </div>
        </div>
    </article>

    <article class="tarjeta-resumen-3d agotado">
        <div class="tarjeta-resumen-contenido">
            <div class="tarjeta-resumen-frente">
                <div class="resumen-icono">🍞</div>
                <div class="resumen-etiqueta">Productos sin existencias</div>
                <div class="resumen-numero">
                    <?= number_format($productosSinStock) ?>
                </div>
                <small>Pasa el cursor para girar</small>
            </div>

            <div class="tarjeta-resumen-reverso">
                <h3>Sin existencias</h3>
                <p>Revisa estos productos para considerar su reposición.</p>
            </div>
        </div>
    </article>

</section>

    <?php endif; ?>

    <?php if ($subVista === 'ver_tablas'):?>
        <div class="header-panel">
            <!--cambiar titulo segun la ventana-->
            <h1><?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?><h1 class="titulo-maquinaria">Control de Maquinaria</h1></h1>
            <button onclick="window.print()" style="background-color: var(--cafe-oscuro); color:white; padding: 10px 20px;
            border: 2px solid var(--dorado); border-radius: 8px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; font-family: inherit;">
                🖨️ Imprimir Hoja
                 </button>
            </div>
            <!--Buscador que solo afectara a esta ventana activa-->
            <div style="margin-bottom: 25px; width: 100%;">
                <input type="text" id="buscadorInput" onkeyup="filtrarInventario()" placeholder="🔍 Escribe para buscar en <?php echo htmlspecialchars(strtolower($seccionActual), ENT_QUOTES, 'UTF-8'); ?>
                en tiempo real..." style="width: 100%; padding: 12px 20px; border: 2px solid var(--dorado); border-radius: 25px; font-size: 1rem; outline: none; box-shadow: 0 2px 5px rgba(0,0,0,0.05); font-family: inherit;">
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
                        <th>Notas/ Ubicación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php
                    $hayProductos = false;
                    foreach ($inventario as $indice => $item):
                        // OJO: Si el artículo no pertenece a la ventana/sección activa, lo ignoramos
                        if ($item['categoria'] !== $seccionActual) continue; $hayProductos = true;?>
                            <tr class="fila-producto">
                                <td class="nombre-producto">
                                    <?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td>
                                    <span style="font-weight:bold; display:inline-block; width:25px; text-align:center;"><?php echo $item['stock_actual']; ?></span>
                                </td>
                                <td><?php echo $item['stock_minimo'];?></td>
                                <td><?php echo obtenerAlertaStock($item['stock_actual'], $item['stock_minimo']); ?></td>
                                <td>
                                <i style="color: #666;">        
                                    <?php echo htmlspecialchars($item['nota'], ENT_QUOTES, 'UTF-8'); ?>
                                </i>
                            </td>
                            <td>
                            <a href="?vista=panel&accion_panel=editar&nombre=<?php echo urlencode($item['nombre']); ?>&categoria=<?php echo urlencode($item['categoria']); ?>"
                            class="btn-editar">
                            <span class="label">✏️ Modificar</span>
                            <span class="gradient-container">
                            <span class="gradient"></span>
                            </span>
                            </a>    
                            </td>
                            </tr>
                            <?php endforeach;?>
                            <tr class="mensaje-no-existe" style="display: none;">
                                <td colspan="6" style="text-align: center; color: #D90429; font-weight: bold; padding: 15px;">
                                    ⚠️ No se encontraron coincidencias en esta ventana.
                                </td>
                            </tr>
                            <?php if (!$hayProductos):?>
                                <tr><td colspan="6" style="text-align: center; color: #999;">No hay registros cargados en esta área.</td></tr>
                                <?php endif;?>
                </tbody>
            </table>
        </div>
        <?php endif;?>
        
<?php if ($subVista === 'editar'): ?>

    <?php
    $nombreEditar = trim($_GET['nombre'] ?? '');
    $categoriaEditar = $_GET['categoria'] ?? '';

    $productoEditar = null;

    if ($nombreEditar !== '' && $categoriaEditar === 'Maquinaria') {
        $consulta = $pdo->prepare(
            "SELECT * FROM inventario
             WHERE nombre = :nombre
             AND categoria = :categoria
             LIMIT 1"
        );

        $consulta->execute([
            ':nombre' => $nombreEditar,
            ':categoria' => $categoriaEditar
        ]);

        $productoEditar = $consulta->fetch();
    }
    ?>

    <div class="header-panel">
        <h1>Editar</h1><h1 class="titulo-maquinaria">Editar Producto</h1>
        <a href="?vista=panel&accion_panel=ver_tablas"
           class="btn-volver">← Cancelar y regresar</a>
    </div>

    <?php if ($productoEditar): ?>

        <div class="seccion-inventario"
             style="max-width: 600px; margin: 0 auto;">

            <form action="?vista=panel" method="POST">

                <input type="hidden" name="accion" value="editar">

                <input type="hidden" name="nombre_anterior"
                       value="<?php echo htmlspecialchars($productoEditar['nombre'], ENT_QUOTES, 'UTF-8'); ?>">

                <input type="hidden" name="categoria"
                       value="<?php echo htmlspecialchars($productoEditar['categoria'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="form-grupo">
                    <label>Nombre del artículo:</label>
                    <input type="text" name="nombre" required
                           value="<?php echo htmlspecialchars($productoEditar['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-grupo">
                    <label>Stock actual:</label>
                    <input type="number" name="stock_actual" min="0" required
                           value="<?php echo (int)$productoEditar['stock_actual']; ?>">
                </div>

                <div class="form-grupo">
                    <label>Stock mínimo:</label>
                    <input type="number" name="stock_minimo" min="0" required
                           value="<?php echo (int)$productoEditar['stock_minimo']; ?>">
                </div>

                <div class="form-grupo">
                    <label>Notas, ubicación o estado:</label>
                    <input type="text" name="nota"
                           value="<?php echo htmlspecialchars($productoEditar['nota'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <button type="submit" class="btn-guardar">
                    💾 Guardar cambios
                </button>

            </form>
        </div>

    <?php else: ?>

        <p>No se encontró el producto que deseas editar.</p>

    <?php endif; ?>

<?php endif; ?>

            <?php if ($subVista === 'agregar'): 
                // Solo se permite agregar productos de Maquinaria

            $categoriasPermitidas = ['Maquinaria'];

            $categoriaSeleccionada = $_GET['cat'] ?? 'Maquinaria';

            if (!in_array($categoriaSeleccionada, $categoriasPermitidas, true)) {
                $categoriaSeleccionada = 'Maquinaria';
            }
            ?>
                <!-- VISTA: FORMULARIO DINÁMICO PARA AGREGAR ARTÍCULOS -->
                <div class="header-panel">
                    <h1>Añadir</h1><h1 class="titulo-maqui">Añadir al Inventario</h1>
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
                    <form action="?vista=panel" method="POST" class="formulario-inventario">
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
                    <h1>Remover</h1><h1 class="titulo-eliminar">Remover Productos del Inventario</h1>
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
                                    <form method="POST" action="?vista=panel&accion_panel=eliminar_vista" class="form-eliminar-producto"
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

                                        <button type="submit" class="btn-eliminar-producto">
                                            <span class="icono-basura">🗑️</span>
                                            <span>Borrar</span>
                                            </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center; color: #999; padding: 30px;">No hay ningún producto registrado en el sistema.</td></tr>
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const bienvenida = document.querySelector(
        ".bienvenida-container"
    );

    // Si estamos en el inventario, no activar el cursor.
    if (!bienvenida) return;

    // Activar solo con mouse o trackpad.
    if (!window.matchMedia(
        "(hover: hover) and (pointer: fine)"
    ).matches) return;

    const cursor = document.createElement("div");
    cursor.className = "cursor-dorado";
    cursor.setAttribute("aria-hidden", "true");

    cursor.innerHTML = `
        <span class="estrella"></span>
        <span class="brillo brillo-uno"></span>
        <span class="brillo brillo-dos"></span>
    `;

    document.body.appendChild(cursor);

    bienvenida.addEventListener("mouseenter", function () {
        cursor.classList.add("visible");
    });

    bienvenida.addEventListener("mousemove", function (evento) {
        cursor.style.left = evento.clientX + "px";
        cursor.style.top = evento.clientY + "px";
    });

    bienvenida.addEventListener("mouseleave", function () {
        cursor.classList.remove("visible");
    });
});
</script>

</body>
</html>