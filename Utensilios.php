<?php
session_start();
if (!isset($_SESSION['usuario']) && !isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$conexion = new mysqli("localhost", "root", "", "utensilios");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

// Aseguramos que la columna 'seccion' exista
$checkCol = $conexion->query("SHOW COLUMNS FROM equipo_menor LIKE 'seccion'");
if ($checkCol && $checkCol->num_rows === 0) {
    $conexion->query("ALTER TABLE equipo_menor ADD COLUMN seccion VARCHAR(50) NOT NULL DEFAULT 'cocina caliente'");
}

// Detectar y normalizar la pestaña activa
$titulos = [
    'cocina caliente' => 'Cocina caliente',
    'panaderia'       => 'Panadería',
    'reposteria'      => 'Repostería'
];

$seccion = $_GET['seccion'] ?? 'todos';

if ($seccion === 'todos') {
    $tituloActual = "Todos los Utensilios";
    $titulo_vista = "Todos los Utensilios";
    $placeholder_buscador = "Escribe para buscar en todos los utensilios...";
    $subtitulo_tabla = "Listado activo de Todos los Utensilios";
    $stmt = $conexion->prepare("SELECT * FROM equipo_menor ORDER BY id DESC");
} else {
    $nombreSeccion = $titulos[$seccion] ?? ucfirst($seccion);
    $tituloActual = $nombreSeccion;
    $titulo_vista = "Control de " . $nombreSeccion;
    $placeholder_buscador = "Escribe para buscar en " . $nombreSeccion . "...";
    $subtitulo_tabla = "Listado activo de " . $nombreSeccion;
    $stmt = $conexion->prepare("SELECT * FROM equipo_menor WHERE seccion = ? ORDER BY id DESC");
    $stmt->bind_param("s", $seccion);
}

$stmt->execute();
$resultado = $stmt->get_result();

// Calcular contadores para las 4 tarjetas KPI y guardar filas para la tabla
$totalArticulos = 0;
$countOk        = 0;
$countBajo      = 0;
$countSinStock  = 0;
$filasTabla     = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $filasTabla[] = $row;
        $totalArticulos++;
        $act = intval($row['cantidad_actual']);
        $ide = intval($row['cantidad_ideal']);

        if ($act === 0) {
            $countSinStock++;
        } elseif ($act < $ide) {
            $countBajo++;
        } else {
            $countOk++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloActual); ?> | BakeStock</title>
<style>
img {
  max-width: 100%;
  height: auto;
}
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background: url('fondo.jpg') no-repeat center center fixed; background-size: cover; background-color: #9e8a78; height: 100vh; overflow: hidden; display: flex; padding: 16px; gap: 18px; }
.sidebar { width: 235px; background: rgba(58, 34, 18, 0.94); backdrop-filter: blur(4px); border-radius: 14px; padding: 22px 15px; display: flex; flex-direction: column; justify-content: space-between; color: #faf6f0; flex-shrink: 0; border: 1.5px solid #c8963e; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); }
.brand { font-size: 20px; font-weight: 800; color: #e5c384; text-align: center; padding-bottom: 16px; border-bottom: 1.5px solid #c8963e; margin-bottom: 18px; }
.menu-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.8px; color: #e5c384; margin: 18px 0 8px 6px; font-weight: 700; }
.nav-btn { display: block; text-decoration: none; color: #f5ebe1; background-color: rgba(255, 255, 255, 0.03); border: 1px solid rgba(200, 150, 62, 0.35); padding: 11px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 10px; transition: all 0.2s ease; cursor: pointer; }
.nav-btn:hover { background-color: rgba(200, 150, 62, 0.18); border-color: #e5c384; color: #ffffff; }
.nav-btn.active { background-color: rgba(200, 150, 62, 0.28); border: 1.5px solid #e5c384; color: #ffffff; font-weight: 700; }
.nav-btn.danger { color: #dc2626; border-color: rgba(200, 150, 62, 0.35); }
.nav-btn.danger:hover { background-color: rgba(220, 38, 38, 0.18); border-color: #dc2626; }
.sidebar-bottom { padding-top: 14px; border-top: 1px solid rgba(200, 150, 62, 0.25); }
.main-wrapper { flex: 1; display: flex; flex-direction: column; gap: 16px; overflow: hidden; min-width: 0; }
.top-tabs { background: rgba(58, 34, 18, 0.88); backdrop-filter: blur(4px); border-radius: 12px; padding: 11px 24px; display: flex; justify-content: center; gap: 16px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
.tab-link { text-decoration: none; color: #e6d8cc; font-size: 13.5px; font-weight: 600; padding: 7px 20px; border-radius: 8px; transition: 0.2s; }
.tab-link:hover { color: #ffffff; background-color: rgba(255, 255, 255, 0.12); }
.tab-link.active { background-color: #faf6f0; color: #3d2214; font-weight: 700; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.18); }
.content-card { flex: 1; background-color: transparent; padding: 0; box-shadow: none; display: flex; flex-direction: column; gap: 16px; min-height: 0; overflow: hidden; }
.card-header { background-color: #faf6f0; border-radius: 14px; padding: 18px 28px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14); }
.card-header h1 { font-size: 22px; font-weight: 800; color: #3d2214; }
.btn-print, .btn-excel { background-color: #462814; color: #ffffff; border: none; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15); transition: 0.2s; }
.btn-excel { background-color: #1e7e34; }
.btn-excel:hover { background-color: #166534; }
.btn-print:hover { background-color: #2e190b; }
.kpi-grid { display: grid !important; grid-template-columns: repeat(4, 1fr); gap: 16px; }
.kpi-card { background-color: #faf6f0; border-radius: 12px; padding: 16px 20px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12); border-left: 5px solid #462814; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; user-select: none; }
.kpi-card:nth-child(2) { border-left-color: #1e7e34; }
.kpi-card:nth-child(3) { border-left-color: #d97706; }
.kpi-card:nth-child(4) { border-left-color: #c81e1e; }
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0, 0, 0, 0.16); }
.kpi-card.active { box-shadow: 0 0 0 2px #462814; transform: translateY(-2px); }
.search-row { background-color: #ffffff; border-radius: 30px; padding: 8px 18px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; width: 100%; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12); }
.search-input { flex: 1; min-width: 250px; padding: 8px 12px; font-size: 14px; color: #3d2214; background-color: transparent; border: none; outline: none; }
.filter-group { display: flex; flex-wrap: wrap; gap: 6px; }
.filter-btn { padding: 7px 14px; border: 1px solid #e2dcd5; border-radius: 20px; background-color: #faf7f2; color: #3d2214; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; }
.filter-btn:hover { background-color: #efe7dc; }
.filter-btn.active { background-color: #3d2214; color: #faf6f0; border-color: #3d2214; }
.filter-btn.ok.active { background-color: #1e7e34; border-color: #1e7e34; color: #ffffff; }
.filter-btn.bajo.active { background-color: #d97706; border-color: #d97706; color: #ffffff; }
.filter-btn.sin-stock.active { background-color: #c81e1e; border-color: #c81e1e; color: #ffffff; }
.section-subtitle { background-color: #ffffff; padding: 18px 26px 12px 26px; border-radius: 16px 16px 0 0; margin-bottom: -16px; font-size: 15px; font-weight: 800; color: #3d2214; border-left: 4px solid #c8963e; box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.06); }
table { display: block; width: 100%; flex: 1; min-height: 0; overflow-y: auto; border-collapse: collapse; background-color: #ffffff; border-radius: 0 0 16px 16px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.16); }
thead, tbody, tr { display: table; width: 100%; table-layout: fixed; }
thead { position: sticky; top: 0; z-index: 5; }
thead tr { background-color: #462814; color: #ffffff; }
th { text-align: left; padding: 14px 18px; font-size: 13px; font-weight: 700; color: #ffffff; }
td { padding: 14px 18px; font-size: 13.5px; color: #3d2214; border-bottom: 1px solid #f2ece4; }
tbody tr:nth-child(even) { background-color: #fcf9f5; }
tbody tr:hover { background-color: #f5ede3; }
.badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.badge-sin-stock { background-color: #fde8e8; color: #c81e1e; }
.badge-bajo { background-color: #fef6d8; color: #9f6000; }
.badge-ok { background-color: #e6f6ec; color: #1e7e34; }
.btn-modificar { display: inline-flex; align-items: center; justify-content: center; gap: 6px; background-color: #462814; color: #ffffff; text-decoration: none; padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 10px rgba(70, 40, 20, 0.35); transition: all 0.2s ease; }
.btn-modificar:hover { background-color: #2e190b; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(70, 40, 20, 0.45); }
.btn-modificar:active { transform: translateY(0); }

/* Ajustes para Tabletas (entre 769px y 1024px) */
@media (max-width: 1024px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }
}

/* Ajustes para Celulares (hasta 768px) */
@media (max-width: 768px) {
  body {
    flex-direction: column;
    height: auto;
    min-height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
  }

  .sidebar {
    width: 100% !important;
    height: auto !important;
  }

  .main-wrapper, .content-card {
    width: 100% !important;
    overflow: visible;
  }

  .top-tabs {
    flex-wrap: wrap;
    padding: 10px;
    gap: 8px;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .kpi-grid {
    grid-template-columns: 1fr !important;
  }

  .search-input {
    min-width: 100%;
  }

  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }

  thead, tbody, tr {
    display: table;
    width: 700px;
  }
}

/* Reglas exclusivas para cuando mandas a Imprimir */
@media print {
  .sidebar, .top-tabs, .kpi-grid, .search-row, .header-btns, .btn-print, .btn-excel, button, aside {
    display: none !important;
  }
  .main-wrapper, .content-card, .table-box {
    display: block !important;
    width: 100% !important;
    height: auto !important;
    overflow: visible !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  .card-header {
    box-shadow: none !important;
    padding: 0 0 10px 0 !important;
    border-bottom: 2px solid #3d2214 !important;
    border-radius: 0 !important;
    margin-bottom: 12px !important;
  }
  .section-subtitle {
    box-shadow: none !important;
    padding: 0 0 8px 0 !important;
    margin-bottom: 6px !important;
  }
  thead {
    display: table-header-group;
  }
}
</style>



</head>
<body>

    <aside class="sidebar">
        <div class="brand">🥖 Pan panel</div>
        <a href="Utensilios.php?seccion=todos" class="nav-btn <?= ($seccion === 'todos') ? 'active' : '' ?>">
  🍳 Ver Utensilios
</a>


        <a href="eventos.php" class="nav-btn">📋 Tabla de eventos</a>

        <div class="menu-label">Añadir al inventario</div>
        <a href="anadir.php?seccion=<?php echo urlencode($seccion); ?>" class="nav-btn">+ Agregar Artículo</a>

        <div class="menu-label">Acciones críticas</div>
        <a href="eliminar.php?seccion=<?php echo urlencode($seccion); ?>" class="nav-btn danger">🗑 Eliminar Producto</a>

        <div style="margin-top: auto;">
            <a href="menu.php" style="display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 15px; margin-bottom: 8px; color: #E6CCB2; background-color: rgba(255, 255, 255, 0.06); border: 1px solid rgba(212, 163, 115, 0.25); border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; transition: 0.3s;" onmouseover="this.style.backgroundColor='rgba(212, 163, 115, 0.2)'; this.style.color='#FFFFFF';" onmouseout="this.style.backgroundColor='rgba(255, 255, 255, 0.06)'; this.style.color='#E6CCB2';">
     Salir a portada
</a>

<a href="login.php?cerrar=1" class="nav-btn">Cerrar Sesión</a>
        </div>
    </aside>

    <div class="main-wrapper">
        <nav class="top-tabs">
  <a href="Utensilios.php?seccion=cocina+caliente" class="tab-link <?= ($seccion === 'cocina caliente') ? 'active' : '' ?>">Cocina caliente</a>
  <a href="Utensilios.php?seccion=panaderia" class="tab-link <?= ($seccion === 'panaderia') ? 'active' : '' ?>">Panadería</a>
  <a href="Utensilios.php?seccion=reposteria" class="tab-link <?= ($seccion === 'reposteria') ? 'active' : '' ?>">Repostería</a>
</nav>


        <main class="content-card">
            <div class="card-header">
                <h1>Control de <?php echo htmlspecialchars($tituloActual); ?></h1>
                <div style="display: flex; gap: 10px;">
                <button type="button" onclick="exportarExcel()" style="background-color: #1e7e34; color: #ffffff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer;">📥 Exportar Excel</button>
                <button class="btn-print" onclick="window.print()">🖨 Imprimir hoja</button>
            </div>
            </div>

            <!-- 4 Tarjetas KPI -->
             <!-- 4 Tarjetas KPI -->
<div class="kpi-grid">
    <div class="kpi-card total active" data-estado="todos">
        <span class="kpi-title">TOTAL DE ARTÍCULOS</span>
        <span class="kpi-number"><?php echo ($countOk + $countBajo + $countSinStock); ?></span>
    </div>

    <div class="kpi-card ok" data-estado="ok">
        <span class="kpi-title">✅ EN ORDEN (OK)</span>
        <span class="kpi-number"><?php echo $countOk; ?></span>
    </div>

    <div class="kpi-card bajo" data-estado="stock bajo">
        <span class="kpi-title">🟡 STOCK BAJO</span>
        <span class="kpi-number"><?php echo $countBajo; ?></span>
    </div>

    <div class="kpi-card sin-stock" data-estado="sin stock">
        <span class="kpi-title">⚠️ SIN STOCK</span>
        <span class="kpi-number"><?php echo $countSinStock; ?></span>
    </div>
</div>

                        <div class="search-row">
                <input type="text" id="buscador" class="search-input" placeholder="🔍 Escribe para buscar en <?php echo htmlspecialchars($tituloActual); ?>...">
            </div>

            <div class="section-subtitle">Listado activo de <?php echo htmlspecialchars($tituloActual); ?></div>

            <table>
<thead>
    <tr>
        <th style="width: 21%;">Nombre del artículo</th>
        <th style="width: 10%;">Entrada</th>
        <th style="width: 10%;">Disponibles</th>
        <th style="width: 11%;">En uso</th>
        <th style="width: 13%;">Estado</th>
        <th style="width: 14%;">Notas</th>
        <th style="width: 11%;">Fecha de Registro</th>
        <th style="width: 10%; text-align: center;">Acción</th>
    </tr>
</thead>
<tbody id="cuerpoTabla">
    <?php if (count($filasTabla) > 0): ?>
        <?php foreach ($filasTabla as $fila): ?>
             <?php
                $entrada     = intval($fila['cantidad_ideal']);  // Total que existe
                $disponibles = intval($fila['cantidad_actual']); // Los que están libres hoy
                $en_uso      = $entrada - $disponibles;          // Los que están ocupados
                if ($en_uso < 0) { $en_uso = 0; }
            ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($fila['nombre_articulo']); ?></strong></td>
                <td><?php echo $entrada; ?></td>
                <td><strong><?php echo $disponibles; ?></strong></td>
                <td><?php echo $en_uso; ?></td>
                <td>
                    <?php if ($disponibles === 0): ?>
                        <span class="badge badge-sin-stock">⚠️ SIN STOCK</span>
                    <?php elseif ($disponibles < $entrada): ?>
                        <span class="badge badge-bajo">🟡 STOCK BAJO</span>
                    <?php else: ?>
                        <span class="badge badge-ok">✅ OK</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($fila['notas_ubicacion']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($fila['fecha_registro'])); ?></td>
                <td style="text-align: center;">
  <a href="editar.php?id=<?php echo $fila['id']; ?>" class="btn-modificar">✏️ Modificar</a>

                   </td>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #8c7b70;">
                                No hay utensilios registrados en <strong><?php echo htmlspecialchars($tituloActual); ?></strong> todavía.
                            </td>
                        </tr>

                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador');
    const botonesFiltro = document.querySelectorAll('.kpi-card');
    let estadoActivo = 'todos';

    function filtrarTabla() {
        const textoOriginal = buscador ? buscador.value.trim() : "";
        const textoFiltro = textoOriginal.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        
        const tbody = document.querySelector('#cuerpoTabla') || document.querySelector('table tbody');
        if (!tbody) return;

        const filas = tbody.querySelectorAll('tr:not(#filaSinResultados)');
        let visibles = 0;

        filas.forEach(function(fila) {
            if (fila.cells.length < 2) return; // Ignora filas vacías
            const textoFila = fila.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const coincideTexto = textoFila.includes(textoFiltro);
            const coincideEstado = (estadoActivo === 'todos') || textoFila.includes(estadoActivo);

            if (coincideTexto && coincideEstado) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        // Mostrar u ocultar mensaje de error si no hay resultados
        let filaMensaje = document.getElementById('filaSinResultados');
        if (visibles === 0 && filas.length > 0) {
            if (!filaMensaje) {
                filaMensaje = document.createElement('tr');
                filaMensaje.id = 'filaSinResultados';
                filaMensaje.innerHTML = `<td colspan="8" style="width: 100%; text-align: center; padding: 26px; color: #c81e1e; font-weight: 700; background-color: #fef2f2; font-size: 14px;"></td>`;
                tbody.appendChild(filaMensaje);
            }
            filaMensaje.querySelector('td').textContent = textoOriginal !== '' 
                ? `⚠️ Error: El artículo "${textoOriginal}" no se encuentra registrado en esta sección del inventario.`
                : `⚠️ No hay artículos con ese estado en esta sección.`;
            filaMensaje.style.display = '';
        } else if (filaMensaje) {
            filaMensaje.style.display = 'none';
        }
    }

    if (buscador) {
        buscador.addEventListener('input', filtrarTabla);
    }

    botonesFiltro.forEach(function(boton) {
        boton.addEventListener('click', function() {
            botonesFiltro.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoActivo = (this.getAttribute('data-estado') || 'todos').toLowerCase();
            filtrarTabla();
        });
    });
});

// Función global para el botón verde (onclick="exportarExcel()")
function exportarExcel() {
    const tabla = document.querySelector('table');
    if (!tabla) return;

    let csv = [];
    const filas = tabla.querySelectorAll('tr');

    filas.forEach(function(fila) {
        if (fila.style.display === 'none' || fila.id === 'filaSinResultados') return;

        const celdas = fila.querySelectorAll('th, td');
        let datosFila = [];

        for (let i = 0; i < celdas.length - 1; i++) {
            let texto = celdas[i].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
            datosFila.push('"' + texto.replace(/"/g, '""') + '"');
        }
        csv.push(datosFila.join(','));
    });

    const titulo = document.querySelector('h1')?.innerText.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]/g, '').trim().replace(/\s+/g, '_') || 'Inventario';
    const fecha = new Date().toISOString().slice(0, 10);

    const blob = new Blob(["\uFEFF" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const enlace = document.createElement('a');
    enlace.href = URL.createObjectURL(blob);
    enlace.download = `${titulo}_BakeStock_${fecha}.csv`;
    
    document.body.appendChild(enlace);
    enlace.click();
    document.body.removeChild(enlace);
}
</script>


</body>
</html>
