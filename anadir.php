<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "utensilios");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// Detectar en qué sección estábamos (Cocina caliente, Panadería o Repostería)
$seccion_actual = $_GET['seccion'] ?? 'cocina caliente';
if ($seccion_actual === 'cocina+caliente') {
    $seccion_actual = 'cocina caliente';
}

$mensaje_error = "";
$nombre_previo = "";
$total_previo  = 1;
$actual_previo = 1;
$notas_previas = "";
$fecha_previa  = date('Y-m-d');

// CUANDO EL USUARIO PRESIONA "GUARDAR PRODUCTO"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim($_POST['nombre_articulo']);
    $total   = intval($_POST['cantidad_ideal']);
    $actual  = intval($_POST['cantidad_actual']);
    $notas   = trim($_POST['notas_ubicacion']);
    $fecha   = $_POST['fecha_registro'];
    $seccion = $_POST['seccion'];

    // Guardamos lo que escribió por si hay error y no tenga que volver a escribir todo
    $nombre_previo  = $nombre;
    $total_previo   = $total;
    $actual_previo  = $actual;
    $notas_previas  = $notas;
    $fecha_previa   = $fecha;
    $seccion_actual = $seccion;

    // 1. REVISAR SI EL ARTÍCULO YA EXISTE EN CUALQUIERA DE LAS 3 SECCIONES
    $sql_verificar = "SELECT seccion FROM equipo_menor WHERE LOWER(TRIM(nombre_articulo)) = LOWER(?)";
    $stmt_verificar = $conexion->prepare($sql_verificar);
    $stmt_verificar->bind_param("s", $nombre);
    $stmt_verificar->execute();
    $resultado_verificar = $stmt_verificar->get_result();

    if ($resultado_verificar->num_rows > 0) {
        // Obtenemos la sección donde ya está guardado
        $fila_existente = $resultado_verificar->fetch_assoc();
        
        // Traducimos el nombre interno a un texto bonito con acentos
        $nombres_bonitos = [
            'cocina caliente' => 'Cocina caliente',
            'panaderia'       => 'Panadería',
            'reposteria'      => 'Repostería'
        ];
        $sec_bd = $fila_existente['seccion'];
        $nombre_seccion = $nombres_bonitos[$sec_bd] ?? ucfirst($sec_bd);

        // ¡YA EXISTE! No guardamos nada y mostramos exactamente en qué pestaña está
        $mensaje_error = "⚠️ El artículo <strong>\"" . htmlspecialchars($nombre) . "\"</strong> ya está registrado en la sección de <strong>" . $nombre_seccion . "</strong>. No se guardó para evitar duplicados.";
    } else {
        // 2. NO EXISTE: LO GUARDAMOS EN LA BASE DE DATOS
        if ($actual > $total) {
            $actual = $total;
        }

        $sql_insertar = "INSERT INTO equipo_menor (nombre_articulo, cantidad_ideal, cantidad_actual, notas_ubicacion, fecha_registro, seccion) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insertar = $conexion->prepare($sql_insertar);
        $stmt_insertar->bind_param("siisss", $nombre, $total, $actual, $notas, $fecha, $seccion);

        if ($stmt_insertar->execute()) {
            // 3. CONEXIÓN CON LA TABLA EVENTOS (Esto es lo que faltaba)
            $conexion->query("CREATE TABLE IF NOT EXISTS eventos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario VARCHAR(100) NOT NULL DEFAULT 'Desconocido',
                accion VARCHAR(30) NOT NULL,
                seccion VARCHAR(50) NOT NULL DEFAULT 'general',
                descripcion TEXT NOT NULL,
                fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $usuario_ev = $_SESSION['usuario'] ?? $_SESSION['nombre'] ?? 'Administrador';
            $accion_ev  = "AGREGAR";
            $desc_ev    = "Se agregó nuevo artículo: '$nombre' (Entrada: $total, Disponibles: $actual)";

            $sql_ev  = "INSERT INTO eventos (usuario, accion, seccion, descripcion, fecha_hora) VALUES (?, ?, ?, ?, NOW())";
            $stmt_ev = $conexion->prepare($sql_ev);

            if ($stmt_ev) {
                $stmt_ev->bind_param("ssss", $usuario_ev, $accion_ev, $seccion, $desc_ev);
                if (!$stmt_ev->execute()) {
                    die("Error al registrar en eventos: " . $stmt_ev->error);
                }
            } else {
                die("Error al preparar consulta eventos: " . $conexion->error);
            }

            // 4. REGRESAR AL INVENTARIO
            header("Location: Utensilios.php?seccion=" . urlencode($seccion));
            exit();
        } else {
            $mensaje_error = "Error al guardar en la base de datos: " . $conexion->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Artículo | BakeStock</title>
    <style>
  * {
    box-sizing: border-box;
  }
  img {
    max-width: 100%;
    height: auto;
  }
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #9e8a78;
    margin: 0;
    padding: 30px 40px;
  }
  .top-bar {
    background-color: #fdf8f2;
    border-radius: 12px;
    padding: 16px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    max-width: 1150px;
    margin: 0 auto 30px auto;
  }
  .top-bar .left-label {
    font-weight: 700;
    color: #462814;
    font-size: 16px;
  }
  .top-bar h1 {
    margin: 0;
    font-size: 20px;
    color: #462814;
    font-weight: 700;
    text-align: center;
  }
  .btn-volver {
    background-color: #d6a472;
    color: #462814;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    transition: background 0.2s;
  }
  .btn-volver:hover {
    background-color: #c5915e;
  }
  .form-card {
    background-color: #ffffff;
    width: 100%;
    max-width: 440px;
    margin: 0 auto;
    padding: 32px 36px;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
  }
  .alerta-duplicado {
    background-color: #fee2e2;
    border-left: 4px solid #dc2626;
    color: #991b1b;
    padding: 12px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 18px;
    line-height: 1.4;
  }
  label {
    display: block;
    margin-top: 15px;
    margin-bottom: 6px;
    font-weight: 700;
    color: #462814;
    font-size: 13px;
  }
  label:first-of-type {
    margin-top: 0;
  }
  input, select {
    width: 100%;
    padding: 11px 14px;
    background-color: #fbf7f3;
    border: 1px solid #eadfd5;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 14px;
    color: #3d2c1e;
    font-family: inherit;
  }
  input:focus, select:focus {
    outline: none;
    border-color: #b08256;
    background-color: #ffffff;
  }
  .btn-guardar {
    width: 100%;
    margin-top: 24px;
    background-color: #462814;
    color: #ffffff;
    border: none;
    padding: 13px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: background 0.2s;
  }
  .btn-guardar:hover {
    background-color: #321b0c;
  }

  /* Ajustes para Celulares y Tabletas chicas (hasta 650px) */
  @media (max-width: 650px) {
    body {
      padding: 16px;
    }
    .top-bar {
      flex-direction: column;
      text-align: center;
      padding: 16px;
      margin-bottom: 20px;
    }
    .top-bar h1 {
      font-size: 18px;
    }
    .btn-volver {
      width: 100%;
      text-align: center;
    }
    .form-card {
      padding: 24px 20px;
    }
  }
</style>

</head>
<body>

    <div class="top-bar">
        <span class="left-label">Añadir</span>
        <h1>Agregar Nuevo Producto</h1>
        <a href="Utensilios.php?seccion=<?php echo urlencode($seccion_actual); ?>" class="btn-volver">← Cancelar y regresar</a>
    </div>

    <div class="form-card">
        
        <?php if (!empty($mensaje_error)): ?>
            <div class="alerta-duplicado">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Nombre del artículo:</label>
            <input type="text" name="nombre_articulo" value="<?php echo htmlspecialchars($nombre_previo); ?>" placeholder="Ej. Batidor de globo" required>

            <label>Entrada:</label>
            <input type="number" name="cantidad_ideal" min="1" value="<?php echo $total_previo; ?>" required>

            <label>Disponibles:</label>
            <input type="number" name="cantidad_actual" min="0" value="<?php echo $actual_previo; ?>" required>

            <label>Notas, ubicación o estado:</label>
            <input type="text" name="notas_ubicacion" value="<?php echo htmlspecialchars($notas_previas); ?>" placeholder="Opcional">

            <label>Fecha de registro:</label>
            <input type="date" name="fecha_registro" value="<?php echo htmlspecialchars($fecha_previa); ?>" required>

            <label>Guardar en la pestaña:</label>
            <select name="seccion">
                <option value="cocina caliente" <?php if($seccion_actual == 'cocina caliente') echo 'selected'; ?>>Cocina caliente</option>
                <option value="panaderia" <?php if($seccion_actual == 'panaderia') echo 'selected'; ?>>Panadería</option>
                <option value="reposteria" <?php if($seccion_actual == 'reposteria') echo 'selected'; ?>>Repostería</option>
            </select>

            <button type="submit" class="btn-guardar">Guardar Producto</button>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputTotal = document.getElementById('cantidad_ideal');
        const inputActual = document.getElementById('cantidad_actual');
        if (!inputTotal || !inputActual) return;

        function aplicarCandados() {
            const total = parseInt(inputTotal.value) || 0;
            const actual = parseInt(inputActual.value) || 0;

            inputActual.max = total;

            if (actual < 0) {
                inputActual.value = 0;
            }
            if (actual > total) {
                inputActual.value = total;
            }
        }

        // Al escribir el total, iguala automáticamente la cantidad actual y aplica el límite
        inputTotal.addEventListener('input', function() {
            const total = parseInt(this.value) || 0;
            if (total < 1 && this.value !== '') this.value = 1;
            inputActual.value = parseInt(this.value) || 0;
            aplicarCandados();
        });

        // Al escribir directo en cantidad actual, frena si se pasa del total o pone negativo
        inputActual.addEventListener('input', aplicarCandados);

        aplicarCandados();
    });
    </script>
</body>
</html>
