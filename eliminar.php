<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "utensilios");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// 1. Detectar y normalizar la pestaña seleccionada
$seccion = $_GET['seccion'] ?? 'cocina caliente';
if ($seccion === 'cocina+caliente') {
    $seccion = 'cocina caliente';
}

$nombresSeccion = [
    'cocina caliente' => 'Cocina caliente',
    'panaderia'       => 'Panadería',
    'reposteria'      => 'Repostería'
];
$categoriaActual = $nombresSeccion[$seccion] ?? 'Cocina caliente';

// 2. Procesar eliminación si presionan "Borrar"
if (isset($_GET['borrar']) && is_numeric($_GET['borrar'])) {
    $idBorrar = intval($_GET['borrar']);

    // Obtener nombre y sección real del producto antes de borrarlo
    $stmtInfo = $conexion->prepare("SELECT nombre_articulo, seccion FROM equipo_menor WHERE id = ?");
    $stmtInfo->bind_param("i", $idBorrar);
    $stmtInfo->execute();
    $resInfo = $stmtInfo->get_result();

    if ($rowInfo = $resInfo->fetch_assoc()) {
        $conexion->query("CREATE TABLE IF NOT EXISTS eventos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(100) NOT NULL DEFAULT 'Desconocido',
            accion VARCHAR(30) NOT NULL,
            seccion VARCHAR(50) NOT NULL DEFAULT 'general',
            descripcion TEXT NOT NULL,
            fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $usuarioActual = $_SESSION['usuario'] ?? $_SESSION['nombre'] ?? 'Desconocido';
        $seccionProd   = !empty($rowInfo['seccion']) ? $rowInfo['seccion'] : $seccion;
        $nombreSecDesc = $nombresSeccion[$seccionProd] ?? ucfirst($seccionProd);
        $descEvento    = "Eliminó el producto: " . $rowInfo['nombre_articulo'] . ". Categoría: " . $nombreSecDesc . ".";

        $stmtEv = $conexion->prepare("INSERT INTO eventos (usuario, accion, seccion, descripcion, fecha_hora) VALUES (?, 'ELIMINAR', ?, ?, NOW())");
        $stmtEv->bind_param("sss", $usuarioActual, $seccionProd, $descEvento);
        $stmtEv->execute();
        $stmtEv->close();
    }
    $stmtInfo->close();

    $stmtDel = $conexion->prepare("DELETE FROM equipo_menor WHERE id = ?");
    if ($stmtDel) {
        $stmtDel->bind_param("i", $idBorrar);
        $stmtDel->execute();
        $stmtDel->close();
    }
    header("Location: eliminar.php?seccion=" . urlencode($seccion));
    exit;
}

// 3. Obtener los artículos únicamente de la categoría activa
$stmt = $conexion->prepare("SELECT * FROM equipo_menor WHERE seccion = ? ORDER BY nombre_articulo ASC");
if (!$stmt) {
    die("Error SQL: " . $conexion->error);
}
$stmt->bind_param("s", $seccion);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remover Productos | BakeStock</title>
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

        /* Barra superior idéntica a tu captura */
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

        /* Selector de las 3 opciones para que los chefs no se confundan */
        .tabs-bar {
            width: 100%;
            max-width: 1380px;
            background-color: #3d2214;
            border-radius: 12px;
            padding: 12px 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .tabs-label { color: #e5c384; font-size: 13px; font-weight: 700; margin-right: 8px; }
        .tab-btn {
            text-decoration: none;
            color: #e6d8cc;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 22px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .tab-btn:hover { background-color: #57321f; color: #ffffff; }
        .tab-btn.active {
            background-color: #d69d76;
            color: #3d2214;
            font-weight: 800;
        }

        /* Tarjeta blanca principal */
        .main-card {
            width: 100%;
            max-width: 1380px;
            background-color: #ffffff;
            border-radius: 14px;
            padding: 28px 32px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.15);
        }
        .card-title-row {
            border-left: 3px solid #c8963e;
            padding-left: 12px;
            margin-bottom: 20px;
        }
        .card-title-row h2 { font-size: 17px; font-weight: 800; color: #3d2214; margin-bottom: 4px; }
        .card-title-row p { font-size: 13px; color: #7a6859; }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; }
        thead tr { background-color: #3d2214; color: #ffffff; }
        th { text-align: left; padding: 13px 18px; font-size: 13px; font-weight: 700; }
        th:last-child { text-align: center; }
        td { padding: 14px 18px; font-size: 14px; color: #3d2214; border-bottom: 1px solid #f0e8df; }
        tbody tr:nth-child(even) { background-color: #f9efe6; }
        tbody tr:hover { background-color: #f3e4d6; }

        .badge-categoria {
            display: inline-block;
            background-color: #efe2d4;
            color: #3d2214;
            font-weight: 700;
            font-size: 12.5px;
            padding: 4px 10px;
            border-radius: 6px;
        }
        .btn-borrar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background-color: #d92638;
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(217, 38, 56, 0.3);
            transition: 0.2s;
        }
        .btn-borrar:hover { background-color: #b01c2b; transform: translateY(-1px); }
    </style>
</head>
<body>

    <header class="top-header">
        <div class="header-left">Remover</div>
        <div class="header-center"><h1>Remover Productos del Inventario</h1></div>
        <div class="header-right">
            <a href="Utensilios.php?seccion=<?php echo urlencode($seccion); ?>" class="btn-volver">← Volver al inventario</a>
        </div>
    </header>

    <!-- Selector claro de las 3 secciones -->
    <nav class="tabs-bar">
        <span class="tabs-label">Selecciona el área:</span>
        <a href="eliminar.php?seccion=cocina+caliente" class="tab-btn <?php echo $seccion === 'cocina caliente' ? 'active' : ''; ?>">Cocina caliente</a>
        <a href="eliminar.php?seccion=panaderia" class="tab-btn <?php echo $seccion === 'panaderia' ? 'active' : ''; ?>">Panadería</a>
        <a href="eliminar.php?seccion=reposteria" class="tab-btn <?php echo $seccion === 'reposteria' ? 'active' : ''; ?>">Repostería</a>
    </nav>

    <main class="main-card">
        <div class="card-title-row">
            <h2>Lista de Control de Baja — <?php echo htmlspecialchars($categoriaActual); ?></h2>
            <p>Haz clic en el botón de eliminar correspondiente al artículo de <strong><?php echo htmlspecialchars($categoriaActual); ?></strong> que deseas dar de baja permanentemente.</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Nombre del Artículo</th>
                    <th>Stock</th>
                    <th>Acción Crítica</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge-categoria"><?php echo htmlspecialchars($categoriaActual); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($fila['nombre_articulo']); ?></strong></td>
                            <td><?php echo intval($fila['cantidad_actual']); ?> unidades</td>
                            <td style="text-align: center;">
                                <a href="eliminar.php?seccion=<?php echo urlencode($seccion); ?>&borrar=<?php echo intval($fila['id']); ?>"
                                   class="btn-borrar"
                                   onclick="return confirm('⚠️ ¿Estás seguro de eliminar permanentemente «<?php echo htmlspecialchars(addslashes($fila['nombre_articulo'])); ?>» de <?php echo htmlspecialchars($categoriaActual); ?>?');">
                                   🗑 Borrar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 28px; color: #8c7a6b;">
                            No hay productos registrados en <strong><?php echo htmlspecialchars($categoriaActual); ?></strong> para eliminar.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

</body>
</html>
