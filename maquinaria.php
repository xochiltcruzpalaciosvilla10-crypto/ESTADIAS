<?php
$secciones = [
    'inicio' => [
        'etiqueta' => 'Inicio',
        'titulo' => 'Bienvenido al panel principal',
        'descripcion' => 'Selecciona una etapa del proceso de panadería en el menú para consultar su información.',
    ],
    'amasado' => [
        'etiqueta' => 'Equipo de Mezclado y amasado',
        'titulo' => 'Equipo de Mezclado y amasado',
        'descripcion' => 'Etapa 1: donde puedes preparar la masa y darle forma.',
        'herramientas' => [
            'Amasadora de espiral',
            'Amasadora de brazos',
            'Batidoras planetarias',
        ],
    ],
    'proporción' => [
        'etiqueta' => 'Equipo de porcionado y espirado',
        'titulo' => 'Equipo de porcionado y espirado',
        'descripcion' => 'Etapa 2: integra los ingredientes y prepara la masa de acuerdo con el producto que vas a elaborar.',
        'herramientas' => [
            'Divisoras volumétricas',
            'Boleadoras automatizadas',
            'Laminadoras de bandas y motorizadas',
        ],
    ],
    'formado' => [
        'etiqueta' => 'Equipo de formado y moldeo',
        'titulo' => 'Equipo de formado y moldeo',
        'descripcion' => 'Etapa 3: hornea, hace que el producto este cosido.',
        'herramientas' => [
            'Formadoras de baguettes',
            'Formadoras de bolillos',
            'Moldeadoras de pan de caja',
        ],
    ],
    'fermentacion' => [
        'etiqueta' => 'Equipos de fermentación y climática',
        'titulo' => 'Equipos de fermentación y climática',
        'descripcion' => 'Etapa 4: donde puedo dejar el producto no cosido o cosido.',
        'herramientas' => [
            'Cámara de fermentación estática',
            'Cámara de fermentación controlada',
        ],
    ],
    'hornos' => [
        'etiqueta' => 'Sistema de horno y cocción',
        'titulo' => 'Sistema de horno y cocción',
        'descripcion' => 'Etapa 4: donde puedo dejar el producto no cosido o cosido.',
        'herramientas' => [
            'Hornos rotativos',
            'Hornos de pisos',
            'Hornos de convección',
        ],
    ],
    'gestion' => [
        'etiqueta' => 'Gestión de cadena de frío',
        'titulo' => 'Gestión de cadena de frío',
        'descripcion' => 'Etapa 4: donde puedo dejar el producto no cosido o cosido.',
        'herramientas' => [
            'Cámara frigorificas',
            'Cámara de congelación',
            'Batidoras de temperatura',
        ],
    ],
    'logistica' => [
        'etiqueta' => 'Logística y soporte de obrador',
        'titulo' => 'Logística y soporte de obrador',
        'descripcion' => 'Etapa 4: donde puedo dejar el producto no cosido o cosido.',
        'herramientas' => [
            'Mesas de acero inoxidable pesadas',
            'espigueros',
        ],
    ],
];

$seccionActual = $_GET['seccion'] ?? 'amasado';
if (!is_string($seccionActual) || !isset($secciones[$seccionActual])) {
    $seccionActual = 'amasado';
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($seccion['titulo'], ENT_QUOTES, 'UTF-8'); ?> | BakeStock</title>
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

        .cancel-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 36px;
            padding: 7px 11px;
            border: 1px solid transparent;
            border-radius: 7px;
            font: inherit;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .cancel-button {
            border-color: #d6c6b7;
            background: #fff;
            color: #5d4037;
        }

        .cancel-button:hover,
        .cancel-button:focus-visible {
            background: #f5efe7;
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
            .delete-button {
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

        .row-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #e07a5f;
        }

        .inventory-table:not(.is-selecting) .selection-column {
            display: none;
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
                <?php foreach ($secciones as $id => $datos): ?>
                     <li>
                        <!-- Cambiado a maquinaria.php con la herramienta=0 fija -->
                        <a
                            href="maquinaria.php?seccion=<?php echo urlencode($id); ?>&amp;herramienta=0"
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
                    <!-- Cambiado también aquí abajo para mantener la navegación en maquinaria.php -->
                    <a
                        href="maquinaria.php?seccion=<?php echo urlencode($seccionActual); ?>&amp;herramienta=<?php echo $indice; ?>"
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
                        <button class="delete-button" type="button" id="btn-eliminar-seleccion">
                            🗑️ Eliminar
                        </button>
                        <button class="cancel-button" type="button" id="cancelar-eliminacion" hidden>
                            Cancelar
                        </button>
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
                                <th class="selection-column" style="width: 40px; text-align: center;"></th>
                                <th>Nombre del artículo</th>
                                <th>Cantidad total</th>
                                <th>Cantidad actual</th>
                                <th>Notas_ubicación</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-rows">
                            <tr
                                class="inventory-row"
                                data-storage-key="bakestock-<?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?>-<?php echo $herramientaSeleccionada; ?>"
                                data-deleted-key="bakestock-eliminado-<?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?>-<?php echo $herramientaSeleccionada; ?>"
                                data-default-name="<?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                             <!-- 2. AQUÍ QUEDA LA CASILLA INDIVIDUAL PARA ESTA FILA -->
                                <td class="selection-column" style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="row-checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                                </td>
                                <td class="item-name"><?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="item-stock">—</td>
                                <td class="item-minimum">—</td>
                                <td class="item-notes">Pendiente de registrar existencias</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="empty-search" id="empty-search" role="status">No se encontraron resultados.</p>
                    <p class="edit-error" id="edit-error" role="alert"></p>
                </div>
            <?php else: ?>
                <h1 id="page-title"><?php echo htmlspecialchars($seccion['titulo'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p><?php echo htmlspecialchars($seccion['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </section>
    </main>
    <?php if ($herramientaSeleccionada !== null): ?>
        <script>
            const inventorySearch = document.getElementById('inventory-search');
            const inventoryRow = document.querySelector('.inventory-row');
            const editError = document.getElementById('edit-error');
            const editModeToggle = document.getElementById('edit-mode-toggle');
            const itemName = inventoryRow.querySelector('.item-name');
            const itemStock = inventoryRow.querySelector('.item-stock');
            const itemMinimum = inventoryRow.querySelector('.item-minimum');
            const itemNotes = inventoryRow.querySelector('.item-notes');
            let savedItem = null;

            // ========================================================
            // EL CÓDIGO DE LAS CASILLAS Y ELIMINAR VA AQUÍ MISMO:
            // ========================================================
            const btnEliminar = document.getElementById('btn-eliminar-seleccion');
            const cancelarEliminacion = document.getElementById('cancelar-eliminacion');
            const inventoryTable = document.querySelector('.inventory-table');
            let modoSeleccion = false;
            let itemDeleted = false;

            function setSelectionMode(enabled) {
                modoSeleccion = enabled;
                inventoryTable.classList.toggle('is-selecting', enabled);
                btnEliminar.textContent = enabled ? '🗑️ Eliminar seleccionados' : '🗑️ Eliminar';
                cancelarEliminacion.hidden = !enabled;
                if (!enabled) {
                    document.querySelectorAll('.row-checkbox').forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                }
            }

            if (btnEliminar) {
                btnEliminar.addEventListener('click', () => {
                    if (!modoSeleccion) {
                        setSelectionMode(true);
                        return;
                    }

                    const seleccionados = document.querySelectorAll('.row-checkbox:checked');
                    if (seleccionados.length === 0) {
                        alert('Por favor, selecciona primero las casillas de los artículos que quieres borrar.');
                        return;
                    }
                    if (!confirm(`¿Estás seguro de que quieres eliminar las ${seleccionados.length} filas seleccionadas?`)) {
                        setSelectionMode(false);
                        return;
                    }
                    let eliminados = 0;
                    seleccionados.forEach((checkbox) => {
                        const fila = checkbox.closest('.inventory-row');
                        if (!fila) return;

                        try {
                            localStorage.setItem(fila.dataset.deletedKey, 'true');
                            localStorage.removeItem(fila.dataset.storageKey);
                            if (fila === inventoryRow) {
                                savedItem = null;
                                itemDeleted = true;
                            }
                            fila.remove();
                            eliminados++;
                        } catch (error) {
                            console.error('No se pudo eliminar el artículo del inventario.', error);
                            editError.textContent = 'No se pudo eliminar el artículo guardado en este navegador.';
                            editError.style.display = 'block';
                        }
                    });
                    if (eliminados > 0) {
                        editError.textContent = '';
                        editError.style.display = 'none';
                        setSelectionMode(false);
                        if (document.querySelectorAll('.inventory-row').length === 0) {
                            document.getElementById('empty-search').textContent = 'No hay artículos en este inventario.';
                            document.getElementById('empty-search').style.display = 'block';
                            btnEliminar.disabled = true;
                        }
                    }
                });
            }

            cancelarEliminacion.addEventListener('click', () => {
                setSelectionMode(false);
            });

            function showItem(item) {
                itemName.textContent = item.name;
                itemStock.textContent = item.stock === null ? '—' : item.stock;
                itemMinimum.textContent = item.minimum === null ? '—' : item.minimum;
                itemNotes.textContent = item.notes || '—';
            }

            try {
                if (localStorage.getItem(inventoryRow.dataset.deletedKey) === 'true') {
                    inventoryRow.remove();
                    document.getElementById('empty-search').textContent = 'No hay artículos en este inventario.';
                    document.getElementById('empty-search').style.display = 'block';
                    itemDeleted = true;
                    btnEliminar.disabled = true;
                } else {
                    const storedItem = localStorage.getItem(inventoryRow.dataset.storageKey);
                    if (storedItem !== null) {
                        const parsedItem = JSON.parse(storedItem);
                        if (
                            typeof parsedItem.name !== 'string'
                            || (parsedItem.stock !== null && !Number.isInteger(parsedItem.stock))
                            || (parsedItem.minimum !== null && !Number.isInteger(parsedItem.minimum))
                            || typeof parsedItem.notes !== 'string'
                        ) {
                            throw new Error('Los datos guardados para este artículo no son válidos.');
                        }
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
                btnEliminar.disabled = isEditing || itemDeleted;
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
                    itemNotes.textContent = 'Pendiente de registrar existencias';
                    inventoryRow.hidden = false;
                    itemDeleted = false;
                    document.getElementById('empty-search').style.display = 'none';
                }
                const current = savedItem || {
                    name: itemName.textContent,
                    stock: '',
                    minimum: '',
                    notes: ''
                };
                itemName.innerHTML = '';
                itemStock.innerHTML = '';
                itemMinimum.innerHTML = '';
                itemNotes.innerHTML = '';

                const fields = [
                    [itemName, 'name', current.name, 'text'],
                    [itemStock, 'stock', current.stock ?? '', 'number'],
                    [itemMinimum, 'minimum', current.minimum ?? '', 'number'],
                    [itemNotes, 'notes', current.notes, 'text']
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
                setEditing(true);
                inventoryRow.querySelector('[name="name"]').focus();
            }

            function saveEditing() {
                const name = inventoryRow.querySelector('[name="name"]').value.trim();
                const stockValue = inventoryRow.querySelector('[name="stock"]').value;
                const minimumValue = inventoryRow.querySelector('[name="minimum"]').value;
                const notes = inventoryRow.querySelector('[name="notes"]').value.trim();
                const stock = stockValue === '' ? null : Number(stockValue);
                const minimum = minimumValue === '' ? null : Number(minimumValue);

                if (!name || (stock !== null && (!Number.isInteger(stock) || stock < 0)) || (minimum !== null && (!Number.isInteger(minimum) || minimum < 0))) {
                    editError.textContent = 'Escribe un nombre y cantidades enteras iguales o mayores que cero.';
                    editError.style.display = 'block';
                    editModeToggle.checked = true;
                    return false;
                }

                const updatedItem = { name, stock, minimum, notes };
                try {
                    localStorage.setItem(inventoryRow.dataset.storageKey, JSON.stringify(updatedItem));
                    localStorage.removeItem(inventoryRow.dataset.deletedKey);
                } catch (error) {
                    console.error('No se pudieron guardar los cambios del inventario.', error);
                    editError.textContent = 'No se pudieron guardar los cambios en este navegador.';
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
                const matches = inventoryRow.textContent.toLocaleLowerCase().includes(query);
                inventoryRow.hidden = !matches;
                document.getElementById('empty-search').style.display = matches ? 'none' : 'block';
            });
        </script>
    <?php endif; ?>
</body>
</html>