<?php
session_start();
if (!isset($_SESSION['usuario']) && !isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
$conn = new mysqli("localhost", "root", "", "insumos");
if ($conn->connect_error) { die("Error de conexión: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'modificar') {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $categoria = $_POST['categoria'];
        $unidad = $_POST['unidad'];
        $entrada = floatval($_POST['entrada']);
        $en_uso = min($entrada, max(0, floatval($_POST['en_uso'])));
        $minimo = floatval($_POST['minimo']);
        $caducidad = !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null;

        $stmt = $conn->prepare("UPDATE inventario_insumos SET nombre=?, categoria=?, unidad=?, entrada=?, en_uso=?, minimo=?, fecha_caducidad=? WHERE id=?");
        $stmt->bind_param("sssdddsi", $nombre, $categoria, $unidad, $entrada, $en_uso, $minimo, $caducidad, $id);
        $stmt->execute();
        $stmt->close();
    }
    if ($accion === 'eliminar') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM inventario_insumos WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: insumos.php");
    exit;
}

$resultado = $conn->query("SELECT * FROM inventario_insumos ORDER BY id DESC");
$insumos = [];
$total = 0; $en_orden = 0; $stock_bajo = 0; $sin_stock = 0; $por_caducar_total = 0;
$limite_caducidad = date('Y-m-d', strtotime('+7 days'));

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $fila['disponible'] = max(0, $fila['entrada'] - $fila['en_uso']);
        $insumos[] = $fila;
        $total++;
        if ($fila['disponible'] <= 0) { $sin_stock++; }
        elseif ($fila['disponible'] <= $fila['minimo']) { $stock_bajo++; }
        else { $en_orden++; }
        if (!empty($fila['fecha_caducidad']) && $fila['fecha_caducidad'] <= $limite_caducidad) { $por_caducar_total++; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Control de Insumos | BakeStock</title>
  <link rel="stylesheet" href="style.css">
    <style>
    * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  background-color: #4c3322 !important;
  color: #292524;
  font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  min-height: 100vh;
  display: flex !important;
  overflow-x: hidden;
}

/* MENÚ LATERAL IZQUIERDO */
.sidebar {
  width: 245px;
  min-height: 100vh;
  background-color: #382315;
  color: #e7ddd5;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 1.5rem 1rem;
  flex-shrink: 0;
  border-right: 1px solid #5c3e28;
}

.brand {
  font-size: 1.35rem;
  font-weight: 800;
  color: #f5b942;
  padding: 0 0.5rem 1.25rem;
  border-bottom: 1px solid #5c3e28;
  margin-bottom: 1.25rem;
}

.menu-grupo { margin-bottom: 1.35rem; }

.menu-titulo {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #b59e8c;
  padding: 0 0.25rem;
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.65rem 0.85rem;
  border-radius: 0.45rem;
  color: #f3ece7;
  text-decoration: none;
  font-size: 0.82rem;
  font-weight: 600;
  margin-bottom: 0.45rem;
  transition: all 0.2s;
  cursor: pointer;
  border: 1px solid #6b4c35;
  background-color: #452c1b;
  width: 100%;
  text-align: left;
}

.menu-item:hover { background-color: #5c3c26; border-color: #8c684c; }
.menu-item.activo { background-color: #4f3320; color: #ffffff; border: 1px solid #f5b942; }
.menu-item.btn-nuevo { background-color: #5c3c26; border: 1px solid #f5b942; color: #fde68a; }
.menu-item.btn-nuevo:hover { background-color: #70492e; }

.sidebar-footer { padding-top: 1rem; display: flex; flex-direction: column; gap: 0.2rem; }
.sidebar-footer .menu-item { background-color: #3d2617; border: 1px solid #5c3e28; }

/* ÁREA PRINCIPAL Y PESTAÑAS */
.main-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  padding: 0.85rem 1.25rem 1rem 0.75rem;
  min-width: 0;
}

.top-tabs {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 0.85rem;
}

.tab-btn {
  background: transparent;
  color: #e7ddd5;
  border: none;
  padding: 0.5rem 1.25rem;
  border-radius: 0.55rem;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-btn:hover { background: #5e402b; color: #ffffff; }
.tab-btn.activa { background: #ffffff; color: #382315; font-weight: 700; box-shadow: 0 2px 5px rgba(0,0,0,0.25); }

/* CONTENEDOR CREMA Y ENCABEZADO */
.content-box {
  flex: 1;
  background-color: #f7f4ef;
  border-radius: 0.85rem;
  padding: 1.5rem 1.75rem;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.panel-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding-bottom: 1.1rem;
  border-bottom: 1px solid #e2dcd5;
  margin-bottom: 1.25rem;
}

.panel-header h1 { font-size: 1.55rem; color: #2b1a0f; font-weight: 800; }
.header-actions { display: flex; gap: 0.65rem; }

.btn-excel {
  background-color: #16794c;
  color: #ffffff;
  border: none;
  padding: 0.55rem 1rem;
  border-radius: 0.45rem;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-excel:hover { background-color: #115e3b; }

.btn-print {
  background-color: #5c3c26;
  color: #ffffff;
  border: none;
  padding: 0.55rem 1rem;
  border-radius: 0.45rem;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-print:hover { background-color: #382315; }

/* 4 TARJETAS KPI */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.kpi-card {
  background: #ffffff;
  border: 1.5px solid #4c3322;
  border-radius: 0.7rem;
  padding: 0.85rem 1.1rem;
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #2b1a0f;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.kpi-card.ok { color: #15803d; }
.kpi-card.bajo { color: #92400e; }
.kpi-card.agotado { color: #991b1b; }
/* BUSCADOR Y TABLA */
.search-bar {
  width: 100%;
  background: #ffffff;
  padding: 0.7rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #d6cec4;
  margin-bottom: 1.35rem;
  font-size: 0.85rem;
  outline: none;
}

.search-bar:focus { border-color: #4c3322; }

.tabla-titulo { font-size: 0.95rem; font-weight: 700; color: #2b1a0f; margin-bottom: 0.65rem; }

.tabla-contenedor {
  flex: 1;
  background: #ffffff;
  border-radius: 0.65rem;
  border: 1px solid #e2dcd5;
  overflow-x: auto;
}

table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem; }

th { background-color: #382315; color: #ffffff; font-size: 0.75rem; font-weight: 600; padding: 0.9rem 1rem; }
td { padding: 0.9rem 1rem; border-bottom: 1px solid #eee9e3; color: #292524; vertical-align: middle; }

tbody tr:nth-child(even) { background-color: #faf8f5; }
tbody tr:hover { background-color: #f3ede4; }
tr.fila-baja { background-color: #fefce8 !important; }
tr.fila-agotada { background-color: #fef2f2 !important; }

/* BADGES Y BOTONES DE ACCIÓN */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.65rem;
  border-radius: 0.35rem;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  white-space: nowrap;
}

.badge-ok { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.badge-bajo { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
.badge-agotado { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

.btn-editar {
  background-color: #4c3322;
  color: #ffffff;
  border: none;
  padding: 0.42rem 0.85rem;
  border-radius: 0.35rem;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  margin-right: 0.25rem;
}
.btn-editar:hover { background-color: #382315; }

.btn-eliminar {
  background: #8c2d19;
  color: #ffffff;
  border: none;
  padding: 0.42rem 0.75rem;
  border-radius: 0.35rem;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-eliminar:hover { background: #692011; }

/* MODAL DE MODIFICAR */
.modal-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  align-items: center;
  justify-content: center;
  z-index: 999;
  padding: 1rem;
}

.modal-card {
  background: #f7f4ef;
  padding: 1.5rem;
  border-radius: 0.75rem;
  width: 100%;
  max-width: 480px;
  border: 2px solid #4c3322;
}

.modal-card h3 { margin-bottom: 1rem; color: #2b1a0f; }
.modal-form { display: flex; flex-direction: column; gap: 0.85rem; }
.modal-form label { display: block; font-size: 0.8rem; font-weight: 700; color: #382315; margin-bottom: 0.25rem; }
.modal-form input, .modal-form select {
  border: 1px solid #d6cec4;
  border-radius: 0.45rem;
  padding: 0.55rem 0.75rem;
  font-size: 0.85rem;
  width: 100%;
  outline: none;
  background-color: #ffffff;
}

.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem; }
.btn-agregar { background-color: #4c3322; color: #ffffff; border: none; padding: 0.55rem 1.1rem; border-radius: 0.45rem; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
.btn-cancelar { background: #ffffff; border: 1px solid #c7b9ae; color: #4c3322; padding: 0.55rem 1rem; border-radius: 0.45rem; font-size: 0.82rem; font-weight: 600; cursor: pointer; }

/* RESPONSIVO Y MODO IMPRESIÓN */
@media (max-width: 1024px) {
  body { flex-direction: column !important; }
  .sidebar { width: 100%; min-height: auto; padding: 1rem; }
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 500px) {
  .kpi-grid { grid-template-columns: 1fr; }
}
@media print {
  .sidebar, .top-tabs, .search-bar, .header-actions, .col-accion { display: none !important; }
  body { background: #ffffff !important; }
}

  </style>
</head>
<body>

  <aside class="sidebar">
    <div>
      <div class="brand">🥖 Pan panel</div>
      <div class="menu-grupo">
        <div class="menu-titulo">Inventario de Insumos</div>
        <button type="button" id="btn-todos" onclick="activarVista('todos', this)" class="menu-item activo">🌾 Todos los Insumos (<?= $total ?>)</button>
      </div>
      <div class="menu-grupo">
        <div class="menu-titulo">Operación Diaria</div>
        <a href="nuevo_insumo.php" class="menu-item btn-nuevo">+ Nuevo Insumo</a>
        <button type="button" id="btn-compras" onclick="activarVista('compras', this)" class="menu-item">🛒 Lista de Compras (<?= $stock_bajo + $sin_stock ?>)</button>
        <button type="button" id="btn-caducar" onclick="activarVista('caducar', this)" class="menu-item">⏳ Próximos a Caducar (<?= $por_caducar_total ?>)</button>
      </div>
    </div>
        <div class="sidebar-footer">
      <a href="index.php" class="menu-item">Salir a portada</a>
      <a href="logout.php" class="menu-item" onclick="return confirm('¿Seguro que deseas cerrar sesión?')">Cerrar Sesión</a>
    </div>

  </aside>

  <main class="main-area">
    <nav class="top-tabs">
      <button type="button" class="tab-btn" onclick="filtrarCategoria('Secos y Harinas', this)">Secos y Harinas</button>
      <button type="button" class="tab-btn" onclick="filtrarCategoria('Lácteos y Refrigerados', this)">Lácteos y Refrigerados</button>
      <button type="button" class="tab-btn" onclick="filtrarCategoria('Rellenos y Decoración', this)">Rellenos y Decoración</button>
      <button type="button" class="tab-btn" onclick="filtrarCategoria('Empaques', this)">Empaques</button>
    </nav>

    <div class="content-box">
      <div class="panel-header">
        <h1 id="titulo-vista">Control de Insumos</h1>
        <div class="header-actions">
          <button type="button" onclick="exportarExcel()" class="btn-excel">📥 Exportar Excel</button>
          <button type="button" onclick="window.print()" class="btn-print">🖨️ Imprimir hoja</button>
        </div>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card" style="cursor:pointer;" onclick="activarVista('todos', document.getElementById('btn-todos'))">TOTAL DE INSUMOS <span id="kpi-total"><?= $total ?></span></div>
        <div class="kpi-card ok" style="cursor:pointer;" onclick="activarVista('ok', null)">✅ EN ORDEN (OK) <span id="kpi-ok"><?= $en_orden ?></span></div>
        <div class="kpi-card bajo" style="cursor:pointer;" onclick="activarVista('bajo', null)">🟡 STOCK BAJO <span id="kpi-bajo"><?= $stock_bajo ?></span></div>
        <div class="kpi-card agotado" style="cursor:pointer;" onclick="activarVista('agotado', null)">⚠️ SIN STOCK <span id="kpi-agotado"><?= $sin_stock ?></span></div>
      </div>

      <div id="banner-compras" style="display:none; background:#fef3c7; border:1.5px solid #f59e0b; color:#92400e; padding:0.85rem 1.1rem; border-radius:0.6rem; margin-bottom:1rem; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <div><strong>🛒 Necesitas comprar:</strong> <span id="texto-compras"></span></div>
        <button type="button" onclick="activarVista('compras', document.getElementById('btn-compras'))" style="background:#d97706; color:#fff; border:none; padding:0.35rem 0.8rem; border-radius:0.4rem; font-size:0.78rem; font-weight:700; cursor:pointer;">Ver Lista de Compras</button>
      </div>

      <div id="banner-caducar" style="display:none; background:#fee2e2; border:1.5px solid #ef4444; color:#991b1b; padding:0.85rem 1.1rem; border-radius:0.6rem; margin-bottom:1rem; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <div><strong>⏳ Atención en caducidad:</strong> <span id="texto-caducar"></span></div>
        <button type="button" onclick="activarVista('caducar', document.getElementById('btn-caducar'))" style="background:#dc2626; color:#fff; border:none; padding:0.35rem 0.8rem; border-radius:0.4rem; font-size:0.78rem; font-weight:700; cursor:pointer;">Ver Próximos a Caducar</button>
      </div>

      <input type="text" id="buscador" class="search-bar" placeholder="🔍 Escribe para buscar insumo (ej. azúcar, harina, levadura)...">
      <h3 class="tabla-titulo" style="display:block; width:100%; margin-bottom:0.75rem;">Listado activo de Insumos</h3>
      <div class="tabla-contenedor" style="width:100%;">
        <table id="tabla-exportar">
          <thead>
            <tr>
              <th>Insumo</th>
              <th>Entrada</th>
              <th>Disponible</th>
              <th>En uso</th>
              <th>Estado</th>
              <th>Fecha de caducidad</th>
              <th>Fecha de registro</th>
              <th class="col-accion" style="text-align: right;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($insumos)) { ?>
              <tr><td colspan="8" style="text-align:center; color:#8c7a6b; padding:2.5rem;">No hay insumos registrados aún. Haz clic en <strong>"+ Nuevo Insumo"</strong> para registrar el primero.</td></tr>
            <?php } else { ?>
              <?php foreach ($insumos as $item) { 
                $disponible = $item['disponible'];
                $agotado = $disponible <= 0;
                $bajo = !$agotado && ($disponible <= $item['minimo']);
                $estadoKey = $agotado ? 'agotado' : ($bajo ? 'bajo' : 'ok');
                $necesitaCompra = ($agotado || $bajo) ? '1' : '0';
                $porCaducar = ($item['fecha_caducidad'] && $item['fecha_caducidad'] <= $limite_caducidad) ? '1' : '0';
                $claseFila = $agotado ? 'fila-agotada' : ($bajo ? 'fila-baja' : '');
                $unidad = htmlspecialchars($item['unidad']);
              ?>
                <tr class="fila-insumo <?= $claseFila ?>" data-nombre="<?= strtolower(htmlspecialchars($item['nombre'])) ?>" data-categoria="<?= htmlspecialchars($item['categoria']) ?>" data-estado="<?= $estadoKey ?>" data-compra="<?= $necesitaCompra ?>" data-caducar="<?= $porCaducar ?>">
                  <td><strong style="color:#2b1a0f;"><?= htmlspecialchars($item['nombre']) ?></strong><div style="font-size:0.74rem; color:#78716c;"><?= htmlspecialchars($item['categoria']) ?></div></td>
                  <td style="font-weight:600; color:#4c3322;"><?= floatval($item['entrada']) ?> <?= $unidad ?></td>
                  <td style="font-weight:800; color:<?= $agotado ? '#dc2626' : ($bajo ? '#d97706' : '#15803d') ?>;"><?= floatval($disponible) ?> <?= $unidad ?></td>
                  <td style="font-weight:600; color:#57534e;"><?= floatval($item['en_uso']) ?> <?= $unidad ?></td>
                  <td>
                    <?php if ($agotado) { ?><span class="badge badge-agotado">⚠️ SIN STOCK</span>
                    <?php } elseif ($bajo) { ?><span class="badge badge-bajo">🟡 STOCK BAJO</span>
                    <?php } else { ?><span class="badge badge-ok">✅ OK</span><?php } ?>
                  </td>
                  <td>
                    <?php if ($item['fecha_caducidad']) { ?>
                      <span style="<?= $porCaducar === '1' ? 'color:#b91c1c; font-weight:700; background:#fee2e2; padding:0.2rem 0.5rem; border-radius:0.35rem;' : 'color:#57534e;' ?>"><?= date("d/m/Y", strtotime($item['fecha_caducidad'])) ?><?= $porCaducar === '1' ? ' ⏳' : '' ?></span>
                    <?php } else { ?><span style="color:#a8a29e;">Sin fecha</span><?php } ?>
                  </td>
                  <td style="color:#57534e; font-size:0.82rem;"><?= date("d/m/Y H:i", strtotime($item['fecha_registro'])) ?></td>
                  <td class="col-accion" style="text-align: right; white-space: nowrap;">
                    <button type="button" class="btn-editar" onclick="abrirModificar('<?= $item['id'] ?>', '<?= htmlspecialchars($item['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['categoria'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['unidad'], ENT_QUOTES) ?>', '<?= $item['entrada'] ?>', '<?= $item['en_uso'] ?>', '<?= $item['minimo'] ?>', '<?= $item['fecha_caducidad'] ?>')">Modificar</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este insumo?')">
                      <input type="hidden" name="accion" value="eliminar">
                      <input type="hidden" name="id" value="<?= $item['id'] ?>">
                      <button type="submit" class="btn-eliminar">🗑️</button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  <div id="modal-modificar" class="modal-backdrop">
    <div class="modal-card" style="max-width: 480px;">
      <h3>✏️ Modificar Insumo</h3>
      <form method="POST" action="insumos.php" class="modal-form">
        <input type="hidden" name="accion" value="modificar">
        <input type="hidden" name="id" id="mod-id">
        <div><label>Nombre del insumo:</label><input type="text" name="nombre" id="mod-nombre" required></div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.75rem;">
          <div><label>Categoría:</label><select name="categoria" id="mod-categoria"><option value="Secos y Harinas">Secos y Harinas</option><option value="Lácteos y Refrigerados">Lácteos y Refrigerados</option><option value="Rellenos y Decoración">Rellenos y Decoración</option><option value="Empaques">Empaques</option></select></div>
          <div><label>Unidad:</label><select name="unidad" id="mod-unidad"><option value="bultos">Bultos</option><option value="kg">Kilogramos (kg)</option><option value="L">Litros (L)</option><option value="pzas">Piezas</option></select></div>
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:0.65rem;">
          <div><label>Entrada total:</label><input type="number" name="entrada" id="mod-entrada" step="0.01" min="0" required></div>
          <div><label>En uso:</label><input type="number" name="en_uso" id="mod-en-uso" step="0.01" min="0" required></div>
          <div><label>Mínimo alerta:</label><input type="number" name="minimo" id="mod-minimo" step="0.01" min="0" required></div>
        </div>
        <div><label>Fecha de caducidad:</label><input type="date" name="fecha_caducidad" id="mod-caducidad"></div>
        <div class="modal-actions">
          <button type="button" onclick="cerrarModificar()" class="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn-agregar">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>

  <script>
        function filtrarCategoria(categoria, boton) {
      categoriaActual = categoria;
      vistaActual = 'todos';
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activa'));
      if (boton) boton.classList.add('activa');
      document.querySelectorAll('.sidebar .menu-item').forEach(b => b.classList.remove('activo'));
      document.getElementById('btn-todos').classList.add('activo');
      document.getElementById('titulo-vista').textContent = categoria === 'todas' ? 'Control de Insumos' : 'Insumos: ' + categoria;
      aplicarFiltros();
    }

    function activarVista(vista, boton) {
      vistaActual = vista;
      categoriaActual = 'todas';
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activa'));
      document.querySelectorAll('.sidebar .menu-item').forEach(b => b.classList.remove('activo'));
      if (boton) boton.classList.add('activo');
      const titulos = {
        'todos': 'Control de Insumos',
        'compras': '🛒 Lista de Compras (Stock Bajo y Sin Stock)',
        'caducar': '⏳ Insumos Próximos a Caducar (7 días)',
        'ok': '✅ Insumos en Orden (OK)',
        'bajo': '🟡 Insumos con Stock Bajo',
        'agotado': '⚠️ Insumos Sin Stock'
      };
      document.getElementById('titulo-vista').textContent = titulos[vista] || 'Control de Insumos';
      aplicarFiltros();
    }


    function aplicarFiltros() {
      const texto = document.getElementById('buscador').value.toLowerCase();
      const filas = document.querySelectorAll('.fila-insumo');
      let contTotal = 0, contOk = 0, contBajo = 0, contAgotado = 0;
      let listaCompras = [], listaCaducar = [];

      filas.forEach(fila => {
        const coincideTexto = fila.dataset.nombre.includes(texto);
        const coincideCat = (categoriaActual === 'todas') || (fila.dataset.categoria === categoriaActual);

        if (coincideCat) {
          contTotal++;
          const nombreReal = fila.querySelector('td strong').innerText;
          const dispTexto = fila.cells[2].innerText;
          if (fila.dataset.estado === 'agotado') { contAgotado++; listaCompras.push(`${nombreReal} (Agotado)`); }
          else if (fila.dataset.estado === 'bajo') { contBajo++; listaCompras.push(`${nombreReal} (Quedan ${dispTexto})`); }
          else { contOk++; }

          if (fila.dataset.caducar === '1') {
            const fechaTexto = fila.cells[5].innerText.replace('⏳', '').trim();
            listaCaducar.push(`${nombreReal} (${fechaTexto})`);
          }
        }

        let coincideVista = true;
        if (vistaActual === 'compras') coincideVista = (fila.dataset.compra === '1');
        if (vistaActual === 'caducar') coincideVista = (fila.dataset.caducar === '1');
        if (vistaActual === 'ok') coincideVista = (fila.dataset.estado === 'ok');
        if (vistaActual === 'bajo') coincideVista = (fila.dataset.estado === 'bajo');
        if (vistaActual === 'agotado') coincideVista = (fila.dataset.estado === 'agotado');

        fila.style.display = (coincideTexto && coincideCat && coincideVista) ? '' : 'none';
      });

      document.getElementById('kpi-total').textContent = contTotal;
      document.getElementById('kpi-ok').textContent = contOk;
      document.getElementById('kpi-bajo').textContent = contBajo;
      document.getElementById('kpi-agotado').textContent = contAgotado;

      const bannerCompras = document.getElementById('banner-compras');
      if (listaCompras.length > 0 && vistaActual !== 'compras') {
        document.getElementById('texto-compras').textContent = listaCompras.join(', ');
        bannerCompras.style.display = 'flex';
      } else { bannerCompras.style.display = 'none'; }

      const bannerCaducar = document.getElementById('banner-caducar');
      if (listaCaducar.length > 0 && vistaActual !== 'caducar') {
        document.getElementById('texto-caducar').textContent = listaCaducar.join(', ');
        bannerCaducar.style.display = 'flex';
      } else { bannerCaducar.style.display = 'none'; }
    }

    document.getElementById('buscador').addEventListener('input', aplicarFiltros);
    window.addEventListener('DOMContentLoaded', aplicarFiltros);

    function abrirModificar(id, nombre, categoria, unidad, entrada, enUso, minimo, caducidad) {
      document.getElementById('mod-id').value = id;
      document.getElementById('mod-nombre').value = nombre;
      document.getElementById('mod-categoria').value = categoria;
      document.getElementById('mod-unidad').value = unidad;
      document.getElementById('mod-entrada').value = entrada;
      document.getElementById('mod-en-uso').value = enUso;
      document.getElementById('mod-minimo').value = minimo;
      document.getElementById('mod-caducidad').value = caducidad || '';
      document.getElementById('modal-modificar').style.display = 'flex';
    }
    function cerrarModificar() { document.getElementById('modal-modificar').style.display = 'none'; }
    window.addEventListener('click', function(e) { if (e.target === document.getElementById('modal-modificar')) cerrarModificar(); });

    function exportarExcel() {
      let csv = [];
      const rows = document.querySelectorAll('#tabla-exportar tr');
      for (let i = 0; i < rows.length; i++) {
        if (rows[i].style.display === 'none') continue;
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length - 1; j++) {
          let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').trim();
          row.push('"' + data + '"');
        }
        csv.push(row.join(','));
      }
      const csvFile = new Blob(["\uFEFF" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.download = 'inventario_insumos.csv';
      link.href = URL.createObjectURL(csvFile);
      link.click();
    }
  </script>
</body>
</html>
