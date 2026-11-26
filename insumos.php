<?php
session_start();
date_default_timezone_set('America/Mexico_City');
$conexion = new mysqli("localhost", "root", "", "bakestock_insumos");
if ($conexion->connect_error) { die("Error de conexión: " . $conexion->connect_error); }
$conexion->set_charset("utf8mb4");

// Crear tabla enfocada en abastecimiento y compras de panadería
$checkCol = $conexion->query("SHOW COLUMNS FROM insumos LIKE 'stock_maximo'");
if ($checkCol && $checkCol->num_rows === 0) {
    $conexion->query("DROP TABLE IF EXISTS insumos");
}

$conexion->query("CREATE TABLE IF NOT EXISTS insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_insumo VARCHAR(150) NOT NULL,
    categoria VARCHAR(60) NOT NULL DEFAULT 'Harinas y Secos',
    unidad_medida VARCHAR(30) NOT NULL DEFAULT 'Kg',
    stock_minimo DECIMAL(10,2) NOT NULL DEFAULT 10.00,
    stock_maximo DECIMAL(10,2) NOT NULL DEFAULT 50.00,
    stock_actual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    costo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    proveedor VARCHAR(100) NULL,
    fecha_caducidad DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 1. CREAR TABLA KARDEX SI NO EXISTE
$conexion->query("CREATE TABLE IF NOT EXISTS kardex_insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL DEFAULT 'Admin',
    tipo_movimiento VARCHAR(30) NOT NULL,
    insumo VARCHAR(150) NOT NULL,
    cantidad VARCHAR(50) NOT NULL,
    motivo TEXT NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$alerta_sistema = "";
$usuario_activo = $_SESSION['usuario'] ?? $_SESSION['nombre'] ?? 'Encargado';

// 2. PROCESAR FORMULARIOS DE LOS MODALES (AGREGAR, MOVIMIENTO O ELIMINAR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_modal'])) {
    $accion = $_POST['accion_modal'];

    // A) REGISTRAR NUEVO INSUMO (CON VALIDACIÓN ANTI-DUPLICADOS)
    if ($accion === 'nuevo_insumo') {
        $nombre = trim($_POST['nombre_insumo']);
        $cat    = $_POST['categoria'];
        $unidad = $_POST['unidad_medida'];
        $min    = floatval($_POST['stock_minimo']);
        $max    = floatval($_POST['stock_maximo']);
        $actual = floatval($_POST['stock_actual']);
        $costo  = floatval($_POST['costo_unitario']);
        $prov   = trim($_POST['proveedor']);

        $checkDup = $conexion->prepare("SELECT id FROM insumos WHERE LOWER(TRIM(nombre_insumo)) = LOWER(?)");
        $checkDup->bind_param("s", $nombre);
        $checkDup->execute();
        if ($checkDup->get_result()->num_rows > 0) {
            $alerta_sistema = "<div style='background:#fee2e2; color:#991b1b; padding:12px 18px; border-radius:10px; border-left:5px solid #dc2626; font-weight:700; font-size:13.5px;'>⚠️ Error: El insumo \"$nombre\" ya existe en el inventario. Usa el botón \"Entrada / Salida\" para sumar mercancía.</div>";
        } else {
            $ins = $conexion->prepare("INSERT INTO insumos (nombre_insumo, categoria, unidad_medida, stock_minimo, stock_maximo, stock_actual, costo_unitario, proveedor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param("sssdddds", $nombre, $cat, $unidad, $min, $max, $actual, $costo, $prov);
            if ($ins->execute()) {
                $cantTxt = "$actual $unidad";
                $motTxt  = "Alta inicial en bodega (Mín: $min / Máx: $max | Prov: $prov)";
                $k = $conexion->prepare("INSERT INTO kardex_insumos (usuario, tipo_movimiento, insumo, cantidad, motivo) VALUES (?, 'ALTA', ?, ?, ?)");
                $k->bind_param("ssss", $usuario_activo, $nombre, $cantTxt, $motTxt);
                $k->execute();
                header("Location: insumos.php?msg=ok"); exit();
            }
        }
    }

    // B) REGISTRAR ENTRADA (COMPRA) O SALIDA (PRODUCCIÓN / MERMA)
    if ($accion === 'movimiento') {
        $id_ins   = intval($_POST['id_insumo']);
        $tipo     = $_POST['tipo_mov']; // ENTRADA o SALIDA
        $cantidad = floatval($_POST['cantidad_mov']);
        $motivo   = trim($_POST['motivo_mov']);

        $q = $conexion->query("SELECT * FROM insumos WHERE id = $id_ins");
        if ($q && $item = $q->fetch_assoc()) {
            $nuevo_stock = ($tipo === 'ENTRADA') ? ($item['stock_actual'] + $cantidad) : max(0, $item['stock_actual'] - $cantidad);
            $conexion->query("UPDATE insumos SET stock_actual = $nuevo_stock WHERE id = $id_ins");

            $signo = ($tipo === 'ENTRADA') ? "+" : "-";
            $cantTxt = "$signo$cantidad " . $item['unidad_medida'];
            $k = $conexion->prepare("INSERT INTO kardex_insumos (usuario, tipo_movimiento, insumo, cantidad, motivo) VALUES (?, ?, ?, ?, ?)");
            $k->bind_param("sssss", $usuario_activo, $tipo, $item['nombre_insumo'], $cantTxt, $motivo);
            $k->execute();
            header("Location: insumos.php?msg=mov"); exit();
        }
    }

    // C) ELIMINAR INSUMO DEL CATÁLOGO
    if ($accion === 'eliminar_insumo') {
        $id_del = intval($_POST['id_eliminar']);
        $mot_del = trim($_POST['motivo_eliminar']);
        $q = $conexion->query("SELECT * FROM insumos WHERE id = $id_del");
        if ($q && $item = $q->fetch_assoc()) {
            $conexion->query("DELETE FROM insumos WHERE id = $id_del");
            $cantTxt = $item['stock_actual'] . " " . $item['unidad_medida'];
            $k = $conexion->prepare("INSERT INTO kardex_insumos (usuario, tipo_movimiento, insumo, cantidad, motivo) VALUES (?, 'BAJA', ?, ?, ?)");
            $k->bind_param("ssss", $usuario_activo, $item['nombre_insumo'], $cantTxt, $mot_del);
            $k->execute();
            header("Location: insumos.php?msg=del"); exit();
        }
    }
}

// 3. CONSULTAR HISTORIAL DEL KARDEX PARA LA VENTANA EMERGENTE
$historialKardex = $conexion->query("SELECT * FROM kardex_insumos ORDER BY fecha_hora DESC, id DESC LIMIT 50");


$resultado = $conexion->query("SELECT * FROM insumos ORDER BY (stock_actual <= stock_minimo) DESC, nombre_insumo ASC");

$totalInsumos = 0; $porComprar = 0; $agotados = 0; $presupuestoCompra = 0; $filasTabla = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $filasTabla[] = $row;
        $totalInsumos++;
        $act = floatval($row['stock_actual']);
        $min = floatval($row['stock_minimo']);
        $max = floatval($row['stock_maximo']);
        $costo = floatval($row['costo_unitario']);

        if ($act <= 0) { $agotados++; }
        if ($act <= $min) {
            $porComprar++;
            $cantidadPedir = max(0, $max - $act);
            $presupuestoCompra += ($cantidadPedir * $costo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Abastecimiento e Insumos | BakeStock</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background: url('fondo.jpg') no-repeat center center fixed; background-size: cover; background-color: #9e8a78; height: 100vh; max-height: 100vh; overflow: hidden; display: flex; padding: 16px; gap: 18px; }
.sidebar { width: 235px; background: rgba(58, 34, 18, 0.94); backdrop-filter: blur(4px); border-radius: 14px; padding: 22px 15px; display: flex; flex-direction: column; justify-content: space-between; color: #faf6f0; flex-shrink: 0; border: 1.5px solid #d97706; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); }
.brand { font-size: 20px; font-weight: 800; color: #fbbf24; text-align: center; padding-bottom: 16px; border-bottom: 1.5px solid #d97706; margin-bottom: 18px; }
.menu-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.8px; color: #fbbf24; margin: 18px 0 8px 6px; font-weight: 700; }
.nav-btn { display: block; text-decoration: none; color: #f5ebe1; background-color: rgba(255, 255, 255, 0.03); border: 1px solid rgba(217, 119, 6, 0.4); padding: 11px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 10px; transition: all 0.2s ease; }
.nav-btn:hover { background-color: rgba(217, 119, 6, 0.22); border-color: #fbbf24; color: #ffffff; }
.nav-btn.active { background-color: rgba(217, 119, 6, 0.32); border: 1.5px solid #fbbf24; color: #ffffff; font-weight: 700; }
.nav-btn.danger { color: #f87171; border-color: rgba(248, 113, 113, 0.35); }
.nav-btn.danger:hover { background-color: rgba(220, 38, 38, 0.2); border-color: #ef4444; }
.sidebar-bottom { padding-top: 14px; border-top: 1px solid rgba(217, 119, 6, 0.3); }
.main-wrapper { flex: 1; display: flex; flex-direction: column; gap: 14px; height: 100%; min-height: 0; overflow: hidden; }
.card-header { background-color: #faf6f0; border-radius: 14px; padding: 16px 28px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14); border-left: 6px solid #d97706; }
.badge-modulo { display: inline-block; background-color: #fef3c7; color: #92400e; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; border: 1px solid #f59e0b; margin-bottom: 4px; }
.card-header h1 { font-size: 21px; font-weight: 800; color: #3d2214; }
.header-btns { display: flex; gap: 10px; }
.btn-top { border: none; padding: 9px 15px; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; color: #ffffff; box-shadow: 0 3px 8px rgba(0,0,0,0.15); transition: 0.2s; }
.btn-compra { background-color: #d97706; }
.btn-compra:hover { background-color: #b45309; }
.btn-excel { background-color: #1e7e34; }
.btn-excel:hover { background-color: #166534; }
.btn-print { background-color: #462814; }
.btn-print:hover { background-color: #2e190b; }
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.kpi-card { background-color: #faf6f0; border-radius: 12px; padding: 14px 18px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12); border-left: 5px solid #462814; cursor: pointer; transition: 0.15s; user-select: none; }
.kpi-card.comprar { border-left-color: #d97706; }
.kpi-card.agotado { border-left-color: #c81e1e; }
.kpi-card.dinero { border-left-color: #0369a1; cursor: default; }
.kpi-card:not(.dinero):hover, .kpi-card.active { transform: translateY(-2px); box-shadow: 0 0 0 2px #d97706; }
.kpi-title { font-size: 11px; font-weight: 700; color: #6b5340; text-transform: uppercase; margin-bottom: 4px; }
.kpi-value { font-size: 23px; font-weight: 800; color: #3d2214; }
.kpi-card.comprar .kpi-value { color: #d97706; }
.kpi-card.agotado .kpi-value { color: #c81e1e; }
.kpi-card.dinero .kpi-value { color: #0369a1; }
.search-row { background-color: #ffffff; border-radius: 30px; padding: 8px 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12); }
.search-input { flex: 1; padding: 8px 6px; font-size: 14px; color: #3d2214; background-color: transparent; border: none; outline: none; }
.cat-select { padding: 7px 14px; border-radius: 20px; border: 1px solid #e2dcd5; background-color: #faf7f2; color: #3d2214; font-size: 12.5px; font-weight: 700; outline: none; cursor: pointer; }
.section-subtitle { background-color: #ffffff; padding: 16px 26px 12px 26px; border-radius: 16px 16px 0 0; margin-bottom: -14px; font-size: 15px; font-weight: 800; color: #3d2214; border-left: 5px solid #d97706; box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.06); display: flex; justify-content: space-between; }
table { display: block; width: 100%; max-height: calc(100vh - 305px); overflow-y: auto; border-collapse: collapse; background-color: #ffffff; border-radius: 0 0 16px 16px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.16); }
thead, tbody, tr { display: table; width: 100%; table-layout: fixed; }
thead { position: sticky; top: 0; z-index: 5; }
thead tr { background-color: #5c3419; color: #ffffff; }
th { text-align: left; padding: 13px 12px; font-size: 12px; font-weight: 700; color: #ffffff; }
td { padding: 12px 12px; font-size: 13px; color: #3d2214; border-bottom: 1px solid #f2ece4; }
tbody tr:nth-child(even) { background-color: #fdf9f3; }
tbody tr:hover { background-color: #faeedc; }
.cat-tag { font-size: 11px; background-color: #f3e8dd; color: #5c3419; padding: 3px 8px; border-radius: 6px; font-weight: 600; }
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; }
.badge-agotado { background-color: #fde8e8; color: #c81e1e; }
.badge-comprar { background-color: #fef3c7; color: #b45309; }
.badge-ok { background-color: #e6f6ec; color: #1e7e34; }
.pedir-box { font-weight: 800; color: #b45309; background-color: #fffbeb; padding: 4px 8px; border-radius: 6px; border: 1px dashed #f59e0b; display: inline-block; }
.btn-mov { display: inline-flex; align-items: center; justify-content: center; gap: 4px; background-color: #462814; color: #ffffff; text-decoration: none; padding: 7px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; transition: 0.2s; }
.btn-mov:hover { background-color: #d97706; }
@media print {
@page { margin: 10mm; }
body { background: #fff !important; height: auto !important; overflow: visible !important; display: block !important; padding: 0 !important; }
.sidebar, .kpi-grid, .search-row, .header-btns { display: none !important; }
.main-wrapper { display: block !important; height: auto !important; overflow: visible !important; }
table { display: table !important; max-height: none !important; overflow: visible !important; box-shadow: none !important; }
thead { display: table-header-group !important; }
tbody { display: table-row-group !important; }
tr { display: table-row !important; }
th, td { display: table-cell !important; border: 1px solid #ccc !important; padding: 7px !important; font-size: 11px !important; }
th:last-child, td:last-child { display: none !important; }
}
</style>
</head>
<body>
<aside class="sidebar">
    <div>
        <div class="brand">🌾 BakeStock Compras</div>
        <a href="insumos.php" class="nav-btn active">📦 Almacén y Pedidos</a>
        <button type="button" onclick="abrirMovimiento(<?php echo $fila['id']; ?>, '<?php echo htmlspecialchars($fila['nombre_insumo'], ENT_QUOTES); ?>', '<?php echo $uni; ?>', <?php echo $act; ?>)" class="btn-mov">🔄 Entrada / Salida</button>
        <a href="#" onclick="abrirModal('modalKardex'); return false;" class="nav-btn">📋 Bitácora Kardex</a>
        <div class="menu-label">Gestión de Materia Prima</div>
        <a href="#" onclick="abrirModal('modalNuevo'); return false;" class="nav-btn">+ Nuevo Insumo</a>
        <div class="menu-label">Control y Bajas</div>
        <a href="#" onclick="abrirModal('modalEliminar'); return false;" class="nav-btn danger">🗑️ Eliminar Insumo</a>
    </div>
    <div class="sidebar-bottom">
        <a href="menu.php" class="nav-btn">Salir a Portada</a>
        <a href="logout.php" class="nav-btn" style="margin-bottom: 0;">Cerrar sesión</a>
    </div>
</aside>

<main class="main-wrapper">
    <?php if (!empty($alerta_sistema)) echo $alerta_sistema; ?>

    <div class="card-header">
        <div>
            <span class="badge-modulo">🛒 CONTROL DE ABASTECIMIENTO Y PUNTO DE REORDEN</span>
            <h1>Inventario de Insumos y Necesidad de Compra</h1>
        </div>
        <div class="header-btns">
            <button onclick="imprimirOrdenCompra()" class="btn-top btn-compra">🛒 Imprimir Lista de Compra</button>
            <button onclick="exportarExcel()" class="btn-top btn-excel">📊 Exportar Excel</button>
            <button onclick="window.print()" class="btn-top btn-print">🖨️ Imprimir Todo</button>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card active" data-filtro="todos">
            <div class="kpi-title">Total Insumos Registrados</div>
            <div class="kpi-value"><?php echo $totalInsumos; ?></div>
        </div>
        <div class="kpi-card comprar" data-filtro="comprar">
            <div class="kpi-title">🛒 Urge Comprar (Reorden)</div>
            <div class="kpi-value"><?php echo $porComprar; ?></div>
        </div>
        <div class="kpi-card agotado" data-filtro="agotado">
            <div class="kpi-title">⚠️ Agotados en Bodega</div>
            <div class="kpi-value"><?php echo $agotados; ?></div>
        </div>
        <div class="kpi-card dinero">
            <div class="kpi-title">💰 Presupuesto de Compra</div>
            <div class="kpi-value">$<?php echo number_format($presupuestoCompra, 2); ?></div>
        </div>
    </div>

    <div class="search-row">
        <span>🔍</span>
        <input type="text" id="buscador" class="search-input" placeholder="Buscar harina, levadura, proveedor...">
        <select id="filtroCategoria" class="cat-select">
            <option value="todas">Todas las categorías</option>
            <option value="Harinas y Secos">Harinas y Secos</option>
            <option value="Lácteos y Refrigerados">Lácteos y Refrigerados</option>
            <option value="Grasas y Aceites">Grasas y Aceites</option>
            <option value="Polvos y Esencias">Polvos y Esencias</option>
        </select>
    </div>

    <div class="section-subtitle">
        <span>Listado de Materia Prima (Ordenado por prioridad de compra)</span>
        <span style="font-size: 12px; color: #78716c; font-weight: 600;">* Sugerencia calculada para llenar al Stock Máximo</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 21%;">Insumo / Proveedor</th>
                <th style="width: 13%;">Categoría</th>
                <th style="width: 9%;">Existencia</th>
                <th style="width: 9%;">Mín / Máx</th>
                <th style="width: 12%;">A Comprar</th>
                <th style="width: 10%;">Costo Pedido</th>
                <th style="width: 12%;">Estado</th>
                <th style="width: 14%; text-align: center;">Movimiento</th>
            </tr>
        </thead>
        <tbody id="cuerpoTabla">
            <?php if (count($filasTabla) > 0): ?>
                <?php foreach ($filasTabla as $fila): ?>
                    <?php
                        $act   = floatval($fila['stock_actual']);
                        $min   = floatval($fila['stock_minimo']);
                        $max   = floatval($fila['stock_maximo']);
                        $costo = floatval($fila['costo_unitario']);
                        $uni   = htmlspecialchars($fila['unidad_medida']);

                        $necesitaCompra = ($act <= $min);
                        $pedir = $necesitaCompra ? max(0, $max - $act) : 0;
                        $costoPedido = $pedir * $costo;

                        if ($act <= 0) {
                            $estadoData = 'agotado comprar';
                            $badgeHtml  = '<span class="badge badge-agotado">⚠️ AGOTADO</span>';
                        } elseif ($act <= $min) {
                            $estadoData = 'comprar';
                            $badgeHtml  = '<span class="badge badge-comprar">🛒 PEDIR YA</span>';
                        } else {
                            $estadoData = 'ok';
                            $badgeHtml  = '<span class="badge badge-ok">✅ SUFICIENTE</span>';
                        }
                    ?>
                    <tr data-estado="<?php echo $estadoData; ?>" data-categoria="<?php echo htmlspecialchars($fila['categoria']); ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($fila['nombre_insumo']); ?></strong><br>
                            <small style="color:#78716c;">Prov: <?php echo htmlspecialchars($fila['proveedor'] ?: 'General'); ?></small>
                        </td>
                        <td><span class="cat-tag"><?php echo htmlspecialchars($fila['categoria']); ?></span></td>
                        <td><strong><?php echo number_format($act, 2); ?></strong> <?php echo $uni; ?></td>
                        <td style="color:#6b5340; font-size:12px;"><?php echo number_format($min, 0); ?> / <?php echo number_format($max, 0); ?></td>
                        <td>
                            <?php if ($pedir > 0): ?>
                                <span class="pedir-box">+ <?php echo number_format($pedir, 2); ?> <?php echo $uni; ?></span>
                            <?php else: ?>
                                <span style="color:#9ca3af;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($costoPedido > 0): ?>
                                <strong style="color:#0369a1;">$<?php echo number_format($costoPedido, 2); ?></strong>
                            <?php else: ?>
                                <span style="color:#9ca3af;">$0.00</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $badgeHtml; ?></td>
                        <td style="text-align: center;">
                            <a href="movimiento_insumo.php?id=<?php echo $fila['id']; ?>" class="btn-mov">🔄 Entrada / Salida</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: #78716c; font-weight: 600;">
                        Aún no tienes insumos registrados. Haz clic en "+ Nuevo Insumo" en el menú izquierdo para agregar tus ingredientes.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador');
    const selectCat = document.getElementById('filtroCategoria');
    const tarjetas = document.querySelectorAll('.kpi-card:not(.dinero)');
    let filtroEstado = 'todos';

    function aplicarFiltros() {
        const texto = (buscador.value || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        const cat = selectCat.value;
        const tbody = document.getElementById('cuerpoTabla');
        const filas = tbody.querySelectorAll('tr:not(#filaVacia)');
        let visibles = 0;

        filas.forEach(fila => {
            if (fila.cells.length < 2) return;
            const contenido = fila.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const estFila = fila.getAttribute('data-estado') || '';
            const catFila = fila.getAttribute('data-categoria') || '';

            const okTexto = contenido.includes(texto);
            const okCat = (cat === 'todas') || (catFila === cat);
            const okEst = (filtroEstado === 'todos') || estFila.includes(filtroEstado);

            if (okTexto && okCat && okEst) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        let msg = document.getElementById('filaVacia');
        if (visibles === 0 && filas.length > 0 && filas[0].cells.length > 1) {
            if (!msg) {
                msg = document.createElement('tr');
                msg.id = 'filaVacia';
                msg.innerHTML = `<td colspan="8" style="text-align:center; padding:25px; color:#c81e1e; font-weight:700; background:#fef2f2;"></td>`;
                tbody.appendChild(msg);
            }
            msg.querySelector('td').textContent = `⚠️ No se encontraron insumos con los filtros seleccionados.`;
            msg.style.display = '';
        } else if (msg) {
            msg.style.display = 'none';
        }
    }

    if (buscador) buscador.addEventListener('input', aplicarFiltros);
    if (selectCat) selectCat.addEventListener('change', aplicarFiltros);

    tarjetas.forEach(t => {
        t.addEventListener('click', function() {
            tarjetas.forEach(x => x.classList.remove('active'));
            this.classList.add('active');
            filtroEstado = this.getAttribute('data-filtro');
            aplicarFiltros();
        });
    });

    window.imprimirOrdenCompra = function() {
        tarjetas.forEach(x => x.classList.remove('active'));
        const cardComprar = document.querySelector('.kpi-card.comprar');
        if (cardComprar) cardComprar.classList.add('active');
        filtroEstado = 'comprar';
        aplicarFiltros();
        setTimeout(() => {
            window.print();
        }, 200);
    };
});

function exportarExcel() {
    const tabla = document.querySelector('table');
    let csv = [];
    tabla.querySelectorAll('tr').forEach(fila => {
        if (fila.style.display === 'none' || fila.id === 'filaVacia' || fila.cells.length < 2) return;
        const celdas = fila.querySelectorAll('th, td');
        let datos = [];
        for (let i = 0; i < celdas.length - 1; i++) {
            datos.push('"' + celdas[i].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim().replace(/"/g, '""') + '"');
        }
        csv.push(datos.join(','));
    });

    const blob = new Blob(["\uFEFF" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `Orden_Compra_Insumos_${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
}

function abrirModal(id) {
    document.getElementById(id).classList.add('abierto');
}

function cerrarModales() {
    document.querySelectorAll('.modal-fondo').forEach(m => m.classList.remove('abierto'));
}

function abrirMovimiento(id, nombre, unidad, actual) {
    document.getElementById('mov_id').value = id;
    document.getElementById('mov_info').textContent = `Insumo: ${nombre} | Existencia actual: ${actual} ${unidad}`;
    abrirModal('modalMovimiento');
}
</script>
</body>
</html>

