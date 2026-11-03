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

$seccionActual = $_GET['seccion'] ?? 'inicio';
if (!is_string($seccionActual) || !isset($secciones[$seccionActual])) {
    $seccionActual = 'inicio';
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
                            href="Inventario.php?seccion=<?php echo urlencode($id); ?>"
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
                        href="Inventario.php?seccion=<?php echo urlencode($seccionActual); ?>&amp;herramienta=<?php echo $indice; ?>"
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
                            </tr>
                        </thead>
                        <tbody id="inventory-rows">
                            <tr class="inventory-row">
                                <td><?php echo htmlspecialchars($nombreHerramienta, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>—</td>
                                <td>—</td>
                                <td><span class="inventory-status">Sin registrar</span></td>
                                <td>Pendiente de registrar existencias</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="empty-search" id="empty-search" role="status">No se encontraron resultados.</p>
                </div>
                <p style="margin-top: 18px;"><a href="Inventario.php?seccion=<?php echo urlencode($seccionActual); ?>">← Volver a <?php echo htmlspecialchars($seccion['etiqueta'], ENT_QUOTES, 'UTF-8'); ?></a></p>
            <?php else: ?>
                <h1 id="page-title"><?php echo htmlspecialchars($seccion['titulo'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p><?php echo htmlspecialchars($seccion['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </section>
    </main>
    <?php if ($herramientaSeleccionada !== null): ?>
        <script>
            const inventorySearch = document.getElementById('inventory-search');
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
