<?php
// ==========================================
// 1. CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// ==========================================
$db_host = 'localhost';
$db_user = 'root'; // Cambia si tienes usuario
$db_pass = '';     // Cambia si tienes contraseña
$db_name = 'insumos'; // <-- CAMBIA ESTO POR EL NOMBRE DE TU BASE DE DATOS

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='padding:20px; background:#fde4e1; color:#9c2f25; font-family:sans-serif;'>
            <strong>Error de conexión a la Base de Datos:</strong> " . htmlspecialchars($e->getMessage()) . "
         </div>");
}

// ==========================================
// 2. ARREGLO DE SECCIONES GENERALES
// ==========================================
$secciones = [
    'inicio' => [
        'etiqueta' => 'Inicio',
        'titulo' => 'Bienvenido al panel de insumos',
        'descripcion' => 'Selecciona una etapa del proceso de panadería en el menú para consultar su información.',
    ],
    'secos' => [
        'etiqueta' => 'Insumos secos y masivos',
        'titulo' => 'Insumos secos y masivos',
        'herramientas' => [
            'Harina de trigo',
            'Azúcar',
            'Sal',
            'Sémola',
            'Fécula',
        ],
    ],
    'levadurantes' => [
        'etiqueta' => 'Insumo levadurante y químicos',
        'titulo' => 'Insumo levadurante y químicos',
        'herramientas' => [
            'Levadura seca/fresca',
            'Polvo para hornear',
            'Bicarbonato',
        ],
    ],
    'grasas' => [
        'etiqueta' => 'Materias grasas',
        'titulo' => 'Materias grasas',
        'herramientas' => [
            'Mantequilla',
            'Margarina',
            'Aceite vegetal',
            'Manteca',
        ],
    ],
    'liquidos' => [
        'etiqueta' => 'Líquidos y frescos',
        'titulo' => 'Líquidos y frescos',
        'herramientas' => [
            'Huevo',
            'Leche',
            'Crema',
            'Yogurt',
        ],
    ],
    'sabores' => [
        'etiqueta' => 'Aditivos, sabores y decoración',
        'titulo' => 'Aditivos, sabores y decoración',
        'herramientas' => [
            'Esencias',
            'Colorantes',
            'Semillas',
            'Frutas secas',
            'Chispas',
        ],
    ],
    'suministros' => [
        'etiqueta' => 'Insumos y suministros',
        'titulo' => 'Insumos y suministros',
        'herramientas' => [
            'Bolsas',
            'Cajas',
            'Capacillos',
            'Productos de limpieza',
        ],
    ],
];

$seccionActual = $_GET['seccion'] ?? 'secos';
if (!is_string($seccionActual) || !isset($secciones[$seccionActual])) {
    $seccionActual = 'secos';
}
$seccion = $secciones[$seccionActual];
$herramientaSeleccionada = null;
if (
    isset($_GET['herramienta'])
    && is_string($_GET['herramienta'])
    && ctype_digit($_GET['herramienta'])
    && isset($seccion['herramientas'][(int) $_GET['herramienta']])
) {
    $herramientaSeleccionada = (int) $_GET['herramienta'];
} elseif (!empty($seccion['herramientas'])) {
    $herramientaSeleccionada = 0;
}
// ==========================================
// 3. PROCESAMIENTO AJAX PARA GUARDAR EN LA BD
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'guardar') {
    header('Content-Type: application/json');
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
               // Consulta preparada apuntando a inventario_insumos con la unidad_medida
        $stmt = $pdo->prepare("INSERT INTO inventario_insumos (storage_key, nombre_articulo, stock_actual, minimo_requerido, unidad_medida, notas, fecha_registro)
                                VALUES (:key, :nombre, :stock, :minimo, :unidad, :notas, :fecha)
                                ON DUPLICATE KEY UPDATE 
                                nombre_articulo = :nombre, stock_actual = :stock, minimo_requerido = :minimo, unidad_medida = :unidad, notas = :notas, fecha_registro = :fecha");
        
        $stmt->execute([
            ':key'    => $input['storage_key'],
            ':nombre' => $input['name'],
            ':stock'  => $input['stock'],
            ':minimo' => $input['minimum'],
            ':unidad' => $input['unidad'],
            ':notas'  => $input['notes'],
            ':fecha'  => $input['fFecha_registro']
        ]);
        
        echo json_encode(['status' => 'success']);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// 4. CONSULTA DE DATOS DESDE LA TABLA: inventario_insumos
$db_item = ['stock' => null, 'minimum' => null, 'unidad' => 'Piezas', 'notes' => 'Pendiente de registrar existencias', 'fecha' => ''];
if ($herramientaSeleccionada !== null) {
    $current_key = "bakestock-{$seccionActual}-{$herramientaSeleccionada}";
    $stmt = $pdo->prepare("SELECT * FROM inventario_insumos WHERE storage_key = ?");
    $stmt->execute([$current_key]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($resultado) {
        $db_item['stock'] = $resultado['stock_actual'];
        $db_item['minimum'] = $resultado['minimo_requerido'];
        $db_item['unidad'] = $resultado['unidad_medida'];
        $db_item['notes'] = $resultado['notes'];
        $db_item['fecha'] = $resultado['fecha_registro'] ? substr($resultado['fecha_registro'], 0, 10) : '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($seccion['titulo'] ?? $seccion['etiqueta'] ?? 'Insumos', ENT_QUOTES, 'UTF-8'); ?> | BakeStock-insumos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #fdfbf7;
            color: #3e2723;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 260px;
            padding-top: 20px;
            background: #3e2723;
            color: #fff;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.08);
        }

        .sidebar-header {
            margin-bottom: 24px;
            padding: 0 20px 20px;
            border-bottom: 1px solid #5d4037;
            text-align: center;
        }

        .sidebar-header h2 {
            color: #ffb74d;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu a {
            display: block;
            padding: 15px 25px;
            border-left: 4px solid transparent;
            color: #d7ccc8;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a:focus-visible,
        .sidebar-menu a.active {
            border-left-color: #ffb74d;
            background: #4e342e;
            color: #fff;
            outline: none;
        }

        .btn-regresar {
            position: fixed;
            bottom: 18px;
            left: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            color: #d7ccc8;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .btn-regresar:hover,
        .btn-regresar:focus-visible {
            background: #4e342e;
            color: #ffb74d;
            outline: 2px solid #ffb74d;
            outline-offset: 2px;
        }

        .btn-regresar svg {
            width: 32px;
            height: 32px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .main-content {
            width: calc(100% - 260px);
            margin-left: 260px;
            padding: clamp(28px, 6vw, 64px);
        }

        .stage-tools {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 12px 24px;
            margin-bottom: 24px;
            padding: 16px 20px;
            border-radius: 10px;
            background: #ffb74d;
            color: #3e2723;
            font-weight: 600;
        }

        .stage-tools a {
            display: inline-flex;
            align-items: center;
            min-height: 42px;
            padding: 10px 16px;
            border: 2px solid transparent;
            border-radius: 8px;
            color: #3e2723;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .stage-tools a:hover,
        .stage-tools a:focus-visible,
        .stage-tools a[aria-current="page"] {
            border-color: #ffb74d;
            background: #3e2723;
            color: #fff;
            outline: none;
        }

        .content-card {
            width: 100%;
            padding: clamp(24px, 5vw, 42px);
            border: 1px solid #eadcc9;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(62, 39, 35, 0.08);
        }

        .inventory-heading {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 1px solid #d7b36a;
        }

        .inventory-heading h1 {
            margin: 0;
            color: #3e2723;
            font-size: clamp(1.5rem, 3vw, 2rem);
        }

        .inventory-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .inventory-actions [hidden] {
            display: none !important;
        }

        .delete-button {
            border: 0;
            border-radius: 6px;
            padding: 8px 12px;
            background: #d9534f;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .delete-button:hover,
        .delete-button:focus-visible {
            background: #c9302c;
        }

        .delete-button:active {
            transform: scale(0.98);
        }

        .print-button {
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: #3e2723;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .print-button:hover,
        .print-button:focus-visible {
            background: #7a442a;
        }

        .add-item-link {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        .search-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .inventory-search {
            flex: 1;
            min-width: 0;
            margin-bottom: 0;
            padding: 13px 18px;
            border: 2px solid #d7b36a;
            border-radius: 25px;
            color: #3e2723;
            font: inherit;
        }

        .edit-mode-control {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 8px;
            color: #5d4037;
            font-size: 0.9rem;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
        }

        .edit-mode-toggle {
            position: relative;
            width: 40px;
            height: 23px;
            margin: 0;
            border: 1px solid #a99a8a;
            border-radius: 999px;
            appearance: none;
            background: #d7ccc8;
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .edit-mode-toggle::before {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: #fff;
            content: "";
            transition: transform 0.2s ease;
        }

        .edit-mode-toggle:checked {
            border-color: #267343;
            background: #267343;
        }

        .edit-mode-toggle:checked::before {
            transform: translateX(17px);
        }

        .edit-mode-toggle:focus-visible {
            outline: 3px solid rgba(38, 115, 67, 0.3);
            outline-offset: 2px;
        }

        .inventory-table-wrap {
            overflow-x: auto;
            padding: 16px;
            border: 1px solid #f0e5d7;
            border-radius: 10px;
        }

        .inventory-table-wrap h2 {
            margin-bottom: 14px;
            padding-left: 10px;
            border-left: 4px solid #d7b36a;
            color: #5d4037;
            font-size: 1.15rem;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .row-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #e07a5f;
        }

        .inventory-table:not(.is-selecting) .selection-column {
            display: none;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee4da;
            white-space: nowrap;
        }

        .inventory-table th {
            background: #f5efe7;
            color: #3e2723;
        }

        .edit-field {
            width: 100%;
            min-width: 90px;
            padding: 8px;
            border: 1px solid #d7b36a;
            border-radius: 6px;
            color: #3e2723;
            font: inherit;
        }

        .edit-field:focus {
            outline: 2px solid rgba(215, 179, 106, 0.35);
        }

        .edit-error {
            display: none;
            padding: 12px 0 0;
            color: #9c2f25;
            font-weight: 600;
        }

        .empty-search {
            display: none;
            padding: 18px 10px;
            color: #79551a;
            text-align: center;
        }

        .content-card h1 {
            margin-bottom: 14px;
            color: #3e2723;
            font-size: clamp(1.7rem, 4vw, 2.4rem);
        }

        .content-card p {
            color: #5d4037;
            font-size: 1.05rem;
            line-height: 1.7;
        }

        @media (max-width: 700px) {
            .sidebar {
                position: static;
                width: 100%;
                padding-top: 14px;
            }

            .sidebar-header {
                margin-bottom: 10px;
                padding-bottom: 14px;
            }

            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding-bottom: 8px;
            }

            .sidebar-menu li {
                flex: 0 0 auto;
            }

            .sidebar-menu a {
                padding: 12px 16px;
                border-left: 0;
                border-bottom: 3px solid transparent;
                font-size: 14px;
            }

            .sidebar-menu a:hover,
            .sidebar-menu a:focus-visible,
            .sidebar-menu a.active {
                border-bottom-color: #ffb74d;
            }

            .main-content {
                width: 100%;
                margin-left: 0;
                padding: 24px 16px;
            }
        }

        @media print {
            .sidebar,
            .stage-tools,
            .search-controls,
            .print-button,
            .inventory-actions {
                display: none !important;
            }

            .main-content {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .content-card {
                border: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
       <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🥖 BakeStock</h2>
        </div>
        <nav aria-label="Menú principal">
            <ul class="sidebar-menu">
                <!-- Busca algo muy similar a esto en tu archivo insumos.php -->

                <?php foreach ($secciones as $id => $datos): ?>
                     <li>
                        <!-- Forzamos de forma directa la redirección a la herramienta 0 -->
                        <a
                            href="insumos.php?seccion=<?php echo urlencode($id); ?>&amp;herramienta=0"
                            class="<?php echo $id === $seccionActual ? 'active' : ''; ?>"
                            <?php echo $id === $seccionActual ? 'aria-current="page"' : ''; ?>
                        ><?php echo htmlspecialchars($datos['etiqueta'], ENT_QUOTES, 'UTF-8'); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <a class="btn-regresar" href="clasificacion.php" aria-label="Regresar a clasificación" title="Regresar a clasificación">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M19 12H5M12 19l-7-7 7-7"></path>
            </svg>
        </a>
    </aside>

    <main class="main-content">
        <?php if (!empty($seccion['herramientas'])): ?>
            <div class="stage-tools" aria-label="Herramientas de <?php echo htmlspecialchars($seccion['etiqueta'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach ($seccion['herramientas'] as $indice => $herramienta): ?>
                    <a
                        href="insumos.php?seccion=<?php echo urlencode($seccionActual); ?>&amp;herramienta=<?php echo $indice; ?>"
                        <?php echo $herramientaSeleccionada === $indice ? 'aria-current="page"' : ''; ?>
                    ><?php echo htmlspecialchars($herramienta, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="content-card" aria-labelledby="page-title">
            <?php if ($herramientaSeleccionada !== null): ?>
                <?php $nombreHerramienta = $seccion['herramientas'][$herramientaSeleccionada]; ?>
                <div class="inventory-heading">
                    <div class="inventory-actions">
                        <button class="delete-button" type="button" id="btn-eliminar-seleccion">🗑️ Eliminar</button>
                        <button class="cancel-button" type="button" id="cancelar-eliminacion" hidden>Cancelar</button>
                    </div>
                    <h1 id="page-title">Control de <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <button class="print-button" type="button" onclick="window.print()">🖨️ Imprimir hoja</button>
                </div>
                <div class="search-controls">
                    <input
                        class="inventory-search"
                        id="inventory-search"
                        
                        type="search"
                        placeholder="🔍 Escribe para buscar en <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>..."
                        aria-label="Buscar en el inventario de <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <a
                        class="print-button add-item-link"
                        href="anadir.php?seccion=<?php echo rawurlencode($seccionActual); ?>&amp;herramienta=<?php echo $herramientaSeleccionada; ?>"
                    >➕ Agregar artículo</a>
                    <label class="edit-mode-control" for="edit-mode-toggle">
                        <span>Modo edición</span>
                        <input class="edit-mode-toggle" id="edit-mode-toggle" type="checkbox" role="switch" aria-label="Activar modo de edición">
                    </label>
                </div>
                <div class="inventory-table-wrap">
                    <h2>Listado activo de <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th class="selection-column" style="width: 40px;"></th>
                                <th>Nombre del artículo</th>
                                <th>Cantidad total</th>
                                <th>Cantidad actual</th>
                                <th>Unidad_medida</th>
                                <th>Notas</th>
                                <th>Fecha de Registro</th>
                            </tr>
                        </thead>
                        <tbody
                            id="inventory-rows"
                            data-list-key="bakestock-listado-insumos-<?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?>-<?php echo $herramientaSeleccionada; ?>"
                            data-has-unit="true"
                        >
                            <tr
                                class="inventory-row"
                                data-storage-key="bakestock-<?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?>-<?php echo $herramientaSeleccionada; ?>"
                                data-deleted-key="bakestock-eliminado-<?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?>-<?php echo $herramientaSeleccionada; ?>"
                                data-default-name="<?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>"
                                data-registration-date="<?php echo htmlspecialchars($db_item['fecha'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <td class="selection-column" style="text-align: center;">
                                    <input type="checkbox" class="row-checkbox" aria-label="Seleccionar <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>">
                                </td>
                                <td class="item-name"><?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="item-stock">—</td>
                                <td class="item-minimum">—</td>
                                <td class="item-unit">Piezas (pz)</td>
                                <td class="item-notes">Pendiente de registrar existencias</td>
                                <td class="item-date">—</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="empty-search" id="empty-search" role="status">No se encontraron resultados.</p>
                    <p class="edit-error" id="edit-error" role="alert"></p>
                </div>
            <?php else: ?>
            <?php endif; ?>
        </section>
    </main>
    <?php if ($herramientaSeleccionada !== null): ?>
        <script src="inventario-listado.js"></script>
        <script>
            const inventorySearch = document.getElementById('inventory-search');
            const inventoryRow = document.querySelector('.inventory-row');
            const editError = document.getElementById('edit-error');
            const editModeToggle = document.getElementById('edit-mode-toggle');
            const itemName = inventoryRow.querySelector('.item-name');
            const itemStock = inventoryRow.querySelector('.item-stock');
            const itemMinimum = inventoryRow.querySelector('.item-minimum');
            const itemNotes = inventoryRow.querySelector('.item-notes');
            const itemUnit = inventoryRow.querySelector('.item-unit');
            const itemDate = inventoryRow.querySelector('.item-date');
            let savedItem = null;
            const today = () => {
                const now = new Date();
                return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
            };
            const measureUnits = [
                'Gramos (g)',
                'Kilogramos (kg)',
                'Libras (lb)',
                'Onzas (oz)',
                'Mililitros (ml)',
                'Litros (L)',
                'Piezas (pz)'
            ];
            const defaultMeasureUnit = 'Piezas (pz)';
            const deleteButton = document.getElementById('btn-eliminar-seleccion');
            const cancelDeleteButton = document.getElementById('cancelar-eliminacion');
            const inventoryTable = document.querySelector('.inventory-table');
            let itemDeleted = false;
            let selectionMode = false;

            function setSelectionMode(enabled) {
                selectionMode = enabled;
                inventoryTable.classList.toggle('is-selecting', enabled);
                deleteButton.textContent = enabled ? '🗑️ Eliminar seleccionado' : '🗑️ Eliminar';
                cancelDeleteButton.hidden = !enabled;
                editModeToggle.disabled = enabled || itemDeleted;
                if (!enabled) {
                    document.querySelectorAll('.row-checkbox').forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                }
            }

            deleteButton.addEventListener('click', () => {
                if (!selectionMode) {
                    setSelectionMode(true);
                    return;
                }
                const selectedRows = document.querySelectorAll('.row-checkbox:checked');
                if (selectedRows.length === 0) {
                    alert('Selecciona primero las casillas de los artículos que quieres eliminar.');
                    return;
                }
                if (!confirm(`¿Seguro que quieres eliminar los ${selectedRows.length} artículos seleccionados?`)) {
                    setSelectionMode(false);
                    return;
                }

                try {
                    selectedRows.forEach((checkbox) => {
                        const row = checkbox.closest('.inventory-row');
                        if (!row) return;
                        localStorage.setItem(row.dataset.deletedKey, 'true');
                        localStorage.removeItem(row.dataset.storageKey);
                        if (row.dataset.listId && window.BakeStockAddedItems) {
                            window.BakeStockAddedItems.remove(row);
                        }
                        if (row === inventoryRow) {
                            savedItem = null;
                            itemDeleted = true;
                        }
                        row.remove();
                    });
                    setSelectionMode(false);
                    const hasRows = document.querySelectorAll('.inventory-row').length > 0;
                    if (!hasRows) {
                        document.getElementById('empty-search').textContent = 'No hay artículos en este inventario.';
                        document.getElementById('empty-search').style.display = 'block';
                    }
                    deleteButton.disabled = !hasRows;
                } catch (error) {
                    console.error('No se pudo eliminar el artículo del inventario.', error);
                    editError.textContent = 'No se pudo eliminar el artículo guardado en este navegador.';
                    editError.style.display = 'block';
                }
            });

            cancelDeleteButton.addEventListener('click', () => setSelectionMode(false));

            function showItem(item) {
                itemName.textContent = item.name;
                itemStock.textContent = item.stock === null ? '—' : item.stock;
                itemMinimum.textContent = item.minimum === null ? '—' : item.minimum;
                itemUnit.textContent = item.unit;
                itemDate.textContent = item.fFecha_registro || inventoryRow.dataset.registrationDate || '—';
                itemNotes.textContent = item.notes || '—';
            }

            try {
                if (localStorage.getItem(inventoryRow.dataset.deletedKey) === 'true') {
                    inventoryRow.remove();
                    itemDeleted = true;
                    const hasRows = document.querySelectorAll('.inventory-row').length > 0;
                    if (!hasRows) {
                        document.getElementById('empty-search').textContent = 'No hay artículos en este inventario.';
                        document.getElementById('empty-search').style.display = 'block';
                    }
                    deleteButton.disabled = !hasRows;
                } else {
                    const storedItem = localStorage.getItem(inventoryRow.dataset.storageKey);
                    if (storedItem !== null) {
                        const parsedItem = JSON.parse(storedItem);
                        if (
                            typeof parsedItem.name !== 'string'
                            || (parsedItem.stock !== null && !Number.isInteger(parsedItem.stock))
                            || (parsedItem.minimum !== null && !Number.isInteger(parsedItem.minimum))
                            || typeof parsedItem.notes !== 'string'
                            || (parsedItem.unit !== undefined && !measureUnits.includes(parsedItem.unit))
                            || (parsedItem.fFecha_registro !== undefined && !/^\d{4}-\d{2}-\d{2}$/.test(parsedItem.fFecha_registro))
                        ) {
                            throw new Error('Los datos guardados para este artículo no son válidos.');
                        }
                        parsedItem.unit = parsedItem.unit || defaultMeasureUnit;
                        savedItem = parsedItem;
                        showItem(savedItem);
                    }
                }
            } catch (error) {
                console.error('No se pudieron cargar los datos guardados del inventario.', error);
                editError.textContent = 'No se pudieron cargar los datos guardados. Revisa el almacenamiento del navegador.';
                editError.style.display = 'block';
            }

            function setEditing(isEditing) {
                editModeToggle.checked = isEditing;
                deleteButton.disabled = isEditing || itemDeleted;
                if (isEditing) {
                    setSelectionMode(false);
                }
            }

            function startEditing() {
                editError.style.display = 'none';
                if (itemDeleted) {
                    document.getElementById('inventory-rows').append(inventoryRow);
                    itemName.textContent = inventoryRow.dataset.defaultName;
                    itemStock.textContent = '—';
                    itemMinimum.textContent = '—';
                    itemUnit.textContent = defaultMeasureUnit;
                    itemDate.textContent = today();
                    itemNotes.textContent = 'Pendiente de registrar existencias';
                    inventoryRow.hidden = false;
                    itemDeleted = false;
                    document.getElementById('empty-search').style.display = 'none';
                }
                const current = savedItem || {
                    name: itemName.textContent,
                    stock: '',
                    minimum: '',
                    notes: '',
                    fFecha_registro: inventoryRow.dataset.registrationDate || today()
                };
                itemName.innerHTML = '';
                itemStock.innerHTML = '';
                itemMinimum.innerHTML = '';
                itemNotes.innerHTML = '';
                itemDate.innerHTML = '';

                const fields = [
                    [itemName, 'name', current.name, 'text'],
                    [itemStock, 'stock', current.stock ?? '', 'number'],
                    [itemMinimum, 'minimum', current.minimum ?? '', 'number'],
                    [itemNotes, 'notes', current.notes, 'text'],
                    [itemDate, 'fFecha_registro', current.fFecha_registro || inventoryRow.dataset.registrationDate || today(), 'date']
                ];
                fields.forEach(([cell, name, value, type]) => {
                    const input = document.createElement('input');
                    input.className = 'edit-field editable-value';
                    input.name = name;
                    input.type = type;
                    input.value = value;
                    if (type === 'number') {
                        input.min = '0';
                        input.step = '1';
                        input.placeholder = '0';
                    }
                    cell.replaceChildren(input);
                });
                const unitSelect = document.createElement('select');
                unitSelect.className = 'edit-field editable-value';
                unitSelect.name = 'unit';
                measureUnits.forEach((unit) => {
                    const option = document.createElement('option');
                    option.value = unit;
                    option.textContent = unit;
                    unitSelect.append(option);
                });
                unitSelect.value = measureUnits.includes(current.unit) ? current.unit : defaultMeasureUnit;
                itemUnit.replaceChildren(unitSelect);
                setEditing(true);
                inventoryRow.querySelector('[name="name"]').focus();
            }

            async function saveEditing() {
                const name = inventoryRow.querySelector('[name="name"]').value.trim();
                const stockValue = inventoryRow.querySelector('[name="stock"]').value;
                const minimumValue = inventoryRow.querySelector('[name="minimum"]').value;
                const unit = inventoryRow.querySelector('[name="unit"]').value;
                const fFecha_registro = inventoryRow.querySelector('[name="fFecha_registro"]').value;
                const notes = inventoryRow.querySelector('[name="notes"]').value.trim();
                const stock = stockValue === '' ? null : Number(stockValue);
                const minimum = minimumValue === '' ? null : Number(minimumValue);

                if (!name || !measureUnits.includes(unit) || !/^\d{4}-\d{2}-\d{2}$/.test(fFecha_registro) || (stock !== null && (!Number.isInteger(stock) || stock < 0)) || (minimum !== null && (!Number.isInteger(minimum) || minimum < 0))) {
                    editError.textContent = 'Escribe un nombre, selecciona una unidad, una fecha válida y cantidades enteras iguales o mayores que cero.';
                    editError.style.display = 'block';
                    editModeToggle.checked = true;
                    return false;
                }

                const updatedItem = { name, stock, minimum, unit, notes, fFecha_registro };
                try {
                    const response = await fetch('insumos.php?action=guardar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            storage_key: inventoryRow.dataset.storageKey,
                            name,
                            stock,
                            minimum,
                            unidad: unit,
                            notes,
                            fFecha_registro
                        })
                    });
                    const result = await response.json();
                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || 'No se pudo guardar en la base de datos.');
                    }
                    localStorage.setItem(inventoryRow.dataset.storageKey, JSON.stringify(updatedItem));
                    localStorage.removeItem(inventoryRow.dataset.deletedKey);
                } catch (error) {
                    console.error('No se pudieron guardar los cambios del inventario.', error);
                    editError.textContent = 'No se pudieron guardar los cambios en la base de datos y el navegador.';
                    editError.style.display = 'block';
                    editModeToggle.checked = true;
                    return false;
                }

                savedItem = updatedItem;
                showItem(savedItem);
                editError.style.display = 'none';
                setEditing(false);
                return true;
            }

            editModeToggle.addEventListener('change', () => {
                if (editModeToggle.checked) {
                    startEditing();
                } else {
                    saveEditing();
                }
            });

            inventorySearch.addEventListener('input', () => {
                const query = inventorySearch.value.trim().toLocaleLowerCase();
                let visibleRows = 0;

                document.querySelectorAll('.inventory-row').forEach((row) => {
                    const matches = row.textContent.toLocaleLowerCase().includes(query);
                    row.hidden = !matches;
                    if (matches) visibleRows++;
                });

                document.getElementById('empty-search').style.display = visibleRows ? 'none' : 'block';
            });
        </script>
    <?php endif; ?>
</body>
</html>
