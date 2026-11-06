<?php
$secciones = [
    'inicio' => [
        'etiqueta' => 'Inicio',
        'titulo' => 'Bienvenido al panel principal',
        'descripcion' => 'Selecciona una etapa del proceso de panadería en el menú para consultar su información.',
    ],
    'medicion' => [
        'etiqueta' => 'Medición y Pesado',
        'titulo' => 'Medición y Pesado',
        'descripcion' => 'Etapa 1: prepara y pesa las cantidades de cada ingrediente antes de comenzar la elaboración.',
        'herramientas' => [
            'Balanza de precisión / Báscula digital',
            'Jarras y cucharas medidoras',
            'Termómetro',
        ],
    ],
    'mezcla' => [
        'etiqueta' => 'Mezcla y Preparación',
        'titulo' => 'Mezcla y Preparación',
        'descripcion' => 'Etapa 2: integra los ingredientes y prepara la masa de acuerdo con el producto que vas a elaborar.',
        'herramientas' => [
            'Bowls o cuencos',
            'Cernidores o arneros',
            'Espátulas y brochas',
        ],
    ],
    'corte' => [
        'etiqueta' => 'Corte, División y formado',
        'titulo' => 'Corte, División y formado',
        'descripcion' => 'Etapa 3: divide la masa en porciones y dales la forma necesaria antes de hornearlas.',
        'herramientas' => [
            'Raspador de masa',
            'Cuchillos de sierra y hojas de panadero',
            'Rodillos de amasar',
            'Cortadores y moldes',
        ],
    ],
    'horneado' => [
        'etiqueta' => 'Horneado y reposo',
        'titulo' => 'Horneado y reposo',
        'descripcion' => 'Etapa 4: hornea las piezas y déjalas reposar el tiempo necesario antes de manipularlas.',
        'herramientas' => [
            'Charolas y bandejas',
            'Moldes de caja y rosca',
            'Rejilla de enfriamiento',
        ],
    ],
];

$seccionActual = $_GET['seccion'] ?? 'mezcla';
if (!is_string($seccionActual) || !isset($secciones[$seccionActual])) {
    $seccionActual = 'mezcla';
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

        .inventory-search {
            width: 100%;
            margin-bottom: 20px;
            padding: 13px 18px;
            border: 2px solid #d7b36a;
            border-radius: 25px;
            color: #3e2723;
            font: inherit;
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

        .inventory-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: #fff0cf;
            color: #79551a;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .inventory-status.is-ok {
            background: #dff3e5;
            color: #226b38;
        }

        .inventory-status.is-low {
            background: #fde4e1;
            color: #9c2f25;
        }

        .edit-button,
        .save-button,
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

        .edit-button {
            background: #e3f2e7;
            color: #267343;
        }

        .edit-button:hover,
        .edit-button:focus-visible {
            background: #267343;
            color: #fff;
        }

        .edit-button svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }

        .save-button {
            background: #3e2723;
            color: #fff;
        }

        .save-button:hover,
        .save-button:focus-visible {
            background: #7a442a;
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
            .inventory-search,
            .print-button {
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
                <?php foreach ($secciones as $id => $datos): ?>
                    <li>
                        <a
                            href="Utensilios.php?seccion=<?php echo urlencode($id); ?>"
                            class="<?php echo $id === $seccionActual ? 'active' : ''; ?>"
                            <?php echo $id === $seccionActual ? 'aria-current="page"' : ''; ?>
                        ><?php echo htmlspecialchars($datos['etiqueta'], ENT_QUOTES, 'UTF-8'); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <?php if (!empty($seccion['herramientas'])): ?>
            <div class="stage-tools" aria-label="Herramientas de <?php echo htmlspecialchars($seccion['etiqueta'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach ($seccion['herramientas'] as $indice => $herramienta): ?>
                    <a
                        href="Utensilios.php?seccion=<?php echo urlencode($seccionActual); ?>&amp;herramienta=<?php echo $indice; ?>"
                        <?php echo $herramientaSeleccionada === $indice ? 'aria-current="page"' : ''; ?>
                    ><?php echo htmlspecialchars($herramienta, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <section class="content-card" aria-labelledby="page-title">
            <?php if ($herramientaSeleccionada !== null): ?>
                <?php $nombreHerramienta = $seccion['herramientas'][$herramientaSeleccionada]; ?>
                <div class="inventory-heading">
                    <h1 id="page-title">Control de <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <button class="print-button" type="button" onclick="window.print()">🖨️ Imprimir hoja</button>
                </div>
                <input
                    class="inventory-search"
                    id="inventory-search"
                    type="search"
                    placeholder="🔍 Escribe para buscar en <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>..."
                    aria-label="Buscar en el inventario de <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>"
                >
                <div class="inventory-table-wrap">
                    <h2>Listado activo de <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Nombre del artículo</th>
                                <th>Stock actual</th>
                                <th>Mínimo requerido</th>
                                <th>Estado</th>
                                <th>Notas / Ubicación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-rows">
                            <tr
                                class="inventory-row"
                                data-storage-key="bakestock-<?php echo htmlspecialchars($seccionActual, ENT_QUOTES, 'UTF-8'); ?>-<?php echo $herramientaSeleccionada; ?>"
                                data-default-name="<?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <td class="item-name"><?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="item-stock">—</td>
                                <td class="item-minimum">—</td>
                                <td><span class="inventory-status">Sin registrar</span></td>
                                <td class="item-notes">Pendiente de registrar existencias</td>
                                <td class="item-actions">
                                    <button class="edit-button" type="button" aria-label="Editar <?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm17.71-10.04a1 1 0 0 0 0-1.42l-2.5-2.5a1 1 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 2-1.66z"/></svg>
                                        <span>Editar</span>
                                    </button>
                                    <div class="edit-actions" hidden>
                                        <button class="save-button" type="button">Guardar</button>
                                        <button class="cancel-button" type="button">Cancelar</button>
                                    </div>
                                </td>
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
            const editButton = inventoryRow.querySelector('.edit-button');
            const editActions = inventoryRow.querySelector('.edit-actions');
            const itemName = inventoryRow.querySelector('.item-name');
            const itemStock = inventoryRow.querySelector('.item-stock');
            const itemMinimum = inventoryRow.querySelector('.item-minimum');
            const itemNotes = inventoryRow.querySelector('.item-notes');
            const statusBadge = inventoryRow.querySelector('.inventory-status');
            let savedItem = null;

            function updateStatus(stock, minimum) {
                statusBadge.classList.remove('is-ok', 'is-low');
                if (stock === null || minimum === null) {
                    statusBadge.textContent = 'Sin registrar';
                } else if (stock >= minimum) {
                    statusBadge.textContent = 'OK';
                    statusBadge.classList.add('is-ok');
                } else {
                    statusBadge.textContent = 'Stock bajo';
                    statusBadge.classList.add('is-low');
                }
            }

            function showItem(item) {
                itemName.textContent = item.name;
                itemStock.textContent = item.stock === null ? '—' : item.stock;
                itemMinimum.textContent = item.minimum === null ? '—' : item.minimum;
                itemNotes.textContent = item.notes || '—';
                updateStatus(item.stock, item.minimum);
            }

            try {
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
            } catch (error) {
                console.error('No se pudieron cargar los datos guardados del inventario.', error);
                editError.textContent = 'No se pudieron cargar los datos guardados. Revisa el almacenamiento del navegador.';
                editError.style.display = 'block';
            }

            function setEditing(isEditing) {
                editButton.hidden = isEditing;
                editActions.hidden = !isEditing;
                inventoryRow.querySelectorAll('.editable-value').forEach((field) => {
                    const cell = field.closest('td');
                    if (cell) cell.textContent = field.value;
                });
            }

            editButton.addEventListener('click', () => {
                editError.style.display = 'none';
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
            });

            inventoryRow.querySelector('.save-button').addEventListener('click', () => {
                const name = inventoryRow.querySelector('[name="name"]').value.trim();
                const stockValue = inventoryRow.querySelector('[name="stock"]').value;
                const minimumValue = inventoryRow.querySelector('[name="minimum"]').value;
                const notes = inventoryRow.querySelector('[name="notes"]').value.trim();
                const stock = stockValue === '' ? null : Number(stockValue);
                const minimum = minimumValue === '' ? null : Number(minimumValue);

                if (!name || (stock !== null && (!Number.isInteger(stock) || stock < 0)) || (minimum !== null && (!Number.isInteger(minimum) || minimum < 0))) {
                    editError.textContent = 'Escribe un nombre y cantidades enteras iguales o mayores que cero.';
                    editError.style.display = 'block';
                    return;
                }

                const updatedItem = { name, stock, minimum, notes };
                try {
                    localStorage.setItem(inventoryRow.dataset.storageKey, JSON.stringify(updatedItem));
                } catch (error) {
                    console.error('No se pudieron guardar los cambios del inventario.', error);
                    editError.textContent = 'No se pudieron guardar los cambios en este navegador.';
                    editError.style.display = 'block';
                    return;
                }

                savedItem = updatedItem;
                showItem(savedItem);
                editError.style.display = 'none';
                setEditing(false);
            });

            inventoryRow.querySelector('.cancel-button').addEventListener('click', () => {
                showItem(savedItem || {
                    name: inventoryRow.dataset.defaultName,
                    stock: null,
                    minimum: null,
                    notes: 'Pendiente de registrar existencias'
                });
                editError.style.display = 'none';
                setEditing(false);
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
