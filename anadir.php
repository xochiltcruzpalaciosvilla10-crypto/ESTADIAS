<?php
$tipo = 'insumos';
$seccion = $_GET['seccion'] ?? 'secos';
$herramienta = $_GET['herramienta'] ?? '0';

if (!is_string($seccion) || $seccion === '') {
    $seccion = 'secos';
}
if (!is_string($herramienta) || !ctype_digit($herramienta)) {
    $herramienta = '0';
}

$urlRegreso = 'insumos.php?' . http_build_query([
    'seccion' => $seccion,
    'herramienta' => $herramienta,
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir artículo | BakeStock</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 32px 16px;
            background: #fdfbf7;
            color: #3e2723;
        }

        .form-card {
            width: min(100%, 680px);
            margin: 0 auto;
            padding: clamp(24px, 5vw, 40px);
            border: 1px solid #f0e5d7;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(62, 39, 35, 0.08);
        }

        h1 {
            margin: 0 0 8px;
            font-size: clamp(1.6rem, 4vw, 2rem);
        }

        .form-intro {
            margin: 0 0 24px;
            color: #6d554b;
            line-height: 1.6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .form-field {
            display: grid;
            gap: 7px;
        }

        .form-field.full-width {
            grid-column: 1 / -1;
        }

        label {
            color: #5d4037;
            font-weight: 700;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid #d7b36a;
            border-radius: 7px;
            background: #fff;
            color: #3e2723;
            font: inherit;
        }

        textarea {
            min-height: 92px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: 3px solid rgba(215, 179, 106, 0.3);
            outline-offset: 1px;
        }

        .form-error {
            display: none;
            margin: 16px 0 0;
            color: #9c2f25;
            font-weight: 700;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .form-actions button,
        .form-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .save-button {
            background: #3e2723;
            color: #fff;
        }

        .save-button:hover,
        .save-button:focus-visible {
            background: #7a442a;
        }

        .cancel-link {
            border: 1px solid #d6c6b7 !important;
            background: #fff;
            color: #5d4037;
        }

        @media (max-width: 520px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-field.full-width {
                grid-column: auto;
            }
        }
    </style>
</head>
<body>
    <main class="form-card">
        <h1>Añadir artículo</h1>
        <p class="form-intro">
            Agrega un registro nuevo al listado de Insumos.
            Captura los datos disponibles; las cantidades pueden dejarse vacías.
        </p>
        <form id="add-item-form" novalidate>
            <div class="form-grid">
                <div class="form-field full-width">
                    <label for="item-name">Nombre del artículo</label>
                    <input id="item-name" name="name" type="text" maxlength="120" required autocomplete="off">
                </div>
                <div class="form-field">
                    <label for="item-minimum">Cantidad actual</label>
                    <input id="item-minimum" name="minimum" type="number" min="0" step="1" inputmode="numeric">
                </div>
                <div class="form-field">
                    <label for="item-unit">Unidad de medida</label>
                    <select id="item-unit" name="unit">
                        <option>Gramos (g)</option>
                        <option>Kilogramos (kg)</option>
                        <option>Libras (lb)</option>
                        <option>Onzas (oz)</option>
                        <option>Mililitros (ml)</option>
                        <option>Litros (L)</option>
                        <option>Paquetes (paqs)</option>
                        <option selected>Piezas (pz)</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="item-date">Fecha de Registro</label>
                    <input id="item-date" name="fFecha_registro" type="date" value="<?php echo htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-field full-width">
                    <label for="item-notes">Notas / Ubicación</label>
                    <textarea id="item-notes" name="notes" maxlength="500"></textarea>
                </div>
            </div>
            <p class="form-error" id="form-error" role="alert"></p>
            <div class="form-actions">
                <button class="save-button" type="submit">Guardar</button>
                <a class="cancel-link" href="<?php echo htmlspecialchars($urlRegreso, ENT_QUOTES, 'UTF-8'); ?>">Cancelar</a>
            </div>
        </form>
    </main>
    <script>
        const form = document.getElementById('add-item-form');
        const formError = document.getElementById('form-error');
        const inventorySection = <?php echo json_encode($seccion, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const inventoryTool = <?php echo json_encode($herramienta); ?>;
        const inventoryListKey = `bakestock-listado-insumos-${inventorySection}-${inventoryTool}`;
        const measureUnits = [
            'Gramos (g)',
            'Kilogramos (kg)',
            'Libras (lb)',
            'Onzas (oz)',
            'Mililitros (ml)',
            'Litros (L)',
            'Piezas (pz)'
        ];

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            formError.style.display = 'none';

            const formData = new FormData(form);
            const name = String(formData.get('name') || '').trim();
            const minimumInput = String(formData.get('minimum') || '');
            const stock = null;
            const minimum = minimumInput === '' ? null : Number(minimumInput);
            const notes = String(formData.get('notes') || '').trim();
            const unit = String(formData.get('unit') || '');
            const fFecha_registro = String(formData.get('fFecha_registro') || '');

            if (
                !name
                || (minimum !== null && (!Number.isInteger(minimum) || minimum < 0))
                || !measureUnits.includes(unit)
                || !/^\d{4}-\d{2}-\d{2}$/.test(fFecha_registro)
            ) {
                formError.textContent = 'Escribe el nombre y utiliza cantidades enteras iguales o mayores que cero.';
                formError.style.display = 'block';
                return;
            }

            try {
                const storedIds = localStorage.getItem(inventoryListKey);
                const ids = storedIds === null ? [] : JSON.parse(storedIds);
                if (!Array.isArray(ids) || !ids.every((id) => typeof id === 'string')) {
                    throw new Error('El listado guardado no tiene un formato válido.');
                }

                const id = typeof crypto.randomUUID === 'function'
                    ? crypto.randomUUID()
                    : `${Date.now()}-${Math.random().toString(36).slice(2)}`;
                const item = { id, name, stock, minimum, notes, unit, fFecha_registro };

                fetch('insumos.php?action=guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        storage_key: `bakestock-articulo-${id}`,
                        name,
                        stock,
                        minimum,
                        unidad: unit,
                        notes,
                        fFecha_registro
                    })
                })
                    .then(async (response) => {
                        const result = await response.json();
                        if (!response.ok || result.status !== 'success') {
                            throw new Error(result.message || 'No se pudo guardar en la base de datos.');
                        }

                        localStorage.setItem(`bakestock-articulo-${id}`, JSON.stringify(item));
                        ids.push(id);
                        localStorage.setItem(inventoryListKey, JSON.stringify(ids));
                        window.location.href = <?php echo json_encode($urlRegreso, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    })
                    .catch((error) => {
                        console.error('No se pudo guardar el insumo.', error);
                        formError.textContent = 'No se pudo guardar en la base de datos. Revisa la conexión e inténtalo de nuevo.';
                        formError.style.display = 'block';
                    });
            } catch (error) {
                console.error('No se pudo agregar el artículo al listado.', error);
                formError.textContent = 'No se pudo guardar el artículo en este navegador. Revisa el almacenamiento e inténtalo de nuevo.';
                formError.style.display = 'block';
            }
        });
    </script>
</body>
</html>