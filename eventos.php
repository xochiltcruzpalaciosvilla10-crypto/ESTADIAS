<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "utensilios");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// Crear la tabla 'eventos' si no existe
$conexion->query("CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL DEFAULT 'Desconocido',
    accion VARCHAR(30) NOT NULL,
    seccion VARCHAR(50) NOT NULL DEFAULT 'general',
    descripcion TEXT NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Aseguramos que la columna 'seccion' exista sin borrar tus datos viejos
$checkSec = $conexion->query("SHOW COLUMNS FROM eventos LIKE 'seccion'");
if ($checkSec && $checkSec->num_rows === 0) {
    $conexion->query("ALTER TABLE eventos ADD COLUMN seccion VARCHAR(50) NOT NULL DEFAULT 'general' AFTER accion");
}

$seccion_volver = $_GET['seccion'] ?? 'cocina caliente';
$filtro = $_GET['filtro'] ?? $seccion_volver;

// Si llega algo raro o vacío, lo forzamos a 'cocina caliente'
if ($filtro !== 'cocina caliente' && $filtro !== 'panaderia' && $filtro !== 'reposteria') {
}

// Filtrar la consulta según la pestaña seleccionada
$stmt = $conexion->prepare("SELECT * FROM eventos WHERE seccion = ? OR descripcion LIKE ? ORDER BY fecha_hora DESC, id DESC");
$like_busqueda = "%" . $filtro . "%";
$stmt->bind_param("ss", $filtro, $like_busqueda);
$stmt->execute();
$resultado = $stmt->get_result();


$nombres_seccion = [
    'cocina caliente' => 'Cocina caliente',
    'panaderia'       => 'Panadería',
    'reposteria'      => 'Repostería'
    ];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Eventos | BakeStock</title>
    <style>
        img {
  max-width: 100%;
  height: auto;
}
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            min-height: 100vh;
            background-color: #9e8a78;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            gap: 20px;
        }

        /* Encabezado */
        .top-header {
            width: 100%;
            max-width: 1380px;
            background-color: #faf6f0;
            border-radius: 12px;
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .header-left { font-size: 22px; font-weight: 800; color: #3d2214; min-width: 180px; }
        .header-center h1 {
            font-size: 24px;
            font-weight: 800;
            color: #3d2214;
            padding-bottom: 6px;
            border-bottom: 2px solid #e5c384;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-right { min-width: 180px; display: flex; justify-content: flex-end; }
        .btn-volver {
            background-color: #d69d76;
            color: #3d2214;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .btn-volver:hover { background-color: #c5885f; }

        /* Tarjeta blanca de la tabla */
        .main-card {
            width: 100%;
            max-width: 1380px;
            background-color: #ffffff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.15);
            overflow-x: auto;
        }

        /* Pestañas de filtro */
        .filtros-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .tab-filtro {
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            background-color: #f3e8dd;
            color: #5c4938;
            transition: 0.2s;
        }
        .tab-filtro:hover {
            background-color: #e6d3c1;
            color: #3d2214;
        }
        .tab-filtro.activa {
            background-color: #3d2214;
            color: #ffffff;
        }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background-color: #3d2214; color: #ffffff; }
        th { text-align: left; padding: 14px 18px; font-size: 13px; font-weight: 700; }
        th:last-child { text-align: right; }
        td { padding: 13px 18px; font-size: 13.5px; color: #3d2214; border-bottom: 1px solid #f0e8df; }
        td:last-child { text-align: right; color: #5c4938; font-variant-numeric: tabular-nums; }
        tbody tr:nth-child(even) { background-color: #f9efe6; }
        tbody tr:hover { background-color: #f3e4d6; }
        .accion-texto { font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .badge-seccion {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background-color: #efe3d6;
            color: #462814;
        }
    </style>
</head>
<body>

    <header class="top-header">
        <div class="header-left">Eventos</div>
        <div class="header-center"><h1>📋 Registro de Eventos</h1></div>
        <div class="header-right">
            <a href="Utensilios.php?seccion=<?php echo urlencode($seccion_volver); ?>" class="btn-volver">← Volver al inventario</a>
        </div>
    </header>

    <main class="main-card">
        <!-- PESTAÑAS DE FILTRO -->
        <div class="filtros-bar">
            <a href="eventos.php?filtro=cocina+caliente&seccion=<?php echo urlencode($seccion_volver); ?>" 
               class="tab-filtro <?php echo ($filtro === 'cocina caliente') ? 'activa' : ''; ?>">
               Cocina caliente
            </a>
            <a href="eventos.php?filtro=panaderia&seccion=<?php echo urlencode($seccion_volver); ?>" 
               class="tab-filtro <?php echo ($filtro === 'panaderia') ? 'activa' : ''; ?>">
               Panadería
            </a>
            <a href="eventos.php?filtro=reposteria&seccion=<?php echo urlencode($seccion_volver); ?>" 
               class="tab-filtro <?php echo ($filtro === 'reposteria') ? 'activa' : ''; ?>">
               Repostería
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Sección</th>
                    <th>Descripción</th>
                    <th>Fecha y hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($ev = $resultado->fetch_assoc()): ?>
                        <?php 
                            $sec_val = $ev['seccion'] ?? 'general';
                            $etiqueta_sec = $nombres_seccion[$sec_val] ?? ucfirst($sec_val);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ev['usuario']); ?></td>
                            <td><span class="accion-texto"><?php echo htmlspecialchars($ev['accion']); ?></span></td>
                            <td><span class="badge-seccion"><?php echo htmlspecialchars($etiqueta_sec); ?></span></td>
                            <td><?php echo htmlspecialchars($ev['descripcion']); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($ev['fecha_hora'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 28px; color: #8c7a6b;">
                            No hay movimientos registrados en esta sección.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

</body>
</html>
